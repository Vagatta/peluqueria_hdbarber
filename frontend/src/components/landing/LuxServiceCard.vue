<script setup lang="ts">
import { Clock } from 'lucide-vue-next'

defineProps<{
  index: number
  title: string
  duration: string
  price: string
  description?: string
}>()
defineEmits<{ (e: 'select'): void }>()
</script>

<template>
  <button
    type="button"
    @click="$emit('select')"
    class="group relative text-left w-full h-full rounded-3xl p-8 bg-surfaceDark border border-borderHair
           transition-all duration-500 hover:-translate-y-1 hover:border-white/20 overflow-hidden
           flex flex-col"
  >
    <!-- soft spotlight on hover -->
    <span class="pointer-events-none absolute -inset-px rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-700"
      style="background: radial-gradient(500px circle at var(--mx,50%) var(--my,0%), rgba(255,255,255,0.06), transparent 40%);"
    />
    <span class="absolute inset-0 noise-overlay rounded-3xl" />

    <!-- Number top-right -->
    <div class="relative flex items-start justify-end shrink-0">
      <span class="font-display text-2xl tracking-wider text-primaryText/45">
        {{ String(index).padStart(2, '0') }}
      </span>
    </div>

    <!-- Title — fixed min-height so 1-line and 2-line titles take same space -->
    <div class="relative mt-4 min-h-[5.5rem] flex items-start shrink-0">
      <h3 class="font-display text-3xl md:text-4xl uppercase tracking-[0.04em] text-primaryText leading-[1.05]">
        {{ title }}
      </h3>
    </div>

    <!-- Description — grows to fill available space -->
    <div class="relative flex-1 mt-3">
      <p v-if="description" class="text-sm text-textSecondary leading-relaxed">
        {{ description }}
      </p>
    </div>

    <!-- Footer pinned to bottom -->
    <div class="relative mt-6 pt-5 border-t border-borderHair flex items-center justify-between shrink-0">
      <span class="inline-flex items-center gap-2 text-[11px] uppercase tracking-[0.24em] text-textSecondary">
        <Clock class="w-3.5 h-3.5" /> {{ duration }}
      </span>
      <span class="font-display text-3xl tracking-wider text-primaryText">{{ price }}</span>
    </div>
  </button>
</template>
