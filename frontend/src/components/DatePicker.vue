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
  <div class="card h-full flex flex-col !bg-[#0d0d0d] !border-white/15">
    <div class="flex items-center justify-between mb-6">
      <button class="p-2 text-textSecondary hover:text-primaryText hover:bg-white/5 transition-colors" @click="shift(-1)" aria-label="Mes anterior">
        <ChevronLeft class="w-4 h-4" />
      </button>
      <span class="font-display text-4xl md:text-5xl capitalize tracking-wide text-primaryText">{{ monthLabel }}</span>
      <button class="p-2 text-textSecondary hover:text-primaryText hover:bg-white/5 transition-colors" @click="shift(1)" aria-label="Mes siguiente">
        <ChevronRight class="w-4 h-4" />
      </button>
    </div>
    <div class="grid grid-cols-7 text-center mb-3">
      <span v-for="d in ['L','M','X','J','V','S','D']" :key="d" class="text-[11px] uppercase tracking-[0.2em] text-textSecondary py-1">{{ d }}</span>
    </div>
    <div class="grid grid-cols-7 gap-2 flex-1">
      <button
        v-for="d in days" :key="d.toISOString()"
        @click="pick(d)"
        :disabled="isPast(d)"
        class="aspect-square text-base md:text-lg transition-all border"
        :class="[
          isSelected(d)
            ? 'bg-primaryText text-black font-semibold border-primaryText shadow-[0_0_0_1px_rgba(255,255,255,0.35)]'
            : [
                isSameMonth(d) ? 'text-primaryText' : 'text-textMuted',
                'bg-transparent border-transparent hover:bg-white/5 hover:border-white/15'
              ],
          isPast(d) ? '!text-white/18 cursor-not-allowed hover:bg-transparent hover:border-transparent' : ''
        ]"
      >{{ d.getDate() }}</button>
    </div>
  </div>
</template>
