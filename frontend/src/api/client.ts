import axios from 'axios'

// Extender AxiosRequestConfig para incluir _csrfRetry
declare module 'axios' {
  export interface AxiosRequestConfig {
    _csrfRetry?: boolean
  }
}

// En desarrollo, usar el proxy de Vite para evitar problemas CORS
const isDev = import.meta.env.DEV
const baseURL = isDev ? '' : (import.meta.env.VITE_API_URL || 'http://localhost:8000')

export const api = axios.create({
  baseURL,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
  timeout: 30000 // 30 segundos timeout
})

let csrfPromise: Promise<void> | null = null
let csrfReady = false

function getCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'))
  return match ? decodeURIComponent(match[2]) : null
}

export async function ensureCsrf() {
  if (csrfReady) return
  if (csrfPromise) return csrfPromise

  csrfPromise = axios.get(`${baseURL}/sanctum/csrf-cookie`, {
    withCredentials: true,
    timeout: 10000
  }).then(() => {
    csrfReady = true
    console.log('[CSRF] Cookie obtained successfully')
  }).catch((err) => {
    console.error('[CSRF] Failed to obtain cookie:', err.message)
    throw err
  }).finally(() => {
    csrfPromise = null
  })

  return csrfPromise
}

api.interceptors.request.use(async (config) => {
  const method = (config.method || 'get').toLowerCase()
  if (['post', 'put', 'patch', 'delete'].includes(method)) {
    await ensureCsrf()
  }
  
  // Debug: log cookies y headers
  if (method !== 'get') {
    const xsrfCookie = getCookie('XSRF-TOKEN')
    console.log(`[API ${method.toUpperCase()}] ${config.url}`, {
      xsrfCookie: xsrfCookie ? xsrfCookie.substring(0, 20) + '...' : 'NOT FOUND',
      xsrfHeader: config.headers?.['X-XSRF-TOKEN'] ? 'SET' : 'NOT SET',
      withCredentials: config.withCredentials
    })
  }
  
  return config
})

api.interceptors.response.use(
  (r) => r,
  async (err) => {
    // Manejo de errores de red
    if (!err.response) {
      console.error('Network Error:', err.message)
      if (err.code === 'ECONNABORTED') {
        err.message = 'La petición tardó demasiado. Inténtalo de nuevo.'
      } else if (err.code === 'ERR_NETWORK') {
        err.message = 'No se pudo conectar con el servidor. Verifica que el backend esté corriendo.'
      }
    }

    if (err?.response?.status === 419) {
      // CSRF token mismatch - reset y reintentar una vez
      console.log('[CSRF] Token mismatch, retrying...')
      csrfReady = false
      
      // Reintentar la petición original una vez
      const originalConfig = err.config
      if (originalConfig && !originalConfig._csrfRetry) {
        originalConfig._csrfRetry = true
        await ensureCsrf()
        return api(originalConfig)
      }
    }

    if (err?.response?.status === 401) {
      // optional: redirect to login handled by guards
    }
    return Promise.reject(err)
  }
)
