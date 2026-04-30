import axios from 'axios'

// En desarrollo, usar el proxy de Vite para evitar problemas CORS
const isDev = import.meta.env.DEV
const baseURL = isDev ? '' : (import.meta.env.VITE_API_URL || 'http://localhost:8000')

export const api = axios.create({
  baseURL,
  withCredentials: true,
  withXSRFToken: true,
  headers: { Accept: 'application/json' },
  timeout: 10000 // 10 segundos timeout
})

let csrfPromise: Promise<void> | null = null
let csrfReady = false

export async function ensureCsrf() {
  if (csrfReady) return
  if (csrfPromise) return csrfPromise

  csrfPromise = axios.get(`${baseURL}/sanctum/csrf-cookie`, {
    withCredentials: true,
    timeout: 5000
  }).then(() => {
    csrfReady = true
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
  return config
})

api.interceptors.response.use(
  (r) => r,
  (err) => {
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
      csrfReady = false
    }

    if (err?.response?.status === 401) {
      // optional: redirect to login handled by guards
    }
    return Promise.reject(err)
  }
)
