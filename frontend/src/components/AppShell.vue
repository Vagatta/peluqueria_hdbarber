<script setup lang="ts">
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { Scissors, Calendar, User, LogOut, Home, Menu, X, ShieldCheck, ArrowRight } from 'lucide-vue-next'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const isAdminArea = computed(() => route.path.startsWith('/admin'))
const isGuestLanding = computed(() => route.path === '/' && !auth.isAuthenticated)
const drawerOpen = ref(false)

async function doLogout() {
  await auth.logout()
  drawerOpen.value = false
  router.push('/')
}

const navItems = computed(() => {
  const items = [{ to: '/', label: 'Inicio', icon: Home }]
  if (auth.isAuthenticated) {
    items.push({ to: '/book', label: 'Reservar', icon: Calendar })
    items.push({ to: '/appointments', label: 'Citas', icon: Scissors })
    items.push({ to: '/profile', label: 'Perfil', icon: User })
  }
  if (auth.isAdmin) items.push({ to: '/admin', label: 'Admin', icon: ShieldCheck })
  return items
})
</script>

<template>
  <div class="min-h-screen flex flex-col">

    <!-- ========== HEADER (hidden on guest landing — landing has its own) ========== -->
    <header
      v-if="!isGuestLanding"
      class="fixed top-0 inset-x-0 z-50 h-[72px] bg-ink/80 backdrop-blur-md border-b border-borderHair"
    >
      <div class="h-full max-w-[1440px] mx-auto px-6 md:px-10 flex items-center justify-between">

        <!-- Logo -->
        <RouterLink to="/" class="font-display text-2xl tracking-[0.24em] text-primaryText shrink-0">
          HDBARBER
        </RouterLink>

        <!-- Desktop nav -->
        <nav class="hidden md:flex items-center gap-8 text-[11px] uppercase tracking-[0.24em] text-textSecondary">
          <RouterLink
            v-for="i in navItems" :key="i.to" :to="i.to"
            class="hover:text-primaryText transition-colors duration-200"
            active-class="!text-primaryText"
          >{{ i.label }}</RouterLink>
        </nav>

        <!-- Desktop actions -->
        <div class="hidden md:flex items-center gap-6">
          <template v-if="auth.isAuthenticated">
            <div class="w-8 h-8 border border-borderSoft grid place-items-center text-[11px] uppercase tracking-[0.1em] text-primaryText">
              {{ auth.user?.name?.charAt(0) || 'U' }}
            </div>
            <button
              class="text-[11px] uppercase tracking-[0.24em] text-textSecondary hover:text-primaryText transition-colors duration-200 flex items-center gap-2"
              @click="doLogout"
            >
              <LogOut class="w-3.5 h-3.5" /> Salir
            </button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="text-[11px] uppercase tracking-[0.24em] text-textSecondary hover:text-primaryText transition-colors duration-200">
              Entrar
            </RouterLink>
            <RouterLink
              to="/register"
              class="inline-flex items-center gap-2 h-10 px-5 bg-primaryText text-black text-[11px] uppercase tracking-[0.18em] hover:bg-white transition-colors duration-200"
            >
              Reservar cita <ArrowRight class="w-3.5 h-3.5" />
            </RouterLink>
          </template>
        </div>

        <!-- Mobile menu toggle -->
        <button
          class="md:hidden p-2 -mr-2 text-primaryText"
          @click="drawerOpen = !drawerOpen"
          :aria-label="drawerOpen ? 'Cerrar' : 'Menú'"
        >
          <X v-if="drawerOpen" class="w-5 h-5" />
          <Menu v-else class="w-5 h-5" />
        </button>
      </div>
    </header>

    <!-- ========== MOBILE DRAWER ========== -->
    <Transition
      enter-from-class="opacity-0" enter-active-class="transition-opacity duration-150"
      leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150"
    >
      <div
        v-if="drawerOpen && !isGuestLanding"
        class="fixed inset-0 z-40 md:hidden"
        @click="drawerOpen = false"
      >
        <aside
          class="absolute top-[72px] inset-x-0 bg-ink/95 backdrop-blur-xl border-b border-borderHair px-6 py-6 flex flex-col gap-4"
          @click.stop
        >
          <RouterLink
            v-for="i in navItems" :key="i.to" :to="i.to"
            class="py-2 text-[12px] uppercase tracking-[0.24em] text-textSecondary hover:text-primaryText transition-colors"
            active-class="!text-primaryText"
            @click="drawerOpen = false"
          >{{ i.label }}</RouterLink>

          <div class="pt-4 mt-2 border-t border-borderHair flex gap-3">
            <template v-if="auth.isAuthenticated">
              <button class="flex-1 py-3 border border-borderSoft text-[11px] uppercase tracking-[0.18em] text-primaryText hover:bg-white/5 transition-colors" @click="doLogout">
                <LogOut class="w-4 h-4 inline mr-2" />Salir
              </button>
            </template>
            <template v-else>
              <RouterLink to="/login" class="flex-1 py-3 text-center border border-borderSoft text-[11px] uppercase tracking-[0.18em] text-primaryText" @click="drawerOpen = false">
                Entrar
              </RouterLink>
              <RouterLink to="/register" class="flex-1 py-3 text-center bg-primaryText text-black text-[11px] uppercase tracking-[0.18em]" @click="drawerOpen = false">
                Reservar
              </RouterLink>
            </template>
          </div>
        </aside>
      </div>
    </Transition>

    <!-- ========== MAIN ========== -->
    <main class="flex-1" :class="isGuestLanding ? '' : 'pt-[72px] pb-24 md:pb-10'">
      <slot />
    </main>

    <!-- ========== MOBILE BOTTOM NAV (auth only, not admin) ========== -->
    <nav
      v-if="!isAdminArea && auth.isAuthenticated"
      class="md:hidden fixed bottom-0 inset-x-0 z-40 h-16 bg-ink border-t border-borderHair"
    >
      <div class="h-full grid" :class="`grid-cols-${Math.min(navItems.length, 4)}`">
        <RouterLink
          v-for="i in navItems.slice(0, 4)" :key="i.to" :to="i.to"
          class="flex flex-col items-center justify-center gap-1 text-textSecondary hover:text-primaryText transition-colors active:scale-95"
          active-class="!text-primaryText"
        >
          <component :is="i.icon" class="w-5 h-5" stroke-width="1.5" />
          <span class="font-display text-[9px] uppercase tracking-[0.15em]">{{ i.label }}</span>
        </RouterLink>
      </div>
    </nav>

    <!-- ========== FOOTER (non-auth, non-landing) ========== -->
    <footer v-if="!auth.isAuthenticated && !isGuestLanding" class="hidden md:block border-t border-borderHair">
      <div class="max-w-[1440px] mx-auto px-6 md:px-10 py-6 flex items-center justify-between text-[10px] uppercase tracking-[0.3em] text-textMuted">
        <span>© {{ new Date().getFullYear() }} HDBARBER</span>
        <span>Precision &nbsp;·&nbsp; Tradition</span>
      </div>
    </footer>

  </div>
</template>
