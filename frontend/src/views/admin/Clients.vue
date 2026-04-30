<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { api } from '@/api/client'
import { Search } from 'lucide-vue-next'

interface Client { id: number; name: string; email: string; phone?: string; created_at: string; appointments_count: number }
const items = ref<Client[]>([])
const loading = ref(true)
const search = ref('')

onMounted(async () => {
  try { const { data } = await api.get<Client[]>('/api/admin/clients'); items.value = data }
  finally { loading.value = false }
})

const filtered = computed(() => {
  const q = search.value.toLowerCase().trim()
  if (!q) return items.value
  return items.value.filter(c => c.name.toLowerCase().includes(q) || c.email.toLowerCase().includes(q))
})
</script>

<template>
  <div>
    <div class="mb-gutter">
      <span class="t-label-muted">Cartera</span>
      <h1 class="font-display text-display mt-1">Clientes</h1>
    </div>

    <div class="field max-w-sm mb-gutter">
      <Search class="input-icon w-4 h-4" />
      <input v-model="search" placeholder="Buscar por nombre o email…" class="input input-with-icon" />
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left">
            <th class="t-label-muted py-4 px-6">Nombre</th>
            <th class="t-label-muted">Email</th>
            <th class="t-label-muted">Teléfono</th>
            <th class="t-label-muted">Citas</th>
            <th class="t-label-muted">Alta</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="py-6 px-6 text-center t-muted">Cargando…</td></tr>
          <tr v-else-if="!filtered.length"><td colspan="5" class="py-6 px-6 text-center t-muted">Sin resultados.</td></tr>
          <tr v-for="c in filtered" :key="c.id" class="border-t border-surface-container-highest hover:bg-surface-container-low transition-colors">
            <td class="py-3 px-6">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-surface-container-high border border-outline-variant grid place-items-center text-label uppercase">
                  {{ c.name?.charAt(0) || '?' }}
                </div>
                <span class="text-on-surface">{{ c.name }}</span>
              </div>
            </td>
            <td class="text-on-surface-variant">{{ c.email }}</td>
            <td class="text-on-surface-variant">{{ c.phone || '—' }}</td>
            <td><span class="chip">{{ c.appointments_count }}</span></td>
            <td class="t-muted text-xs">{{ new Date(c.created_at).toLocaleDateString('es-ES') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
