<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const auth = useAuthStore()
const form = ref({ name: auth.user?.name || '', phone: auth.user?.phone || '', password: '', password_confirmation: '' })
const msg = ref<string | null>(null)
const err = ref<string | null>(null)

async function submit() {
  msg.value = null; err.value = null
  try {
    const payload: any = { name: form.value.name, phone: form.value.phone }
    if (form.value.password) {
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }
    await auth.updateProfile(payload)
    msg.value = 'Perfil actualizado'
    form.value.password = ''
    form.value.password_confirmation = ''
  } catch (e: any) {
    err.value = e?.response?.data?.message || 'Error al guardar'
  }
}
</script>

<template>
  <div class="page py-margin max-w-2xl">
    <div class="mb-gutter">
      <span class="t-label-muted">Cuenta</span>
      <h1 class="font-display text-display mt-1">Mi perfil</h1>
    </div>

    <div class="card">
      <div class="flex items-center gap-4 pb-6 mb-6 divider">
        <div class="w-16 h-16 rounded-full bg-surface-container-high border border-outline-variant grid place-items-center text-h2 font-display uppercase">
          {{ auth.user?.name?.charAt(0) || 'U' }}
        </div>
        <div class="min-w-0">
          <p class="font-display text-h2 truncate">{{ auth.user?.name }}</p>
          <p class="t-muted text-sm truncate">{{ auth.user?.email }}</p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="label">Email (no editable)</label>
          <input :value="auth.user?.email" disabled class="input opacity-60 cursor-not-allowed" />
        </div>
        <div>
          <label class="label">Nombre</label>
          <input v-model="form.name" required class="input" />
        </div>
        <div>
          <label class="label">Teléfono</label>
          <input v-model="form.phone" class="input" />
        </div>

        <div class="pt-6 divider">
          <h3 class="t-label mb-4">Cambiar contraseña <span class="normal-case text-on-surface-variant/60">(opcional)</span></h3>
          <div class="grid sm:grid-cols-2 gap-3">
            <div>
              <label class="label">Nueva contraseña</label>
              <input v-model="form.password" type="password" class="input" />
            </div>
            <div>
              <label class="label">Confirmar</label>
              <input v-model="form.password_confirmation" type="password" class="input" />
            </div>
          </div>
        </div>

        <p v-if="msg" class="text-emerald-300 text-sm">{{ msg }}</p>
        <p v-if="err" class="text-error text-sm">{{ err }}</p>
        <button class="btn-primary w-full">Guardar cambios</button>
      </form>
    </div>
  </div>
</template>
