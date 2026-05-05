<script setup lang="ts">
import { ref } from 'vue'
import { RouterView } from 'vue-router'
import AppShell from '@/components/AppShell.vue'
import AppLoader from '@/components/AppLoader.vue'

const loaderDone = ref(false)
</script>

<template>
  <AppLoader v-if="!loaderDone" @done="loaderDone = true" />
  <AppShell>
    <RouterView />
  </AppShell>
</template>
