<script setup lang="ts">
import { ref } from 'vue'
import { useRouter, RouterLink } from 'vue-router'
import { Mail, ArrowRight, ArrowLeft } from 'lucide-vue-next'
import { api, ensureCsrf } from '@/api/client'

const router = useRouter()
const email = ref('')
const loading = ref(false)
const error = ref<string | null>(null)

async function submit() {
  error.value = null
  loading.value = true
  try {
    await ensureCsrf()
    await api.post('/api/auth/forgot-password', { email: email.value })
    router.push({ name: 'password-link-sent' })
  } catch (e: any) {
    // Por seguridad, igualmente navegamos como si se hubiera enviado
    if (e?.response?.status && e.response.status < 500) {
      router.push({ name: 'password-link-sent' })
    } else {
      error.value = e?.response?.data?.message || 'No se pudo enviar el enlace'
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <RouterLink to="/login" class="btn-link mb-6">
      <ArrowLeft class="w-3.5 h-3.5" /> Volver
    </RouterLink>

    <div class="card">
      <h1 class="font-display text-h1 text-center uppercase tracking-tight text-on-surface-variant/70 mb-4">
        Restaurar contraseña
      </h1>
      <p class="t-muted text-center mb-8 max-w-sm mx-auto">
        Introduce la dirección de correo electrónico asociada a tu cuenta y te enviaremos un enlace para restablecer tu contraseña.
      </p>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="label">Correo electrónico</label>
          <div class="field">
            <Mail class="input-icon w-4 h-4" />
            <input v-model="email" type="email" required class="input input-with-icon" placeholder="tu@email.com" />
          </div>
        </div>

        <p v-if="error" class="text-error text-sm">{{ error }}</p>

        <button class="btn-primary w-full" :disabled="loading">
          Enviar enlace de recuperación <ArrowRight class="w-4 h-4" />
        </button>
      </form>

      <div class="mt-8 text-center">
        <RouterLink to="/login" class="btn-link">Volver al inicio de sesión</RouterLink>
      </div>
    </div>
  </div>
</template>
