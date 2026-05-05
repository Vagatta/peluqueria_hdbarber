<script setup lang="ts">
import { onMounted, ref } from 'vue'

const emit = defineEmits<{ (e: 'done'): void }>()

const visible = ref(true)

onMounted(() => {
  // Give fonts / first paint 1.8s then dismiss
  setTimeout(() => {
    visible.value = false
    setTimeout(() => emit('done'), 740)
  }, 1800)
})
</script>

<template>
  <Transition name="loader-slide">
    <div v-if="visible" class="page-loader">
      <!-- Horizontal rule above -->
      <div style="width:min(260px,55vw);height:1px;background:rgba(255,255,255,0.12);margin-bottom:2rem;" />

      <!-- Logo -->
      <div class="page-loader__logo">HDBARBER</div>

      <!-- Tagline -->
      <p style="margin-top:0.75rem;font-size:0.65rem;letter-spacing:0.38em;text-transform:uppercase;color:rgba(241,238,232,0.38);">
        Precision &nbsp;·&nbsp; Tradition
      </p>

      <!-- Progress bar -->
      <div class="page-loader__bar-track" style="margin-top:2.5rem;">
        <div class="page-loader__bar" />
      </div>
    </div>
  </Transition>
</template>
