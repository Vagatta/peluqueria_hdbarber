<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter, RouterLink } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { User, Mail, Phone, Lock, ArrowRight } from 'lucide-vue-next'

const auth = useAuthStore()
const router = useRouter()
const route = useRoute()
const form = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const error = ref<string | null>(null)

async function submit() {
  error.value = null
  try {
    await auth.register(form.value)
    const redirect = (route.query.redirect as string) || '/'
    const service = route.query.service
    router.push(service ? { path: redirect, query: { service } } : redirect)
  } catch (e: any) {
    const errs = e?.response?.data?.errors
    error.value = errs ? Object.values(errs).flat().join(' ') : (e?.response?.data?.message || 'Error al registrar')
  }
}
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <div class="card silver-glow">
      <h1 class="font-display text-h1 text-center uppercase tracking-tight mb-2">Crear cuenta</h1>
      <p class="t-muted text-center mb-8">Reserva tu próxima visita en segundos.</p>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="label">Nombre completo</label>
          <div class="field">
            <User class="input-icon w-4 h-4" />
            <input v-model="form.name" required class="input input-with-icon" placeholder="Alex Pérez" />
          </div>
        </div>
        <div>
          <label class="label">Correo electrónico</label>
          <div class="field">
            <Mail class="input-icon w-4 h-4" />
            <input v-model="form.email" type="email" required class="input input-with-icon" placeholder="cliente@ejemplo.com" />
          </div>
        </div>
        <div>
          <label class="label">Teléfono <span class="normal-case text-on-surface-variant/60 text-xs">(opcional)</span></label>
          <div class="field">
            <Phone class="input-icon w-4 h-4" />
            <input v-model="form.phone" type="tel" class="input input-with-icon" placeholder="+34 600 000 000" />
          </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-3">
          <div>
            <label class="label">Contraseña</label>
            <div class="field">
              <Lock class="input-icon w-4 h-4" />
              <input v-model="form.password" type="password" required class="input input-with-icon" placeholder="••••••••" />
            </div>
          </div>
          <div>
            <label class="label">Repetir</label>
            <div class="field">
              <Lock class="input-icon w-4 h-4" />
              <input v-model="form.password_confirmation" type="password" required class="input input-with-icon" placeholder="••••••••" />
            </div>
          </div>
        </div>

        <p v-if="error" class="text-error text-sm">{{ error }}</p>

        <button class="btn-primary w-full" :disabled="auth.loading">
          Crear cuenta <ArrowRight class="w-4 h-4" />
        </button>
      </form>

      <p class="text-center t-muted text-sm mt-8">
        ¿Ya tienes cuenta?
        <RouterLink to="/login" class="btn-link ml-1">Iniciar sesión</RouterLink>
      </p>
    </div>
  </div>
</template>
