<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { XCircle } from 'lucide-vue-next'
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <div class="card text-center">
      <div class="mx-auto w-20 h-20 rounded bg-surface-container-high border border-outline-variant grid place-items-center mb-8">
        <XCircle class="w-9 h-9 text-error" />
      </div>
      <h1 class="font-display text-h1 uppercase tracking-tight mb-3">Pago cancelado</h1>
      <p class="t-muted max-w-sm mx-auto mb-8">
        No te preocupes, tu cita sigue reservada. Puedes reintentar el pago cuando quieras.
      </p>
      <RouterLink to="/appointments" class="btn-primary inline-flex">Ir a mis citas</RouterLink>
    </div>
  </div>
</template>
