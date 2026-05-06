<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useServicesStore } from '@/stores/services'
import { useAuthStore } from '@/stores/auth'
import { useAppointmentsStore } from '@/stores/appointments'
import WordsPullUp from '@/components/landing/WordsPullUp.vue'
import LuxuryButton from '@/components/landing/LuxuryButton.vue'
import GlassPanel from '@/components/landing/GlassPanel.vue'
import LuxServiceCard from '@/components/landing/LuxServiceCard.vue'

import { useReveal } from '@/composables/useReveal'
import {
  Scissors, Calendar, CalendarCheck, Award, ArrowRight, Clock,
  Instagram, MapPin, Phone, CreditCard, CheckCircle2, UserCircle2, Menu, X,
} from 'lucide-vue-next'

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

// ===== Luxury landing =====
useReveal('#lux-landing')
const mobileNav = ref(false)

// Fallback catalog (used only if backend is empty)
const fallbackServices = [
  { name: 'Afeitado Tradicional', description: 'Navaja, toallas calientes y aceites esenciales.', duration_minutes: 30, price_cents: 1800 },
  { name: 'Corte + Barba',        description: 'Combinación impecable de corte y diseño de barba.', duration_minutes: 45, price_cents: 2500 },
  { name: 'Corte Clásico',        description: 'Precisión y acabado editorial.',                    duration_minutes: 30, price_cents: 1500 },
  { name: 'Corte Infantil',       description: 'Cuidado atento para los más pequeños.',             duration_minutes: 25, price_cents: 1200 },
  { name: 'Perfilado Premium',    description: 'Diseño minucioso de barba y contornos.',            duration_minutes: 40, price_cents: 2000 },
  { name: 'Tinte y Estilo',       description: 'Color duradero y acabado profesional.',             duration_minutes: 60, price_cents: 3500 },
]

function formatDuration(m: number) { return `${m} MIN` }
function formatPrice(c: number) {
  return (c / 100).toLocaleString('es-ES', { style: 'currency', currency: 'EUR', minimumFractionDigits: 0 })
}

const luxServices = computed(() => {
  const src = services.items.length ? services.items : fallbackServices.map((s, i) => ({ ...s, id: -(i + 1) } as any))
  return src.map((s: any) => ({
    id: s.id as number,
    title: s.name as string,
    description: (s.description || '') as string,
    duration: formatDuration(s.duration_minutes),
    price: formatPrice(s.price_cents),
  }))
})

function onSelectLux(id: number) {
  if (id > 0) selectService(id)
  else router.push(auth.isAuthenticated ? '/book' : '/login')
}

// Reliable editorial gallery (uniform aspect; real URLs verified)
const galleryImages = [
  'https://images.unsplash.com/photo-1521590832167-7bcbfaa6381f?w=900&q=80',  // barber tools
  'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?w=900&q=80',  // cutting hair
  'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=900&q=80',  // portrait with beard
  'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?w=900&q=80',  // close-up shave
  'https://images.unsplash.com/photo-1580618672591-eb180b1a973f?w=900&q=80',  // trimming beard
  'https://images.unsplash.com/photo-1585747860715-2ba37e788b70?w=900&q=80',  // barber interior
  'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?w=900&q=80',  // vintage barber
  'https://images.unsplash.com/photo-1605497788044-5a32c7078486?w=900&q=80',  // brush
]

// ===== Parallax (hero subtext + experience cluster) =====
const parallax = ref({ x: 0, y: 0 })
function onParallaxMove(e: MouseEvent) {
  const x = (e.clientX / window.innerWidth - 0.5)
  const y = (e.clientY / window.innerHeight - 0.5)
  parallax.value = { x, y }
}
onMounted(() => {
  window.addEventListener('mousemove', onParallaxMove, { passive: true })
})
</script>

<template>
  <!-- ============== GUEST LANDING · HDBARBER · LUXURY ============== -->
  <div v-if="!auth.isAuthenticated" id="lux-landing" class="bg-ink text-primaryText font-tight min-h-screen relative overflow-x-hidden">

    <!-- ========== NAVBAR ========== -->
    <header class="fixed top-0 inset-x-0 z-50 h-[72px] bg-ink/60 backdrop-blur-md border-b border-borderHair">
      <div class="mx-auto max-w-[1440px] h-full px-6 md:px-10 flex items-center justify-between">
        <RouterLink to="/" class="font-display text-2xl tracking-[0.24em] text-primaryText">HDBARBER</RouterLink>

        <nav class="hidden md:flex items-center gap-10 text-[11px] uppercase tracking-[0.24em] text-textSecondary">
          <a href="#hero"        class="hover:text-primaryText transition-opacity duration-300">Inicio</a>
          <a href="#servicios"   class="hover:text-primaryText transition-opacity duration-300">Servicios</a>
          <a href="#reservas"    class="hover:text-primaryText transition-opacity duration-300">Reservas</a>
          <a href="#estudio"     class="hover:text-primaryText transition-opacity duration-300">Estudio</a>
          <a href="#contacto"    class="hover:text-primaryText transition-opacity duration-300">Contacto</a>
        </nav>

        <div class="hidden md:flex items-center gap-6">
          <RouterLink to="/login" class="text-[11px] uppercase tracking-[0.24em] text-textSecondary hover:text-primaryText transition">Entrar</RouterLink>
          <RouterLink
            to="/login"
            class="shine inline-flex items-center gap-2 h-10 px-5 bg-primaryText text-black text-[11px] uppercase tracking-[0.18em] hover:bg-white transition"
          >Reservar cita <ArrowRight class="w-3.5 h-3.5" /></RouterLink>
        </div>

        <button class="md:hidden p-2 -mr-2 text-primaryText" @click="mobileNav = !mobileNav" :aria-label="mobileNav ? 'Cerrar' : 'Menú'">
          <X v-if="mobileNav" class="w-5 h-5" />
          <Menu v-else class="w-5 h-5" />
        </button>
      </div>

      <Transition
        enter-from-class="opacity-0" enter-active-class="transition-opacity"
        leave-to-class="opacity-0" leave-active-class="transition-opacity"
      >
        <div v-if="mobileNav" class="md:hidden absolute inset-x-0 top-[72px] bg-ink/95 backdrop-blur-xl border-b border-borderHair">
          <div class="px-6 py-6 flex flex-col gap-4 text-[12px] uppercase tracking-[0.24em]">
            <a href="#hero"      @click="mobileNav=false" class="py-2 text-textSecondary hover:text-primaryText">Inicio</a>
            <a href="#servicios" @click="mobileNav=false" class="py-2 text-textSecondary hover:text-primaryText">Servicios</a>
            <a href="#reservas"  @click="mobileNav=false" class="py-2 text-textSecondary hover:text-primaryText">Reservas</a>
            <a href="#estudio"   @click="mobileNav=false" class="py-2 text-textSecondary hover:text-primaryText">Estudio</a>
            <a href="#contacto"  @click="mobileNav=false" class="py-2 text-textSecondary hover:text-primaryText">Contacto</a>
            <div class="pt-4 mt-2 border-t border-borderHair flex gap-3">
              <RouterLink to="/login" class="flex-1 py-3 text-center border border-borderSoft text-primaryText">Entrar</RouterLink>
              <RouterLink to="/login" class="flex-1 py-3 text-center bg-primaryText text-black">Reservar</RouterLink>
            </div>
          </div>
        </div>
      </Transition>
    </header>

    <!-- ========== SECTION 1 · HERO ========== -->
    <section id="hero" class="relative h-screen flex flex-col justify-between overflow-hidden pt-[72px]">
      <!-- grid overlay -->
      <div class="absolute inset-0 grid-overlay pointer-events-none" />
      <!-- noise texture -->
      <div class="absolute inset-0 noise-overlay pointer-events-none" />
      <!-- vignette corners -->
      <div class="absolute inset-0 pointer-events-none" style="background: radial-gradient(ellipse 80% 70% at 50% 50%, transparent 40%, rgba(5,5,5,0.55) 100%)" />

      <!-- Top eyebrow row -->
      <div class="relative w-full px-6 md:px-10 lg:px-14 pt-10 flex items-center justify-between">
        <p class="text-[10px] uppercase tracking-[0.45em] text-textMuted">
          Madrid &nbsp;·&nbsp; Est. 2018
        </p>
        <p class="text-[10px] uppercase tracking-[0.45em] text-textMuted hidden sm:block">
          Precision &nbsp;·&nbsp; Tradition
        </p>
      </div>

      <!-- Massive headline — vertically centred -->
      <div class="relative w-full px-6 md:px-10 lg:px-14 flex-1 flex flex-col justify-center">
        <div class="overflow-hidden">
          <h1 class="hero-headline leading-[0.88] text-primaryText uppercase">
            HDBARBER.
          </h1>
        </div>
        <div class="overflow-hidden">
          <h1 class="hero-headline leading-[0.88] text-primaryText uppercase">
            Navaja, técnica
          </h1>
        </div>
        <div class="overflow-hidden">
          <h1 class="hero-headline leading-[0.88] text-primaryText/30 uppercase">
            y carácter.
          </h1>
        </div>
      </div>

      <!-- Bottom row: copy left · CTAs right -->
      <div class="relative w-full px-6 md:px-10 lg:px-14 pb-10 md:pb-14 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
        <p class="max-w-[26rem] text-sm text-textSecondary leading-relaxed">
          Cada visita es un ritual. Cortes de precisión, afeitados
          con navaja y una atención que no se improvisa.
        </p>
        <div class="flex items-center gap-3 shrink-0">
          <RouterLink
            to="/login"
            class="inline-flex items-center gap-2 h-12 px-7 bg-primaryText text-black text-[11px] uppercase tracking-[0.18em] font-medium hover:bg-white transition-colors duration-200"
          >
            Reservar cita <ArrowRight class="w-3.5 h-3.5" />
          </RouterLink>
          <RouterLink
            to="/login"
            class="inline-flex items-center gap-2 h-12 px-7 border border-white/25 text-primaryText text-[11px] uppercase tracking-[0.18em] hover:border-white/50 hover:bg-white/5 transition-colors duration-200"
          >
            Ya tengo cuenta
          </RouterLink>
        </div>
      </div>

      <!-- scroll hint right side -->
      <div class="absolute bottom-10 right-10 flex flex-col items-center gap-2 text-textMuted">
        <span class="block w-px h-12 bg-gradient-to-b from-transparent via-white/25 to-transparent" />
        <span class="text-[9px] uppercase tracking-[0.35em]" style="writing-mode:vertical-rl">Scroll</span>
      </div>
    </section>

    <!-- ========== SECTION 2 · SERVICES ========== -->
    <section id="servicios" class="relative py-32 md:py-44">
      <div class="absolute inset-0 noise-overlay opacity-[0.04]" />
      <div class="relative mx-auto max-w-[1440px] px-6 md:px-10">
        <div class="text-center max-w-3xl mx-auto mb-20">
          <p class="reveal text-[10px] uppercase tracking-[0.3em] text-textMuted mb-6">Nuestros Servicios</p>
          <h2 class="reveal font-display text-5xl md:text-7xl leading-[0.95] tracking-[-0.02em] text-primaryText">
            Cortes precisos.<br/>Resultados impecables.
          </h2>
        </div>

        <div v-if="services.loading && !services.items.length" class="text-center text-textMuted">Cargando servicios…</div>
        <div v-else-if="services.error" class="max-w-md mx-auto p-5 border border-red-500/30 bg-red-500/10 rounded-2xl text-red-300 text-sm text-center">
          {{ services.error }}
        </div>
        <div v-else class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch">
          <div v-for="(s, i) in luxServices" :key="s.id" class="reveal flex" :style="{ transitionDelay: (i * 60) + 'ms' }">
            <LuxServiceCard
              :index="i + 1"
              :title="s.title"
              :duration="s.duration"
              :price="s.price"
              :description="s.description"
              @select="onSelectLux(s.id)"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 3 · EXPERIENCE ========== -->
    <section id="estudio" class="relative py-32 md:py-44 overflow-hidden">
      <div class="absolute inset-0 metal-gradient opacity-40" />
      <div class="absolute inset-0 grid-overlay" />
      <div class="absolute inset-0 noise-overlay" />

      <div class="relative mx-auto max-w-[1440px] px-6 md:px-10 grid lg:grid-cols-2 gap-20 items-center">
        <div>
          <p class="reveal text-[10px] uppercase tracking-[0.3em] text-textMuted mb-6">La experiencia</p>
          <h2 class="reveal font-display text-6xl md:text-8xl leading-[0.9] tracking-[-0.03em] text-primaryText">
            Más que una<br/>barbería.<br/><span class="text-primaryText/70">Un estándar.</span>
          </h2>
          <p class="reveal mt-10 max-w-lg text-textSecondary text-base md:text-lg leading-relaxed">
            HDBARBER combina tradición clásica, herramientas de precisión y tecnología moderna
            para ofrecer una experiencia impecable en cada cita.
          </p>
          <div class="reveal mt-10">
            <LuxuryButton to="/login" variant="secondary">
              Descubrir el estudio <ArrowRight class="w-4 h-4" />
            </LuxuryButton>
          </div>
        </div>

        <!-- Floating glass cards cluster -->
        <div id="reservas" class="relative h-[560px]" style="perspective: 1200px;">
          <!-- booking card -->
          <GlassPanel
            class="absolute top-0 left-0 w-[78%] p-6 transition-transform duration-300 ease-out"
            :style="{ transform: `translate3d(${parallax.x * -24}px, ${parallax.y * -16}px, 0) rotateY(${parallax.x * 3}deg) rotateX(${parallax.y * -2}deg)` }"
          >
            <div class="flex items-center gap-3 mb-5">
              <Calendar class="w-4 h-4 text-primaryText" />
              <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Reserva online</span>
            </div>
            <div class="font-display text-3xl tracking-wider text-primaryText">Vie · 14:30</div>
            <div class="text-sm text-textSecondary mt-2">Corte + Barba · con Marco</div>
            <div class="mt-6 pt-5 border-t border-borderHair flex items-center justify-between">
              <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Confirmada</span>
              <CheckCircle2 class="w-4 h-4 text-primaryText" />
            </div>
          </GlassPanel>

          <!-- barber profile card -->
          <GlassPanel
            class="absolute top-36 right-0 w-[62%] p-5 transition-transform duration-300 ease-out"
            :style="{ transform: `translate3d(${parallax.x * 28}px, ${parallax.y * 18}px, 0) rotateY(${parallax.x * -3}deg) rotateX(${parallax.y * 2}deg)` }"
          >
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full metal-gradient border border-borderSoft grid place-items-center">
                <UserCircle2 class="w-6 h-6 text-primaryText/70" stroke-width="1" />
              </div>
              <div>
                <div class="font-display text-xl tracking-wider text-primaryText">Marco Vidal</div>
                <div class="text-[11px] uppercase tracking-[0.2em] text-textMuted">Master Barber · 12 años</div>
              </div>
            </div>
          </GlassPanel>

          <!-- appointment status card -->
          <GlassPanel
            class="absolute bottom-24 left-6 w-[58%] p-5 transition-transform duration-300 ease-out"
            :style="{ transform: `translate3d(${parallax.x * -18}px, ${parallax.y * 22}px, 0) rotateY(${parallax.x * 2}deg)` }"
          >
            <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Estado</span>
            <div class="mt-2 font-display text-2xl tracking-wider text-primaryText">En curso</div>
            <div class="mt-4 flex gap-1">
              <span class="h-1 flex-1 bg-primaryText rounded-full" />
              <span class="h-1 flex-1 bg-primaryText rounded-full" />
              <span class="h-1 flex-1 bg-primaryText/70 rounded-full" />
              <span class="h-1 flex-1 bg-white/15 rounded-full" />
            </div>
          </GlassPanel>

          <!-- payment confirmation -->
          <GlassPanel
            class="absolute bottom-0 right-4 w-[48%] p-5 transition-transform duration-300 ease-out"
            :style="{ transform: `translate3d(${parallax.x * 22}px, ${parallax.y * -18}px, 0) rotateY(${parallax.x * -2}deg)` }"
          >
            <div class="flex items-center gap-3">
              <CreditCard class="w-4 h-4 text-primaryText" />
              <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Pago</span>
            </div>
            <div class="mt-2 font-display text-2xl tracking-wider text-primaryText">25,00 €</div>
            <div class="mt-1 text-[11px] text-textSecondary">**** 4421 · Aprobado</div>
          </GlassPanel>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 4 · GALLERY ========== -->
    <section class="relative py-32 md:py-40 bg-ink">
      <div class="absolute inset-0 noise-overlay" />
      <div class="relative mx-auto max-w-[1440px] px-6 md:px-10">
        <div class="flex items-end justify-between flex-wrap gap-6 mb-16">
          <div>
            <p class="reveal text-[10px] uppercase tracking-[0.3em] text-textMuted mb-4">El oficio</p>
            <h2 class="reveal font-display text-5xl md:text-7xl leading-[0.95] tracking-[-0.02em] text-primaryText">
              Editorial.<br/>Preciso.
            </h2>
          </div>
          <p class="reveal max-w-sm text-textSecondary">
            Fragmentos del trabajo diario. Luz, acero, textura y detalle.
          </p>
        </div>

        <!-- True CSS columns masonry; each item floats naturally, no broken slots -->
        <div class="columns-2 md:columns-3 lg:columns-4 gap-3 md:gap-4 [column-fill:_balance]">
          <div
            v-for="(src, i) in galleryImages"
            :key="i"
            class="reveal relative overflow-hidden rounded-xl bg-surfaceDark border border-borderHair group mb-3 md:mb-4 break-inside-avoid"
            :class="[ i % 3 === 0 ? 'aspect-[3/4]' : i % 3 === 1 ? 'aspect-[4/5]' : 'aspect-square' ]"
            :style="{ transitionDelay: (i * 60) + 'ms' }"
          >
            <img
              :src="src"
              alt=""
              loading="lazy"
              referrerpolicy="no-referrer"
              class="absolute inset-0 w-full h-full object-cover grayscale contrast-[0.92] group-hover:grayscale-0 group-hover:contrast-100 group-hover:scale-[1.06] transition-all duration-[1200ms] ease-out"
              @error="(e: any) => { const p = e.target?.parentElement; if (p) p.style.display = 'none' }"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none" />
          </div>
        </div>
      </div>
    </section>

    <!-- ========== SECTION 5 · FOOTER ========== -->
    <footer id="contacto" class="relative bg-ink border-t border-borderHair">
      <div class="absolute inset-0 noise-overlay" />
      <div class="relative mx-auto max-w-[1440px] px-6 md:px-10 py-20">
        <div class="grid md:grid-cols-4 gap-12 mb-16">
          <div class="md:col-span-2">
            <div class="font-display text-4xl tracking-[0.22em] text-primaryText">HDBARBER</div>
            <p class="mt-6 max-w-sm text-textSecondary leading-relaxed">
              Precisión, disciplina y tradición. Un estándar moderno para hombres exigentes.
            </p>
            <div class="mt-8">
              <LuxuryButton to="/login" variant="primary">
                Reservar cita <ArrowRight class="w-4 h-4" />
              </LuxuryButton>
            </div>
          </div>

          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-textMuted mb-5">Estudio</p>
            <ul class="space-y-3 text-textSecondary text-sm">
              <li class="flex items-start gap-3"><MapPin class="w-4 h-4 mt-0.5" /> C/ Calvario 8, Tudela de Duero</li>
              <li class="flex items-start gap-3"><Phone class="w-4 h-4 mt-0.5" /> +34 600 000 000</li>
              <li class="flex items-start gap-3"><Instagram class="w-4 h-4 mt-0.5" /> @hdbarber</li>
            </ul>
          </div>

          <div>
            <p class="text-[10px] uppercase tracking-[0.3em] text-textMuted mb-5">Horario</p>
            <ul class="space-y-2 text-textSecondary text-sm">
              <li class="flex justify-between"><span>Lun – Vie</span><span>10:00 – 21:00</span></li>
              <li class="flex justify-between"><span>Sábado</span><span>09:00 – 20:00</span></li>
              <li class="flex justify-between"><span>Domingo</span><span class="text-textMuted">Cerrado</span></li>
            </ul>
          </div>
        </div>

        <div class="pt-8 border-t border-borderHair flex flex-col md:flex-row items-center justify-between gap-4 text-[10px] uppercase tracking-[0.3em] text-textMuted">
          <span>© {{ new Date().getFullYear() }} HDBARBER</span>
          <div class="flex gap-8">
            <a href="#" class="hover:text-primaryText transition">Privacidad</a>
            <a href="#" class="hover:text-primaryText transition">Términos</a>
            <a href="#" class="hover:text-primaryText transition">Cookies</a>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <!-- ============== AUTH DASHBOARD · LUXURY ============== -->
  <div v-else class="relative bg-ink min-h-screen">
    <div class="absolute inset-0 noise-overlay opacity-[0.04]" />
    <div class="relative max-w-[1440px] mx-auto px-4 md:px-10 py-12 md:py-16">

      <!-- Header -->
      <div class="mb-12 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
        <div>
          <span class="text-[10px] uppercase tracking-[0.3em] text-textMuted">Panel</span>
          <h1 class="mt-3 font-display text-6xl md:text-7xl leading-[0.95] tracking-[-0.02em] text-primaryText">
            Hola, {{ auth.user?.name?.split(' ')[0] }}.
          </h1>
        </div>
        <LuxuryButton to="/book" variant="primary">
          <Calendar class="w-4 h-4" /> Reservar ahora
        </LuxuryButton>
      </div>

      <!-- Bento grid -->
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6">
        <!-- Próxima cita -->
        <div class="md:col-span-8 relative overflow-hidden rounded-3xl bg-surfaceDark border border-borderHair p-8 md:p-10">
          <div class="absolute inset-0 opacity-50 pointer-events-none"
               style="background: radial-gradient(420px circle at top right, rgba(255,255,255,0.05), transparent 60%);" />
          <template v-if="upcoming">
            <div class="relative flex justify-between items-start mb-10">
              <div>
                <div class="flex items-center gap-3 mb-4">
                  <CalendarCheck class="w-4 h-4 text-primaryText" />
                  <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Próxima cita</span>
                </div>
                <div class="font-display text-5xl md:text-6xl leading-[0.9] tracking-[-0.02em] text-primaryText">
                  {{ fmtDate(upcoming.start_at) }}<br/>{{ fmtTime(upcoming.start_at) }}
                </div>
              </div>
              <span class="inline-flex items-center px-3 py-1 text-[10px] uppercase tracking-[0.2em] border border-borderSoft text-primaryText">
                {{ ({ pending: 'Pendiente', confirmed: 'Confirmada', completed: 'Completada', cancelled: 'Cancelada', no_show: 'No asistió' } as any)[upcoming.status] || upcoming.status }}
              </span>
            </div>
            <div class="relative pt-6 border-t border-borderHair flex items-end justify-between gap-3">
              <div>
                <p class="text-lg text-primaryText">{{ upcoming.service?.name }}</p>
                <p class="mt-1 text-sm text-textSecondary flex items-center gap-2">
                  <Scissors class="w-4 h-4" /> con {{ upcoming.employee?.name || 'cualquier estilista' }}
                </p>
              </div>
              <LuxuryButton to="/appointments" variant="secondary">Gestionar</LuxuryButton>
            </div>
          </template>
          <template v-else>
            <div class="relative text-center py-10">
              <Calendar class="w-10 h-10 mx-auto text-textMuted mb-4" stroke-width="1" />
              <h3 class="font-display text-4xl tracking-wider text-primaryText mb-2">Sin citas próximas</h3>
              <p class="text-textSecondary mb-8">Reserva tu próxima visita en pocos clics.</p>
              <LuxuryButton to="/book" variant="primary">
                Reservar ahora <ArrowRight class="w-4 h-4" />
              </LuxuryButton>
            </div>
          </template>
        </div>

        <!-- Fidelización -->
        <div class="md:col-span-4 rounded-3xl bg-surfaceDark border border-borderHair p-8 flex flex-col justify-between">
          <div>
            <div class="flex items-center gap-3 mb-5">
              <Award class="w-4 h-4 text-primaryText" />
              <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Estado de fidelización</span>
            </div>
            <div class="flex items-end gap-2 mb-6">
              <span class="font-display text-6xl leading-none tracking-wider text-primaryText">{{ loyalty.points }}</span>
              <span class="text-textMuted pb-1 text-sm">/ {{ loyalty.goal }} pts</span>
            </div>
          </div>
          <div>
            <div class="w-full h-[3px] bg-white/5 overflow-hidden mb-3">
              <div class="h-full bg-primaryText transition-all duration-700" :style="{ width: (loyalty.points / loyalty.goal * 100) + '%' }"></div>
            </div>
            <p class="text-xs text-textSecondary leading-relaxed">
              {{ loyalty.goal - loyalty.points }} puntos para tu próximo servicio de cortesía.
            </p>
          </div>
        </div>

        <!-- Visitas recientes -->
        <div class="md:col-span-12 mt-4">
          <div class="flex items-center justify-between mb-6 pb-4 border-b border-borderHair">
            <h2 class="font-display text-3xl tracking-wider text-primaryText">Visitas recientes</h2>
            <span class="text-[10px] uppercase tracking-[0.24em] text-textMuted">Histórico</span>
          </div>
          <div v-if="!recent.length" class="rounded-3xl bg-surfaceDark border border-borderHair p-8 text-center text-textSecondary">
            Cuando completes tu primera cita aparecerá aquí.
          </div>
          <ul v-else class="flex flex-col">
            <li
              v-for="a in recent" :key="a.id"
              class="flex items-center justify-between py-5 border-b border-borderHair hover:bg-white/[0.02] transition-colors px-2 -mx-2"
            >
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-ink border border-borderSoft grid place-items-center text-textSecondary shrink-0 rounded-lg">
                  <Scissors class="w-5 h-5" />
                </div>
                <div>
                  <p class="text-[11px] uppercase tracking-[0.18em] text-primaryText">{{ a.service?.name }}</p>
                  <p class="mt-1 text-sm text-textSecondary flex items-center gap-2">
                    <Clock class="w-3.5 h-3.5" /> {{ fmtDate(a.start_at) }} · {{ a.employee?.name || '—' }}
                  </p>
                </div>
              </div>
              <div class="text-right">
                <p class="font-display text-2xl tracking-wider text-primaryText">{{ eur(a.service?.price_cents || 0) }}</p>
                <RouterLink to="/book" class="text-[10px] uppercase tracking-[0.24em] text-textSecondary hover:text-primaryText transition mt-1 inline-block">
                  Volver a reservar
                </RouterLink>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>
