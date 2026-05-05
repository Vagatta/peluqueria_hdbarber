<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  text: string
  className?: string
  delayStep?: number
}>()

const lines = computed(() => props.text.split('\n'))
const step = computed(() => props.delayStep ?? 70)
</script>

<template>
  <h1 :class="props.className" class="leading-[0.9] tracking-[-0.04em] text-balance">
    <span v-for="(line, li) in lines" :key="li" class="block overflow-hidden">
      <span
        v-for="(word, wi) in line.split(' ')"
        :key="wi"
        class="inline-block will-change-transform pull-word mr-[0.22em] last:mr-0"
        :style="{ animationDelay: `${(li * 4 + wi) * step}ms` }"
      >{{ word }}</span>
    </span>
  </h1>
</template>

<style scoped>
.pull-word {
  transform: translateY(110%);
  opacity: 0;
  animation: pullUp 900ms cubic-bezier(.2,.7,.15,1) forwards;
}
@keyframes pullUp {
  to { transform: translateY(0); opacity: 1; }
}
</style>
