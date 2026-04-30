<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useServicesStore } from '@/stores/services'
import { useAuthStore } from '@/stores/auth'
import { useAppointmentsStore } from '@/stores/appointments'
import ServiceCard from '@/components/ServiceCard.vue'
import { Scissors, Calendar, ShieldCheck, CalendarCheck, Award, ArrowRight, Clock } from 'lucide-vue-next'

const services = useServicesStore()
const auth = useAuthStore()
const appts = useAppointmentsStore()
const router = useRouter()

function selectService(id: number) {
  if (auth.isAuthenticated) {
    router.push({ path: '/book', query: { service: id } })
  } else {
    router.push({ path: '/login', query: { redirect: '/book', service: id } })
  }
}

onMounted(() => {
  services.fetch()
  if (auth.isAuthenticated) appts.fetch()
})

const myItems = computed(() =>
  appts.items.filter(a => a.user_id === auth.user?.id)
)

const upcoming = computed(() => {
  const now = Date.now()
  return myItems.value
    .filter(a => a.status !== 'cancelled' && new Date(a.start_at).getTime() > now)
    .sort((a, b) => a.start_at.localeCompare(b.start_at))[0] || null
})

const recent = computed(() =>
  myItems.value
    .filter(a => a.status === 'completed' || new Date(a.start_at).getTime() < Date.now())
    .slice(0, 3)
)

const loyalty = computed(() => {
  const completed = myItems.value.filter(a => a.status === 'completed').length
  return { points: Math.min(500, completed * 100), goal: 500 }
})

function fmtDate(iso: string) {
  const d = new Date(iso)
  return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' })
}
function fmtTime(iso: string) {
  return new Date(iso).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
}
function eur(c: number) { return (c / 100).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' }) }
</script>

<template>
  <!-- ============== GUEST LANDING ============== -->
  <div v-if="!auth.isAuthenticated" class="page py-margin">
    <section class="text-center max-w-2xl mx-auto pb-12">
      <span class="t-micro">PRECISION & TRADITION</span>
      <h1 class="font-display text-display mt-3">
        HDBarber.<br/>
        <span class="text-on-surface-variant">Tu estilo, dominado.</span>
      </h1>
      <p class="t-muted mt-4 max-w-lg mx-auto">
        Reserva tu próximo corte. Pago online seguro, recordatorios automáticos y la precisión de los mejores barberos.
      </p>
      <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
        <RouterLink to="/register" class="btn-primary">
          Reservar cita <ArrowRight class="w-4 h-4" />
        </RouterLink>
        <RouterLink to="/login" class="btn-secondary">Ya tengo cuenta</RouterLink>
      </div>
    </section>

    <section class="grid md:grid-cols-3 gap-gutter mb-margin">
      <div class="card-sm">
        <Scissors class="w-5 h-5 text-primary mb-3" />
        <div class="t-label mb-1">Profesionales top</div>
        <p class="t-muted text-sm">Estilistas con años de experiencia y formación continua.</p>
      </div>
      <div class="card-sm">
        <Calendar class="w-5 h-5 text-primary mb-3" />
        <div class="t-label mb-1">Reserva 24/7</div>
        <p class="t-muted text-sm">Elige servicio, día y hora desde cualquier dispositivo.</p>
      </div>
      <div class="card-sm">
        <ShieldCheck class="w-5 h-5 text-primary mb-3" />
        <div class="t-label mb-1">Pago seguro</div>
        <p class="t-muted text-sm">Procesado por Stripe con cifrado SSL extremo a extremo.</p>
      </div>
    </section>

    <section>
      <h2 class="section-title">Nuestros servicios</h2>
      <div v-if="services.loading" class="t-muted">Cargando…</div>
      <div v-else-if="services.error" class="card border-error bg-error-container">
        <p class="text-on-error-container">{{ services.error }}</p>
      </div>
      <div v-else-if="services.items.length === 0" class="t-muted">No hay servicios disponibles.</div>
      <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
        <ServiceCard v-for="s in services.items" :key="s.id" :service="s" @select="selectService(s.id)" />
      </div>
    </section>
  </div>

  <!-- ============== AUTH PANEL (CLIENT DASHBOARD) ============== -->
  <div v-else class="page py-margin">
    <!-- Welcome -->
    <div class="mb-gutter flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
      <div>
        <span class="t-label-muted">PANEL</span>
        <h1 class="font-display text-display mt-1">Hola, {{ auth.user?.name?.split(' ')[0] }}.</h1>
      </div>
      <RouterLink to="/book" class="btn-primary">
        <Calendar class="w-4 h-4" /> Reservar ahora
      </RouterLink>
    </div>

    <!-- Bento grid -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
      <!-- Próxima cita -->
      <div class="md:col-span-8 card silver-glow relative overflow-hidden">
        <template v-if="upcoming">
          <div class="flex justify-between items-start mb-8">
            <div>
              <div class="flex items-center gap-2 mb-2">
                <CalendarCheck class="w-5 h-5 text-primary" />
                <span class="t-label">Próxima cita</span>
              </div>
              <div class="font-display text-display leading-none">
                {{ fmtDate(upcoming.start_at) }},<br/>{{ fmtTime(upcoming.start_at) }}
              </div>
            </div>
<span class="chip" :class="upcoming.status === 'confirmed' ? 'chip-solid' : upcoming.status === 'completed' ? 'chip-success' : 'chip-warn'">
              {{ { pending: 'Pendiente', confirmed: 'Confirmada', completed: 'Completada', cancelled: 'Cancelada', no_show: 'No asistió' }[upcoming.status] || upcoming.status }}
            </span>
          </div>
          <div class="pt-4 divider flex items-end justify-between gap-3">
            <div>
              <p class="t-body-lg">{{ upcoming.service?.name }}</p>
              <p class="t-muted text-sm flex items-center gap-2 mt-1">
                <Scissors class="w-4 h-4" /> con {{ upcoming.employee?.name || 'cualquier estilista' }}
              </p>
            </div>
            <RouterLink to="/appointments" class="btn-secondary !py-2 !px-4">Gestionar</RouterLink>
          </div>
        </template>
        <template v-else>
          <div class="text-center py-8">
            <Calendar class="w-10 h-10 mx-auto text-on-surface-variant/40 mb-4" />
            <h3 class="font-display text-h2 mb-2">Sin citas próximas</h3>
            <p class="t-muted mb-6">Reserva tu próxima visita en pocos clics.</p>
            <RouterLink to="/book" class="btn-primary inline-flex">Reservar ahora <ArrowRight class="w-4 h-4" /></RouterLink>
          </div>
        </template>
      </div>

      <!-- Fidelización -->
      <div class="md:col-span-4 card flex flex-col justify-between">
        <div>
          <div class="flex items-center gap-2 mb-4">
            <Award class="w-5 h-5 text-primary" />
            <span class="t-label">Estado de fidelización</span>
          </div>
          <div class="flex items-end gap-2 mb-6">
            <span class="font-display text-display leading-none">{{ loyalty.points }}</span>
            <span class="t-muted pb-1">/ {{ loyalty.goal }} pts</span>
          </div>
        </div>
        <div>
          <div class="w-full h-2 rounded-full bg-surface border border-surface-container-highest overflow-hidden mb-2">
            <div class="bg-primary h-full" :style="{ width: (loyalty.points / loyalty.goal * 100) + '%' }"></div>
          </div>
          <p class="t-muted text-sm">{{ loyalty.goal - loyalty.points }} puntos para tu próximo servicio de cortesía.</p>
        </div>
      </div>

      <!-- Acceso rápido mobile -->
      <div class="md:hidden col-span-1">
        <RouterLink to="/book" class="btn-primary w-full">
          <Calendar class="w-4 h-4" /> Reservar un servicio
        </RouterLink>
      </div>

      <!-- Visitas recientes -->
      <div class="md:col-span-12 mt-2">
        <h2 class="section-title">Visitas recientes</h2>
        <div v-if="!recent.length" class="card text-center t-muted">
          Cuando completes tu primera cita aparecerá aquí.
        </div>
        <ul v-else class="flex flex-col">
          <li
            v-for="a in recent" :key="a.id"
            class="flex items-center justify-between py-4 border-b border-surface-container-highest hover:bg-surface-container/50 transition-colors px-2 -mx-2 rounded"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded bg-surface border border-outline-variant grid place-items-center text-on-surface-variant shrink-0">
                <Scissors class="w-5 h-5" />
              </div>
              <div>
                <p class="t-label">{{ a.service?.name }}</p>
                <p class="t-muted text-sm flex items-center gap-2 mt-1">
                  <Clock class="w-3.5 h-3.5" /> {{ fmtDate(a.start_at) }} · {{ a.employee?.name || '—' }}
                </p>
              </div>
            </div>
            <div class="text-right">
              <p class="t-body-lg">{{ eur(a.service?.price_cents || 0) }}</p>
              <RouterLink to="/book" class="btn-link !text-xs mt-1">Volver a reservar</RouterLink>
            </div>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
