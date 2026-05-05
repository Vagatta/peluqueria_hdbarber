<script setup lang="ts">
/**
 * Real 3D procedural barber clipper using TresJS (Three.js for Vue).
 * - Matte black body
 * - Chrome blade with teeth
 * - Dramatic top lighting + rim light
 * - Slow, subtle orbit rotation reacting to mouse parallax
 */
import { onBeforeUnmount, ref, shallowRef } from 'vue'
import { TresCanvas, useRenderLoop } from '@tresjs/core'
import * as THREE from 'three'

// Scene group references
const bodyRef = shallowRef<THREE.Group>()
const targetRotX = ref(0.18)
const targetRotY = ref(-0.35)
const curRotX = ref(0.18)
const curRotY = ref(-0.35)
const t = ref(0)

// Mouse parallax (listens globally)
function onMove(e: MouseEvent) {
  const x = (e.clientX / window.innerWidth - 0.5) * 2
  const y = (e.clientY / window.innerHeight - 0.5) * 2
  targetRotY.value = -0.35 + x * 0.25
  targetRotX.value = 0.18 + y * 0.18
}
if (typeof window !== 'undefined') {
  window.addEventListener('mousemove', onMove, { passive: true })
}
onBeforeUnmount(() => {
  if (typeof window !== 'undefined') window.removeEventListener('mousemove', onMove)
})

const { onLoop } = useRenderLoop()
onLoop(({ delta }) => {
  t.value += delta
  // smooth ease toward target
  curRotX.value += (targetRotX.value - curRotX.value) * 0.05
  curRotY.value += (targetRotY.value - curRotY.value) * 0.05
  if (bodyRef.value) {
    const g = bodyRef.value as any
    g.rotation.x = curRotX.value
    // add slow autonomous orbit
    g.rotation.y = curRotY.value + Math.sin(t.value * 0.25) * 0.12
    g.position.y = Math.sin(t.value * 0.8) * 0.06
  }
})

// Materials tuned for premium look
const bodyMaterial = new THREE.MeshPhysicalMaterial({
  color: '#2a2a2a',
  metalness: 0.7,
  roughness: 0.24,
  clearcoat: 1.0,
  clearcoatRoughness: 0.12,
  reflectivity: 0.8,
})
const chromeMaterial = new THREE.MeshPhysicalMaterial({
  color: '#f4f1eb',
  metalness: 1,
  roughness: 0.08,
  clearcoat: 1,
  clearcoatRoughness: 0.05,
  envMapIntensity: 1.2,
})
const darkChrome = new THREE.MeshPhysicalMaterial({
  color: '#7b7b7b',
  metalness: 1,
  roughness: 0.16,
})
const logoMaterial = new THREE.MeshBasicMaterial({ color: '#F1EEE8', transparent: true, opacity: 0.55 })
</script>

<template>
  <div class="clipper-stage pointer-events-none select-none w-full h-full min-h-[560px]">
    <div class="absolute inset-0 clipper-halo" />
    <TresCanvas
      :alpha="true"
      :antialias="true"
      :dpr="[1, 2]"
    >
      <TresPerspectiveCamera :position="[0, 0.02, 3.15]" :fov="40" />

      <!-- Lighting: cinematic top + rim -->
      <TresAmbientLight :intensity="0.85" color="#ffffff" />
      <TresDirectionalLight :position="[2, 5, 3]" :intensity="5.2" color="#ffffff" />
      <TresDirectionalLight :position="[-3, 2, -2]" :intensity="2.1" color="#d7dde5" />
      <TresPointLight :position="[0, 1.8, 2.5]" :intensity="2.6" color="#ffffff" />
      <TresPointLight :position="[-1.5, -1.0, 2]" :intensity="1.2" color="#f1eee8" />

      <!-- Clipper group -->
      <TresGroup ref="bodyRef" :position="[0, -0.05, 0]" :scale="[1.22, 1.22, 1.22]">
        <!-- Body (rounded box) -->
        <TresMesh :position="[0, -0.6, 0]" :material="bodyMaterial">
          <TresBoxGeometry :args="[1.1, 2.6, 0.7]" />
        </TresMesh>

        <!-- Body side ridges -->
        <TresMesh :position="[0, -0.2, 0.36]" :material="darkChrome">
          <TresBoxGeometry :args="[1.05, 0.04, 0.01]" />
        </TresMesh>
        <TresMesh :position="[0, -0.6, 0.36]" :material="darkChrome">
          <TresBoxGeometry :args="[1.05, 0.04, 0.01]" />
        </TresMesh>
        <TresMesh :position="[0, -1.0, 0.36]" :material="darkChrome">
          <TresBoxGeometry :args="[1.05, 0.04, 0.01]" />
        </TresMesh>

        <!-- HD logo plate -->
        <TresMesh :position="[0, -1.55, 0.352]" :material="logoMaterial">
          <TresPlaneGeometry :args="[0.5, 0.14]" />
        </TresMesh>

        <!-- Blade housing (chrome) -->
        <TresMesh :position="[0, 0.95, 0]" :material="chromeMaterial">
          <TresBoxGeometry :args="[1.35, 0.7, 0.85]" />
        </TresMesh>

        <!-- Blade teeth (individual tiny boxes for realism) -->
        <TresGroup :position="[0, 0.55, 0.44]">
          <TresMesh
            v-for="i in 16" :key="i"
            :position="[-0.6 + (i - 1) * 0.08, 0, 0]"
            :material="chromeMaterial"
          >
            <TresBoxGeometry :args="[0.05, 0.18, 0.08]" />
          </TresMesh>
        </TresGroup>

        <!-- Blade front darker strip -->
        <TresMesh :position="[0, 0.7, 0.43]" :material="darkChrome">
          <TresBoxGeometry :args="[1.3, 0.14, 0.02]" />
        </TresMesh>

        <!-- Power switch detail -->
        <TresMesh :position="[0, 0.2, 0.37]" :material="chromeMaterial">
          <TresBoxGeometry :args="[0.18, 0.08, 0.04]" />
        </TresMesh>
      </TresGroup>

      <!-- Ground contact shadow (dark disk) -->
      <TresMesh :rotation="[-Math.PI / 2, 0, 0]" :position="[0, -2.1, 0]">
        <TresCircleGeometry :args="[1.4, 32]" />
        <TresMeshBasicMaterial color="#000" :transparent="true" :opacity="0.45" />
      </TresMesh>
    </TresCanvas>
  </div>
</template>

<style scoped>
.clipper-stage {
  position: relative;
  isolation: isolate;
}
.clipper-halo {
  z-index: 0;
  background:
    radial-gradient(340px circle at 55% 28%, rgba(241,238,232,0.18), transparent 58%),
    radial-gradient(420px circle at 50% 62%, rgba(255,255,255,0.07), transparent 62%);
  filter: blur(2px);
}
.clipper-stage :deep(canvas) {
  position: relative;
  z-index: 1;
  display: block;
  width: 100% !important;
  height: 100% !important;
  background: transparent !important;
}
</style>
