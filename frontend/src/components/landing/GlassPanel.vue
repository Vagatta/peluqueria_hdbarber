<script setup lang="ts">
defineProps<{ class?: string }>()
</script>

<template>
  <div class="glass-dark rounded-2xl" :class="$props.class">
    <slot />
  </div>
</template>
