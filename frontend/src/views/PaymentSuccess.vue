<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { CheckCircle2 } from 'lucide-vue-next'
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <div class="card text-center">
      <div class="mx-auto w-20 h-20 rounded bg-surface-container-high border border-outline-variant grid place-items-center mb-8">
        <CheckCircle2 class="w-9 h-9 text-emerald-300" />
      </div>
      <h1 class="font-display text-h1 uppercase tracking-tight mb-3">Pago realizado</h1>
      <p class="t-muted max-w-sm mx-auto mb-8">
        Tu cita ha sido confirmada. Hemos enviado los detalles a tu correo electrónico.
      </p>
      <RouterLink to="/appointments" class="btn-primary inline-flex">Ver mis citas</RouterLink>
    </div>
  </div>
</template>
