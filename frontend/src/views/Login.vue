<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { Mail, Lock, ArrowRight, UserPlus } from 'lucide-vue-next'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()

const email = ref('')
const password = ref('')
const error = ref<string | null>(null)

async function submit() {
  error.value = null
  try {
    await auth.login(email.value, password.value)
    const redirect = (route.query.redirect as string) || '/'
    const service = route.query.service
    router.push(service ? { path: redirect, query: { service } } : redirect)
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'No se pudo iniciar sesión'
  }
}

const registerLink = computed(() => {
  const q: Record<string, any> = {}
  if (route.query.redirect) q.redirect = route.query.redirect
  if (route.query.service) q.service = route.query.service
  return Object.keys(q).length ? { path: '/register', query: q } : '/register'
})
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <div class="card silver-glow relative overflow-hidden">
      <!-- Logo -->
      <div class="flex justify-center mb-8">
        <div class="w-20 h-20 rounded bg-surface-container-high border border-outline-variant grid place-items-center">
          <svg viewBox="0 0 64 64" class="w-12 h-12 text-primary">
            <circle cx="32" cy="32" r="22" fill="none" stroke="currentColor" stroke-width="2"/>
            <rect x="27" y="15" width="10" height="34" rx="2" fill="none" stroke="currentColor" stroke-width="2"/>
            <line x1="27" y1="22" x2="37" y2="32" stroke="currentColor" stroke-width="2"/>
            <line x1="27" y1="32" x2="37" y2="42" stroke="currentColor" stroke-width="2"/>
          </svg>
        </div>
      </div>

      <h1 class="font-display text-h1 text-center uppercase tracking-tight mb-2">Acceso a mi cuenta</h1>
      <p class="t-muted text-center mb-8">Ingresa tus credenciales para continuar al portal de reservas.</p>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="label">Correo electrónico</label>
          <div class="field">
            <Mail class="input-icon w-4 h-4" />
            <input v-model="email" type="email" autocomplete="email" required placeholder="cliente@ejemplo.com" class="input input-with-icon" />
          </div>
        </div>

        <div>
          <div class="flex items-baseline justify-between mb-2">
            <span class="t-label-muted">Contraseña</span>
            <RouterLink to="/forgot-password" class="text-label normal-case text-on-surface-variant hover:text-on-surface">¿Olvidaste?</RouterLink>
          </div>
          <div class="field">
            <Lock class="input-icon w-4 h-4" />
            <input v-model="password" type="password" autocomplete="current-password" required placeholder="••••••••" class="input input-with-icon" />
          </div>
        </div>

        <p v-if="error" class="text-error text-sm">{{ error }}</p>

        <button class="btn-primary w-full" :disabled="auth.loading">
          Iniciar sesión <ArrowRight class="w-4 h-4" />
        </button>
      </form>

      <div class="my-8 flex items-center gap-3 t-micro">
        <div class="flex-1 divider"></div>
        <span>O</span>
        <div class="flex-1 divider"></div>
      </div>

      <p class="text-center t-muted text-sm">
        ¿Nuevo cliente?
        <RouterLink :to="registerLink" class="btn-link ml-1">
          Regístrate <UserPlus class="w-3.5 h-3.5" />
        </RouterLink>
      </p>
    </div>
  </div>
</template>
