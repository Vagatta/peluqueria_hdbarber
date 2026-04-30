<script setup lang="ts">
import { computed, ref } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps<{ modelValue: string }>()
const emit = defineEmits<{ (e: 'update:modelValue', v: string): void }>()

const cursor = ref(new Date(props.modelValue || new Date()))

const monthLabel = computed(() => cursor.value.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' }))

function startOfMonthGrid(d: Date) {
  const first = new Date(d.getFullYear(), d.getMonth(), 1)
  const day = (first.getDay() + 6) % 7 // monday=0
  first.setDate(first.getDate() - day)
  return first
}

const days = computed(() => {
  const start = startOfMonthGrid(cursor.value)
  return Array.from({ length: 42 }).map((_, i) => {
    const d = new Date(start); d.setDate(start.getDate() + i); return d
  })
})

function iso(d: Date) {
  // Usar métodos locales para evitar desfase UTC → día anterior en zonas UTC+N
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}
function isSameMonth(d: Date) { return d.getMonth() === cursor.value.getMonth() }
function isPast(d: Date) {
  const today = new Date(); today.setHours(0,0,0,0)
  return d < today
}
function isSelected(d: Date) { return iso(d) === props.modelValue }

function pick(d: Date) {
  if (isPast(d)) return
  emit('update:modelValue', iso(d))
}
function shift(n: number) {
  const d = new Date(cursor.value); d.setMonth(d.getMonth() + n); cursor.value = d
}
</script>

<template>
  <div class="card h-full flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <button class="p-2 rounded text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors" @click="shift(-1)" aria-label="Mes anterior">
        <ChevronLeft class="w-4 h-4" />
      </button>
      <span class="font-display text-h2 capitalize text-on-surface">{{ monthLabel }}</span>
      <button class="p-2 rounded text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors" @click="shift(1)" aria-label="Mes siguiente">
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
    <div class="grid grid-cols-7 text-center mb-2">
      <span v-for="d in ['L','M','X','J','V','S','D']" :key="d" class="t-micro py-1">{{ d }}</span>
    </div>
    <div class="grid grid-cols-7 gap-1 flex-1">
      <button
        v-for="d in days" :key="d.toISOString()"
        @click="pick(d)"
        :disabled="isPast(d)"
        class="aspect-square rounded text-sm transition-colors"
        :class="[
          isSelected(d)
            ? 'bg-primary text-black font-semibold shadow-edge'
            : [
                isSameMonth(d) ? 'text-on-surface' : 'text-on-surface-variant/40',
                'hover:bg-surface-container-high border border-transparent hover:border-outline-variant'
              ],
          isPast(d) ? 'opacity-25 cursor-not-allowed hover:bg-transparent hover:border-transparent' : ''
        ]"
      >{{ d.getDate() }}</button>
    </div>
  </div>
</template>
