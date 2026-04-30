<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useAppointmentsStore } from '@/stores/appointments'
import type { Appointment } from '@/types'
import { X, CreditCard, Scissors, Calendar, Clock, CheckCircle2, CircleDot, CircleX, CircleAlert, CircleOff } from 'lucide-vue-next'
import { statusLabels, paymentLabels, statusChipClasses, paymentChipClasses } from '@/utils/labels'

const appts = useAppointmentsStore()
onMounted(() => appts.fetch())

const upcoming = computed(() =>
  appts.items
    .filter(a => a.status !== 'cancelled' && new Date(a.start_at).getTime() >= Date.now())
    .sort((a, b) => a.start_at.localeCompare(b.start_at))
)
const past = computed(() =>
  appts.items
    .filter(a => a.status === 'cancelled' || new Date(a.start_at).getTime() < Date.now())
    .sort((a, b) => b.start_at.localeCompare(a.start_at))
)

function fmt(d: string) {
  return new Date(d).toLocaleString('es-ES', { weekday: 'short', day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}
function statusChip(s: string) {
  return statusChipClasses[s] || 'chip'
}
function paymentChip(s: string) {
  return paymentChipClasses[s] || 'chip'
}

const statusIcons: Record<string, any> = {
  pending: CircleAlert,
  confirmed: CheckCircle2,
  completed: CircleDot,
  cancelled: CircleX,
  no_show: CircleOff
}

const statusCardClass: Record<string, string> = {
  pending: '',
  confirmed: '',
  completed: 'opacity-75',
  cancelled: 'opacity-50',
  no_show: 'opacity-50'
}

const statusDotClass: Record<string, string> = {
  pending: 'bg-yellow-400',
  confirmed: 'bg-primary',
  completed: 'bg-emerald-400',
  cancelled: 'bg-red-400',
  no_show: 'bg-zinc-500'
}

const statusBgClass: Record<string, string> = {
  pending: 'bg-yellow-500/5',
  confirmed: 'bg-primary/5',
  completed: 'bg-emerald-500/5',
  cancelled: '',
  no_show: ''
}

const statusIconClass: Record<string, string> = {
  pending: 'text-yellow-400',
  confirmed: 'text-primary',
  completed: 'text-emerald-400',
  cancelled: 'text-red-400',
  no_show: 'text-zinc-400'
}

async function cancel(a: Appointment) {
  if (!confirm('¿Cancelar esta cita?')) return
  try { await appts.cancel(a.id) } catch (e: any) { alert(e?.response?.data?.message || 'Error') }
}

async function pay(a: Appointment) {
  try {
    const r = await appts.checkout(a.id)
    window.location.href = r.url
  } catch (e: any) { alert(e?.response?.data?.message || 'Error') }
}
</script>

<template>
  <div class="page py-margin">
    <div class="mb-gutter flex items-end justify-between gap-4">
      <div>
        <span class="t-label-muted">Mi agenda</span>
        <h1 class="font-display text-display mt-1">Mis citas</h1>
      </div>
      <RouterLink to="/book" class="btn-primary"><Calendar class="w-4 h-4" /> Nueva cita</RouterLink>
    </div>

    <div v-if="appts.loading" class="t-muted">Cargando…</div>
    <div v-else-if="!appts.items.length" class="card text-center py-12">
      <Calendar class="w-10 h-10 mx-auto text-on-surface-variant/40 mb-4" />
      <h3 class="font-display text-h2 mb-2">Sin citas todavía</h3>
      <p class="t-muted mb-6">Reserva tu primera visita y empieza tu historial.</p>
      <RouterLink to="/book" class="btn-primary inline-flex">Reservar ahora</RouterLink>
    </div>

    <template v-else>
      <h2 v-if="upcoming.length" class="section-title">Próximas</h2>
      <ul class="space-y-3 mb-margin">
        <li
          v-for="a in upcoming" :key="a.id"
          class="card flex flex-col md:flex-row md:items-center gap-4 transition-opacity relative overflow-hidden"
          :class="[statusCardClass[a.status], statusBgClass[a.status]]"
        >
          <!-- Dot indicador de estado -->
          <span class="absolute top-4 right-4 w-2 h-2 rounded-full" :class="statusDotClass[a.status]"></span>

          <div class="w-11 h-11 rounded-xl border border-outline-variant/50 grid place-items-center shrink-0" :class="statusIconClass[a.status]">
            <component :is="statusIcons[a.status] || Scissors" class="w-5 h-5" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="t-label">{{ a.service?.name }}</p>
            <p class="t-muted text-sm flex items-center gap-2 mt-1">
              <Clock class="w-3.5 h-3.5" />{{ fmt(a.start_at) }} · {{ a.employee?.name || 'Cualquier estilista' }}
            </p>
            <div class="mt-2 flex gap-2 flex-wrap">
              <span class="chip inline-flex items-center gap-1" :class="statusChip(a.status)">
                <component :is="statusIcons[a.status]" class="w-3 h-3" />
                {{ statusLabels[a.status] || a.status }}
              </span>
              <span class="chip" :class="paymentChip(a.payment_status)">{{ paymentLabels[a.payment_status] || a.payment_status }}</span>
            </div>
          </div>
          <div class="flex gap-2 shrink-0 mr-5">
            <button
              v-if="a.payment_status !== 'paid' && a.status !== 'cancelled' && a.status !== 'completed'"
              class="btn-primary !py-2 !px-3" @click="pay(a)"
            >
              <CreditCard class="w-4 h-4" />Pagar
            </button>
            <button
              v-if="a.status !== 'cancelled' && a.status !== 'completed'"
              class="btn-ghost !py-2 !px-3 text-error hover:bg-error/10" @click="cancel(a)"
            >
              <X class="w-4 h-4" />Cancelar
            </button>
          </div>
        </li>
      </ul>

      <h2 v-if="past.length" class="section-title">Historial</h2>
      <ul class="flex flex-col gap-2">
        <li
          v-for="a in past" :key="a.id"
          class="flex items-center justify-between py-3 px-4 rounded-lg hover:bg-surface-container/40 transition-colors"
          :class="statusCardClass[a.status]"
        >
          <div class="flex items-center gap-3 min-w-0">
            <!-- Dot de estado -->
            <span class="w-1.5 h-1.5 rounded-full shrink-0" :class="statusDotClass[a.status]"></span>
            <div class="min-w-0">
              <p class="t-label truncate">{{ a.service?.name }}</p>
              <p class="t-muted text-xs flex items-center gap-1.5 mt-0.5">
                <Clock class="w-3 h-3" />{{ fmt(a.start_at) }}
              </p>
            </div>
          </div>
          <span class="chip inline-flex items-center gap-1 shrink-0" :class="statusChip(a.status)">
            <component :is="statusIcons[a.status]" class="w-3 h-3" />
            {{ statusLabels[a.status] || a.status }}
          </span>
        </li>
      </ul>
    </template>
  </div>
</template>
