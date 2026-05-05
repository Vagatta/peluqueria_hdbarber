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
  <!-- ============== GUEST LANDING - INDUSTRIAL MINIMALIST ============== -->
  <div v-if="!auth.isAuthenticated" class="min-h-screen flex flex-col" style="background-color: #131315; color: #ffffff; font-family: 'Space Grotesk', sans-serif;">
    <!-- Header -->
    <header class="w-full border-b border-white/10 px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-8">
        <div class="font-bold text-xl tracking-tighter">HDBARBER</div>
        <nav class="hidden md:block">
          <ul class="flex space-x-6 text-xs uppercase tracking-widest font-medium">
            <li><RouterLink to="/" class="bg-white/10 px-3 py-1 rounded-sm hover:bg-white/20 transition-colors">Inicio</RouterLink></li>
          </ul>
        </nav>
      </div>
      <div class="flex gap-4">
        <RouterLink to="/login" class="text-xs uppercase tracking-widest font-semibold px-4 py-2 hover:text-gray-400 transition-colors">Entrar</RouterLink>
        <RouterLink to="/register" class="text-xs uppercase tracking-widest font-semibold px-4 py-2 border border-white hover:bg-white hover:text-black transition-all">Crear Cuenta</RouterLink>
      </div>
    </header>

    <!-- Hero Section -->
    <main class="relative flex flex-col items-center justify-center px-6 text-center py-20 flex-1">
      <!-- Hero Label -->
      <span class="text-[10px] md:text-xs uppercase tracking-[0.3em] text-white/50 mb-6 font-medium">
        Precision & Tradition
      </span>
      <!-- Main Headline -->
      <h1 class="text-5xl md:text-7xl font-bold tracking-tight mb-6 max-w-4xl" style="text-wrap: balance;">
        HDBarber.<br/>
        Tu estilo, dominado.
      </h1>
      <!-- Subtext -->
      <p class="text-sm md:text-lg text-white/60 max-w-2xl mb-12 leading-relaxed">
        Reserva tu próximo corte. Pago online seguro, recordatorios automáticos y la precisión de los mejores barberos.
      </p>
      <!-- Call to Actions -->
      <div class="flex flex-col sm:flex-row gap-4 items-center justify-center w-full">
        <!-- Primary Action -->
        <RouterLink to="/register" class="group relative inline-flex items-center justify-center px-8 py-4 text-sm font-bold uppercase tracking-widest bg-white text-black hover:bg-gray-200 transition-all w-full sm:w-auto">
          Reservar Cita
          <svg class="h-4 w-4 ml-2 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
          </svg>
        </RouterLink>
        <!-- Secondary Action -->
        <RouterLink to="/login" class="inline-flex items-center justify-center px-8 py-4 text-sm font-bold uppercase tracking-widest border border-white/20 text-white hover:bg-white/5 transition-all w-full sm:w-auto">
          Ya tengo cuenta
        </RouterLink>
      </div>
      <!-- Decorative Industrial Element -->
      <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-px h-12 bg-gradient-to-b from-white/0 to-white/20 mt-12"></div>
    </main>

    <!-- Services Section (Preserved) -->
    <section class="px-6 py-16 border-t border-white/5">
      <h2 class="text-center text-xs uppercase tracking-[0.2em] text-white/40 mb-10 font-medium">Nuestros Servicios</h2>
      <div v-if="services.loading" class="text-center text-white/40 text-sm">Cargando…</div>
      <div v-else-if="services.error" class="max-w-md mx-auto p-4 border border-red-500/30 bg-red-500/10 rounded">
        <p class="text-red-400 text-sm">{{ services.error }}</p>
      </div>
      <div v-else-if="services.items.length === 0" class="text-center text-white/40 text-sm">No hay servicios disponibles.</div>
      <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 max-w-6xl mx-auto">
        <ServiceCard v-for="s in services.items" :key="s.id" :service="s" @select="selectService(s.id)" />
      </div>
    </section>

    <!-- Footer -->
    <footer class="w-full py-4 border-t border-white/5 text-center" style="background-color: #0e0e10;">
      <p class="text-[9px] uppercase tracking-[0.2em] text-white/30">© 2024 HDBARBER · PRECISION & TRADITION</p>
    </footer>
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
