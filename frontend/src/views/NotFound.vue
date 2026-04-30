<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="page max-w-[480px] py-margin">
    <div class="card text-center">
      <p class="t-label-muted">Error</p>
      <h1 class="font-display text-display mt-3">404</h1>
      <p class="t-muted mt-2 mb-8">Página no encontrada.</p>
      <RouterLink to="/" class="btn-primary inline-flex">Volver al inicio</RouterLink>
    </div>
  </div>
</template>
