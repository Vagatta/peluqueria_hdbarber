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
    class="group relative text-left w-full transition-all duration-200 hover:bg-white/[0.03]"
    :class="selected ? 'bg-white/[0.05] border border-white/30' : 'bg-[#1a1a1c] border border-white/10'"
    style="font-family: 'Space Grotesk', sans-serif;"
  >
    <!-- Selected indicator -->
    <span
      v-if="selected"
      class="absolute top-3 right-3 px-2 py-0.5 text-[10px] uppercase tracking-wider font-medium bg-white text-black"
    >Seleccionado</span>

    <div class="p-5">
      <div class="flex items-start gap-4">
        <div class="shrink-0 w-11 h-11 border border-white/10 grid place-items-center text-white/60 group-hover:text-white transition-colors">
          <Scissors class="w-5 h-5" />
        </div>
        <div class="flex-1 min-w-0">
          <h3 class="text-sm font-semibold uppercase tracking-wide text-white">{{ service.name }}</h3>
          <p v-if="service.description" class="text-xs text-white/50 mt-1 line-clamp-2">{{ service.description }}</p>
        </div>
      </div>

      <div class="mt-4 pt-4 border-t border-white/10 flex items-end justify-between">
        <span class="inline-flex items-center gap-2 text-[10px] uppercase tracking-wider text-white/40 font-medium">
          <Clock class="w-3 h-3" /> {{ service.duration_minutes }} MIN
        </span>
        <span class="text-lg font-bold text-white">{{ fmtPrice(service.price_cents) }}</span>
      </div>
    </div>
  </button>
</template>
