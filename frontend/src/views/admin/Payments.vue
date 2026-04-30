<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api } from '@/api/client'
import type { Payment } from '@/types'

const items = ref<Payment[]>([])
const loading = ref(true)

onMounted(async () => {
  try { const { data } = await api.get<Payment[]>('/api/payments'); items.value = data }
  finally { loading.value = false }
})

function eur(c: number, cur = 'EUR') { return (c/100).toLocaleString('es-ES', { style: 'currency', currency: cur.toUpperCase() }) }
function chipClass(s: string) {
  return ({ succeeded:'chip-success', pending:'chip-warn', processing:'chip-warn', failed:'chip-danger', refunded:'chip-info', cancelled:'chip' } as Record<string,string>)[s] || 'chip'
}
</script>

<template>
  <div>
    <div class="mb-gutter">
      <span class="t-label-muted">Tesorería</span>
      <h1 class="font-display text-display mt-1">Pagos</h1>
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left">
            <th class="t-label-muted py-4 px-6">Fecha</th>
            <th class="t-label-muted">Cliente</th>
            <th class="t-label-muted">Servicio</th>
            <th class="t-label-muted text-right pr-6">Importe</th>
            <th class="t-label-muted">Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="py-6 px-6 text-center t-muted">Cargando…</td></tr>
          <tr v-else-if="!items.length"><td colspan="5" class="py-6 px-6 text-center t-muted">Sin pagos registrados.</td></tr>
          <tr v-for="p in items" :key="p.id" class="border-t border-surface-container-highest hover:bg-surface-container-low transition-colors">
            <td class="py-3 px-6 t-muted text-xs">{{ new Date(p.created_at).toLocaleString('es-ES') }}</td>
            <td class="text-on-surface">{{ p.user?.name || '—' }}</td>
            <td class="text-on-surface-variant">{{ p.appointment?.service?.name || '—' }}</td>
            <td class="font-display text-on-surface text-right pr-6">{{ eur(p.amount_cents, p.currency) }}</td>
            <td><span class="chip" :class="chipClass(p.status)">{{ p.status }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
