<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

type Variant = 'primary' | 'secondary'

const props = defineProps<{
  to?: string
  href?: string
  variant?: Variant
  type?: 'button' | 'submit'
}>()

const variant = computed<Variant>(() => props.variant ?? 'primary')
const base = 'group inline-flex items-center justify-center gap-3 h-14 px-8 uppercase tracking-[0.12em] text-[12px] font-medium transition-all duration-300 select-none shine relative'
const styles = computed(() => variant.value === 'primary'
  ? `${base} bg-[#F1EEE8] text-black hover:bg-white hover:-translate-y-[2px]`
  : `${base} bg-transparent text-[#F1EEE8] border border-white/10 hover:border-white/30 hover:-translate-y-[2px]`
)
</script>

<template>
  <RouterLink v-if="props.to" :to="props.to" :class="styles"><slot /></RouterLink>
  <a v-else-if="props.href" :href="props.href" :class="styles"><slot /></a>
  <button v-else :type="props.type ?? 'button'" :class="styles"><slot /></button>
</template>
