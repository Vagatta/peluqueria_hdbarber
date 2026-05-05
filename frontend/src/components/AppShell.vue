<script setup lang="ts">
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { computed, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { Scissors, Calendar, User, LogOut, Home, Menu, X, ShieldCheck } from 'lucide-vue-next'

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
    <!-- Top App Bar (hide on guest landing) -->
    <header v-if="!isGuestLanding" class="fixed top-0 inset-x-0 z-40 h-16 bg-[#121212] border-b border-surface-container-highest">
      <div class="h-full max-w-[1280px] mx-auto px-4 md:px-margin flex items-center justify-between gap-4">
        <button
          class="md:hidden p-2 -ml-2 rounded text-on-surface hover:bg-surface-container-low transition-colors"
          @click="drawerOpen = !drawerOpen"
          aria-label="Menú"
        >
          <Menu v-if="!drawerOpen" class="w-5 h-5" />
          <X v-else class="w-5 h-5" />
        </button>

        <RouterLink to="/" class="flex items-center gap-2 mx-auto md:mx-0 font-display font-bold uppercase tracking-[0.18em] text-on-surface">
          HDBARBER
        </RouterLink>

        <nav class="hidden md:flex items-center gap-1 ml-8 flex-1">
          <RouterLink
            v-for="i in navItems" :key="i.to" :to="i.to"
            class="px-3 py-2 rounded text-label uppercase tracking-[0.05em] text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low transition-colors"
            active-class="!text-on-surface !bg-surface-container"
          >{{ i.label }}</RouterLink>
        </nav>

        <div class="flex items-center gap-2">
          <template v-if="auth.isAuthenticated">
            <button class="hidden md:inline-flex btn-ghost !py-2 !px-3" @click="doLogout">
              <LogOut class="w-4 h-4" /> Salir
            </button>
            <div class="w-9 h-9 rounded-full bg-surface-container-high border border-outline-variant grid place-items-center text-label uppercase text-on-surface">
              {{ auth.user?.name?.charAt(0) || 'U' }}
            </div>
          </template>
          <template v-else>
            <RouterLink to="/login" class="btn-secondary !py-2 !px-3 hidden sm:inline-flex">Entrar</RouterLink>
            <RouterLink to="/register" class="btn-primary !py-2 !px-3">Crear cuenta</RouterLink>
          </template>
        </div>
      </div>
    </header>

    <!-- Mobile drawer -->
    <Transition
      enter-from-class="opacity-0" enter-active-class="transition-opacity duration-150"
      leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150"
    >
      <div v-if="drawerOpen" class="fixed inset-0 z-30 md:hidden bg-black/60 backdrop-blur-sm" @click="drawerOpen = false">
        <aside class="absolute top-16 inset-x-0 bg-[#121212] border-b border-surface-container-highest p-4 space-y-1" @click.stop>
          <RouterLink
            v-for="i in navItems" :key="i.to" :to="i.to"
            class="flex items-center gap-3 px-3 py-3 rounded text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low"
            active-class="!text-on-surface !bg-surface-container"
            @click="drawerOpen = false"
          >
            <component :is="i.icon" class="w-4 h-4" />
            <span class="text-label uppercase tracking-[0.05em]">{{ i.label }}</span>
          </RouterLink>
          <button v-if="auth.isAuthenticated" class="w-full mt-2 btn-ghost" @click="doLogout">
            <LogOut class="w-4 h-4" /> Salir
          </button>
        </aside>
      </div>
    </Transition>

    <!-- Main canvas -->
    <main class="flex-1" :class="isGuestLanding ? '' : 'pt-16 pb-24 md:pb-margin'">
      <slot />
    </main>

    <!-- Mobile bottom nav (hide in admin) -->
    <nav v-if="!isAdminArea && auth.isAuthenticated" class="md:hidden fixed bottom-0 inset-x-0 z-40 h-16 bg-[#121212] border-t border-surface-container-highest">
      <div class="h-full grid grid-cols-4">
        <RouterLink
          v-for="i in navItems.slice(0, 4)" :key="i.to" :to="i.to"
          class="flex flex-col items-center justify-center gap-1 text-on-surface-variant hover:text-on-surface border-t-2 border-transparent active:scale-95 transition-all"
          active-class="!text-on-surface !border-primary"
        >
          <component :is="i.icon" class="w-5 h-5" />
          <span class="font-display text-[10px] uppercase tracking-tighter">{{ i.label }}</span>
        </RouterLink>
      </div>
    </nav>

    <!-- Footer (desktop only on guest pages, hide on guest landing) -->
    <footer v-if="!auth.isAuthenticated && !isGuestLanding" class="hidden md:block border-t border-surface-container-highest mt-margin">
      <div class="page py-6 text-center t-micro">
        © {{ new Date().getFullYear() }} HDBARBER · PRECISION & TRADITION
      </div>
    </footer>
  </div>
</template>
