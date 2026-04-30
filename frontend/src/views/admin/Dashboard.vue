<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { api } from '@/api/client'
import { useAppointmentsStore } from '@/stores/appointments'
import type { DashboardStats, Appointment } from '@/types'
import { Calendar, Bell, BadgeEuro, Users, Scissors, Plus, Check, X, CreditCard, CheckCheck, ArrowRight } from 'lucide-vue-next'
import { RouterLink } from 'vue-router'

const appts = useAppointmentsStore()
const stats = ref<DashboardStats | null>(null)
const loading = ref(true)
const actionLoading = ref<number | null>(null)

async function loadDashboard() {
  const { data } = await api.get<DashboardStats>('/api/admin/dashboard')
  stats.value = data
}

onMounted(async () => {
  try { await loadDashboard() } finally { loading.value = false }
})

async function applyAction(a: Appointment, status?: string, paymentStatus?: string) {
  actionLoading.value = a.id
  try {
    await appts.adminUpdate(a.id, {
      ...(status ? { status: status as Appointment['status'] } : {}),
      ...(paymentStatus ? { payment_status: paymentStatus } : {}),
    })
    await loadDashboard()
  } catch (e: any) {
    alert(e?.response?.data?.message || 'Error')
  } finally {
    actionLoading.value = null
  }
}

function eur(cents: number) { return (cents / 100).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' }) }
function fmtTime(d: string) { return new Date(d).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }
function durationMin(start: string, end?: string) {
  if (!end) return null
  return Math.round((new Date(end).getTime() - new Date(start).getTime()) / 60000)
}

function dateLabel(iso: string): string | null {
  const d = new Date(iso)
  const now = new Date()
  const todayStr = `${now.getFullYear()}-${now.getMonth()}-${now.getDate()}`
  const tomorrowStr = `${now.getFullYear()}-${now.getMonth()}-${now.getDate() + 1}`
  const dStr = `${d.getFullYear()}-${d.getMonth()}-${d.getDate()}`
  if (dStr === todayStr) return null
  if (dStr === tomorrowStr) return 'Mañana'
  return d.toLocaleDateString('es-ES', { weekday: 'short', day: '2-digit', month: 'short' })
}

const today = computed(() => new Date().toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }).toUpperCase())
</script>

<template>
  <div>
    <div class="mb-gutter">
      <span class="t-label-muted">Resumen</span>
      <h1 class="font-display text-display mt-1">Resumen Diario</h1>
      <p class="t-muted mt-2 max-w-xl">Métricas en vivo y próximo horario para la planta principal. Mantén la precisión.</p>
      <div class="mt-4 inline-flex items-center gap-2 t-label-muted">
        <Calendar class="w-4 h-4" /> {{ today }}
      </div>
    </div>

    <div v-if="loading" class="t-muted">Cargando…</div>

    <div v-else-if="stats" class="space-y-gutter">
      <!-- KPIs -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-gutter">
        <div class="card relative overflow-hidden">
          <span class="t-label-muted">Total de reservas hoy</span>
          <div class="flex items-baseline gap-2 mt-3">
            <span class="font-display text-display leading-none">{{ String(stats.appointments_today).padStart(2, '0') }}</span>
            <span class="t-muted">citas</span>
          </div>
          <Scissors class="w-8 h-8 text-on-surface-variant/20 absolute right-5 bottom-5" />
        </div>

        <div class="card relative overflow-hidden">
          <span class="t-label-muted">Esta semana</span>
          <div class="flex items-baseline gap-2 mt-3">
            <span class="font-display text-display leading-none">{{ String(stats.appointments_week).padStart(2, '0') }}</span>
            <span class="t-muted">citas</span>
          </div>
          <Bell class="w-8 h-8 text-on-surface-variant/20 absolute right-5 bottom-5" />
        </div>

        <div class="card relative overflow-hidden">
          <span class="t-label-muted">Ingresos del mes</span>
          <div class="font-display text-display leading-none mt-3">{{ eur(stats.revenue_month_cents) }}</div>
          <div class="w-full h-1 bg-surface-container-highest mt-4 rounded-full overflow-hidden">
            <div class="bg-primary h-full w-2/3"></div>
          </div>
          <BadgeEuro class="w-8 h-8 text-on-surface-variant/20 absolute right-5 top-5" />
        </div>

        <div class="card relative overflow-hidden">
          <span class="t-label-muted">Clientes registrados</span>
          <div class="font-display text-display leading-none mt-3">{{ stats.clients_total }}</div>
          <Users class="w-8 h-8 text-on-surface-variant/20 absolute right-5 bottom-5" />
        </div>
      </div>

      <!-- Próximas citas (timeline) -->
      <div class="card">
        <div class="flex items-center justify-between mb-6">
          <h2 class="font-display text-h2">Próximas citas</h2>
          <RouterLink to="/admin/calendar" class="btn-link">
            Horario completo <ArrowRight class="w-3.5 h-3.5" />
          </RouterLink>
        </div>

        <div v-if="!stats.upcoming.length" class="text-center py-12 t-muted">
          No hay próximas citas.
        </div>

        <ol v-else class="relative">
          <li
            v-for="a in stats.upcoming" :key="a.id"
            class="grid grid-cols-[80px_1fr] gap-4 pb-5 last:pb-0 relative"
          >
            <div class="text-right">
              <div class="font-display text-h2 leading-none">{{ fmtTime(a.start_at) }}</div>
              <div class="t-micro mt-1" v-if="durationMin(a.start_at, a.end_at)">{{ durationMin(a.start_at, a.end_at) }} MIN</div>
              <div v-if="dateLabel(a.start_at)" class="mt-1.5 text-[10px] font-display uppercase tracking-wide px-1.5 py-0.5 rounded bg-surface-container-high text-on-surface-variant inline-block">{{ dateLabel(a.start_at) }}</div>
            </div>
            <div class="border-l-2 border-surface-container-highest pl-4 relative">
              <span class="absolute -left-[5px] top-2 w-2 h-2 rounded-full bg-primary"></span>
              <div class="card-sm !p-4">
                <p class="t-label">{{ a.service?.name }}</p>
                <p class="t-muted text-sm mt-1">{{ a.employee?.name || 'Sin asignar' }}</p>
                <div class="mt-2 flex gap-1.5 flex-wrap">
                  <span class="chip" :class="a.status === 'confirmed' ? 'chip-solid' : a.status === 'completed' ? 'chip-success' : 'chip-warn'">{{
                    { pending: 'Pendiente', confirmed: 'Confirmada', completed: 'Completada', cancelled: 'Cancelada', no_show: 'No asistió' }[a.status] || a.status
                  }}</span>
                  <span class="chip" :class="a.payment_status === 'paid' ? 'chip-success' : 'chip-warn'">{{
                    { unpaid: 'Sin pagar', paid: 'Pagado', refunded: 'Reembolsado', failed: 'Error pago' }[a.payment_status] || a.payment_status
                  }}</span>
                </div>
                <!-- Acciones rápidas -->
                <div class="mt-3 flex gap-1.5 flex-wrap" v-if="a.status !== 'cancelled'">
                  <button
                    v-if="a.payment_status !== 'paid'"
                    class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1"
                    :disabled="actionLoading === a.id"
                    @click="applyAction(a, undefined, 'paid')"
                    title="Marcar como pagado"
                  ><CreditCard class="w-3 h-3" />Pagado</button>
                  <button
                    v-if="a.status !== 'completed'"
                    class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1"
                    :disabled="actionLoading === a.id"
                    @click="applyAction(a, 'completed')"
                    title="Marcar como completada"
                  ><Check class="w-3 h-3" />Completar</button>
                  <button
                    v-if="a.status !== 'completed' || a.payment_status !== 'paid'"
                    class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1"
                    :disabled="actionLoading === a.id"
                    @click="applyAction(a, 'completed', 'paid')"
                    title="Completar y marcar como pagado"
                  ><CheckCheck class="w-3 h-3" />Completar + Pagar</button>
                  <button
                    class="btn-ghost !py-1 !px-2 text-xs flex items-center gap-1 text-error hover:bg-error/10"
                    :disabled="actionLoading === a.id"
                    @click="applyAction(a, 'cancelled')"
                    title="Cancelar cita"
                  ><X class="w-3 h-3" />Cancelar</button>
                </div>
              </div>
            </div>
          </li>
        </ol>

        <RouterLink
          to="/admin/calendar"
          class="absolute md:relative bottom-6 right-6 md:mt-6 md:bottom-auto md:right-auto md:ml-auto flex items-center justify-center w-12 h-12 rounded bg-primary text-on-primary hover:bg-primary-fixed transition-colors"
          aria-label="Añadir cita"
        >
          <Plus class="w-5 h-5" />
        </RouterLink>
      </div>
    </div>
  </div>
</template>
