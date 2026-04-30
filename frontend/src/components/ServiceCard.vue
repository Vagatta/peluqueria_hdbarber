<script setup lang="ts">
import type { Service } from '@/types'
import { Clock, Scissors } from 'lucide-vue-next'

defineProps<{ service: Service; selected?: boolean }>()
defineEmits<{ (e: 'select', s: Service): void }>()

function fmtPrice(c: number) { return (c / 100).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' }) }
</script>

<template>
  <button
    type="button"
    @click="$emit('select', service)"
    class="group relative bg-surface-container border rounded-lg p-5 text-left w-full transition-all duration-200 hover:bg-surface-container-high"
    :class="selected ? 'border-primary shadow-edge' : 'border-outline-variant'"
  >
    <!-- Silver tag indicator when selected -->
    <span
      v-if="selected"
      class="absolute top-3 right-3 chip-solid !py-0.5 !px-2 text-[10px]"
    >SELECCIONADO</span>

    <div class="flex items-start gap-4">
      <div class="shrink-0 w-12 h-12 rounded bg-surface border border-outline-variant grid place-items-center text-on-surface-variant group-hover:text-primary transition-colors">
        <Scissors class="w-5 h-5" />
      </div>
      <div class="flex-1 min-w-0">
        <h3 class="font-display text-h2 leading-tight text-on-surface uppercase tracking-tight">{{ service.name }}</h3>
        <p v-if="service.description" class="t-muted text-sm mt-1 line-clamp-2">{{ service.description }}</p>
      </div>
    </div>

    <div class="mt-5 pt-4 divider flex items-end justify-between">
      <span class="inline-flex items-center gap-2 t-label-muted">
        <Clock class="w-3.5 h-3.5" /> {{ service.duration_minutes }} MIN
      </span>
      <span class="font-display text-h2 text-on-surface">{{ fmtPrice(service.price_cents) }}</span>
    </div>
  </button>
</template>
