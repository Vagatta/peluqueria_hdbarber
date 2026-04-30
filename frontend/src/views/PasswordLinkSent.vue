<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { MailCheck } from 'lucide-vue-next'
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <div class="card text-center">
      <div class="mx-auto w-20 h-20 rounded bg-surface-container-high border border-outline-variant grid place-items-center mb-8">
        <MailCheck class="w-9 h-9 text-primary" />
      </div>
      <h1 class="font-display text-h1 uppercase tracking-tight mb-3">Enlace enviado</h1>
      <p class="t-muted max-w-sm mx-auto mb-8">
        Revisa tu bandeja de entrada. Te hemos enviado instrucciones para restablecer tu contraseña.
      </p>
      <RouterLink to="/login" class="btn-primary inline-flex">Volver al inicio de sesión</RouterLink>
    </div>
  </div>
</template>
