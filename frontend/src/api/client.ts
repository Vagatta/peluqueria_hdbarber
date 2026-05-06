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

// Token-based auth para cross-origin (Vercel -> Render)
let authToken: string | null = localStorage.getItem('auth_token')

export function setAuthToken(token: string | null) {
  authToken = token
  if (token) {
    localStorage.setItem('auth_token', token)
  } else {
    localStorage.removeItem('auth_token')
  }
}

export function getAuthToken(): string | null {
  return authToken
}

export function clearAuthToken() {
  authToken = null
  localStorage.removeItem('auth_token')
}

// Verificar si usamos token auth (cross-origin) o cookie auth (same-origin)
const useTokenAuth = !isDev || (typeof window !== 'undefined' && window.location.hostname !== 'localhost')

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
  
  // Si tenemos token, usarlo en lugar de CSRF
  if (authToken) {
    config.headers.Authorization = `Bearer ${authToken}`
    // No necesitamos withCredentials para token auth
    config.withCredentials = false
  } else if (['post', 'put', 'patch', 'delete'].includes(method)) {
    // Solo intentar CSRF si no tenemos token
    await ensureCsrf()
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

    if (err?.response?.status === 419 && !authToken) {
      // CSRF token mismatch solo si no usamos token auth
      console.log('[CSRF] Token mismatch, retrying...')
      csrfReady = false
      
      const originalConfig = err.config
      if (originalConfig && !originalConfig._csrfRetry) {
        originalConfig._csrfRetry = true
        await ensureCsrf()
        return api(originalConfig)
      }
    }

    if (err?.response?.status === 401) {
      // Limpiar token inválido
      if (authToken) {
        clearAuthToken()
      }
    }
    return Promise.reject(err)
  }
)
