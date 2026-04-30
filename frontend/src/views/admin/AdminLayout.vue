<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { LayoutDashboard, CalendarDays, Scissors, Users, UserCog, CreditCard, Clock } from 'lucide-vue-next'

const items = [
  { to: '/admin', label: 'Dashboard', icon: LayoutDashboard, exact: true },
  { to: '/admin/calendar', label: 'Agenda', icon: CalendarDays },
  { to: '/admin/services', label: 'Servicios', icon: Scissors },
  { to: '/admin/employees', label: 'Empleados & Horarios', icon: UserCog },
  { to: '/admin/clients', label: 'Clientes', icon: Users },
  { to: '/admin/payments', label: 'Pagos', icon: CreditCard }
]
</script>

<template>
  <div class="page py-margin grid md:grid-cols-[15rem_1fr] gap-gutter">
    <aside class="md:sticky md:top-[calc(64px+32px)] md:self-start">
      <div class="t-label-muted mb-3 px-3">Administración</div>
      <nav class="flex md:flex-col gap-1 overflow-x-auto scrollbar-none -mx-1 px-1">
        <RouterLink
          v-for="i in items" :key="i.to"
          :to="i.to"
          :exact-active-class="i.exact ? 'bg-surface-container text-on-surface border-outline-variant' : ''"
          :active-class="i.exact ? '' : 'bg-surface-container text-on-surface border-outline-variant'"
          class="flex items-center gap-3 px-3 py-2.5 rounded text-on-surface-variant hover:text-on-surface hover:bg-surface-container-low whitespace-nowrap border border-transparent transition-colors"
        >
          <component :is="i.icon" class="w-4 h-4" />
          <span class="text-label uppercase tracking-[0.05em]">{{ i.label }}</span>
        </RouterLink>
      </nav>
    </aside>
    <section><RouterView /></section>
  </div>
</template>
