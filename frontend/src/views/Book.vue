<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useServicesStore } from '@/stores/services'
import { useAppointmentsStore } from '@/stores/appointments'
import ServiceCard from '@/components/ServiceCard.vue'
import DatePicker from '@/components/DatePicker.vue'
import type { Service, Slot } from '@/types'
import { Loader2, Scissors, User, Calendar, CreditCard, ShieldCheck, CheckCircle2, Receipt, Check, ExternalLink } from 'lucide-vue-next'
import { generateGoogleCalendarLink } from '@/utils/calendar'

const services = useServicesStore()
const appts = useAppointmentsStore()
const router = useRouter()
const route = useRoute()

const selectedService = ref<Service | null>(null)
const date = ref<string>(new Date().toISOString().slice(0, 10))
const slots = ref<Slot[]>([])
const loadingSlots = ref(false)
const selectedSlot = ref<Slot | null>(null)
const notes = ref('')
const submitting = ref(false)
const error = ref<string | null>(null)
const showCalendarModal = ref(false)
const lastAppointment = ref<any>(null)

onMounted(async () => {
  await services.fetch()
  const sid = route.query.service
  if (sid) {
    const found = services.items.find(s => s.id === Number(sid))
    if (found) selectedService.value = found
  }
})

async function loadSlots() {
  if (!selectedService.value) return
  loadingSlots.value = true
  try {
    slots.value = await appts.availability(selectedService.value.id, date.value)
    selectedSlot.value = null
  } finally { loadingSlots.value = false }
}

watch([selectedService, date], loadSlots)

const groupedByHour = computed(() => {
  const m = new Map<string, Slot[]>()
  for (const s of slots.value) {
    const h = s.start.slice(11, 13) + ':00'
    if (!m.has(h)) m.set(h, [])
    m.get(h)!.push(s)
  }
  return Array.from(m.entries())
})

function fmtTime(iso: string) { return new Date(iso).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' }) }
function fmtFullDateTime(iso: string) {
  const d = new Date(iso)
  return d.toLocaleDateString('es-ES', { weekday: 'long', day: '2-digit', month: 'short' }) + ' · ' +
         d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })
}
function eur(c: number) { return (c / 100).toLocaleString('es-ES', { style: 'currency', currency: 'EUR' }) }
const employeeName = computed(() => {
  if (!selectedSlot.value) return ''
  if (!selectedSlot.value.employee_id) return 'Cualquier estilista'
  return 'Mtro. ID ' + selectedSlot.value.employee_id
})

async function book(payNow: boolean) {
  if (!selectedService.value || !selectedSlot.value) return
  submitting.value = true
  error.value = null
  try {
    const a = await appts.create({
      service_id: selectedService.value.id,
      employee_id: selectedSlot.value.employee_id,
      start_at: selectedSlot.value.start,
      notes: notes.value || undefined
    })
    if (payNow) {
      const r = await appts.checkout(a.id)
      window.location.href = r.url
    } else {
      // Guardar cita y mostrar modal de Google Calendar
      lastAppointment.value = a
      showCalendarModal.value = true
    }
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'No se pudo reservar'
  } finally { submitting.value = false }
}

const googleCalendarLink = computed(() => {
  if (!lastAppointment.value || !selectedService.value) return ''
  const start = lastAppointment.value.start_at
  const end = lastAppointment.value.end_at
  return generateGoogleCalendarLink({
    title: `Cita HDBarber: ${selectedService.value.name}`,
    start,
    end,
    description: `Servicio: ${selectedService.value.name}\nPrecio: ${eur(selectedService.value.price_cents)}\nNotas: ${notes.value || 'Ninguna'}`,
    location: 'HDBarber - Tu barbería de confianza'
  })
})

function closeCalendarModal() {
  showCalendarModal.value = false
  router.push('/appointments')
}
</script>

<template>
  <div class="page py-margin">
    <div class="mb-gutter">
      <span class="t-label-muted">Reservar</span>
      <h1 class="font-display text-display mt-1">Completar reserva</h1>
      <p class="t-muted mt-2 max-w-2xl">Verifica los detalles de tu servicio y selecciona horario.</p>
    </div>

    <!-- Stepper -->
    <ol class="flex items-center gap-2 mb-gutter overflow-x-auto scrollbar-none">
      <li v-for="(step, i) in [
        {n: 1, l: 'Servicio', done: !!selectedService},
        {n: 2, l: 'Fecha', done: !!selectedService && !!date},
        {n: 3, l: 'Hora', done: !!selectedSlot},
        {n: 4, l: 'Confirmar', done: false}
      ]" :key="i" class="flex items-center gap-2 shrink-0">
        <span
          class="w-7 h-7 rounded-full grid place-items-center text-label border"
          :class="step.done ? 'bg-primary text-on-primary border-primary' : 'border-outline-variant text-on-surface-variant'"
        >{{ step.n }}</span>
        <span class="t-label-muted">{{ step.l }}</span>
        <span v-if="i < 3" class="w-6 border-t border-surface-container-highest"></span>
      </li>
    </ol>

    <!-- Step 1: Service -->
    <section class="mb-margin">
      <h2 class="section-title">1. Elige servicio</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
        <ServiceCard v-for="s in services.items" :key="s.id" :service="s" :selected="selectedService?.id === s.id" @select="selectedService = $event" />
      </div>
    </section>

    <!-- Step 2 & 3: Date & Time -->
    <section v-if="selectedService" class="mb-margin grid md:grid-cols-2 gap-gutter items-stretch">
      <div class="flex flex-col">
        <h2 class="section-title">2. Elige fecha</h2>
        <DatePicker v-model="date" class="flex-1" />
      </div>
      <div class="flex flex-col">
        <h2 class="section-title">3. Elige hora</h2>
        <div class="card flex-1 min-h-[14rem]">
          <div v-if="loadingSlots" class="flex items-center gap-2 t-muted">
            <Loader2 class="w-4 h-4 animate-spin" /> Cargando huecos…
          </div>
          <div v-else-if="!slots.length" class="text-center py-8">
            <p class="t-muted">No hay huecos disponibles ese día.</p>
          </div>
          <div v-else class="space-y-5">
            <div v-for="[h, list] in groupedByHour" :key="h">
              <div class="t-micro mb-2">{{ h }}</div>
              <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                <button
                  v-for="s in list" :key="s.start"
                  @click="selectedSlot = s"
                  class="px-3 py-2.5 rounded text-sm border transition-colors"
                  :class="selectedSlot?.start === s.start
                    ? 'bg-primary text-on-primary border-primary font-semibold'
                    : 'bg-surface-container-low border-outline-variant text-on-surface hover:border-primary'"
                >{{ fmtTime(s.start) }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Step 4: Summary + Confirm (Pasarela style) -->
    <section v-if="selectedSlot && selectedService" class="grid md:grid-cols-12 gap-gutter">
      <div class="md:col-span-7 card">
        <div class="flex items-center gap-2 mb-6">
          <Receipt class="w-5 h-5 text-primary" />
          <span class="t-label">Resumen del pedido</span>
        </div>

        <div class="flex items-start gap-4">
          <div class="w-14 h-14 rounded bg-surface border border-outline-variant grid place-items-center shrink-0">
            <Scissors class="w-6 h-6 text-on-surface-variant" />
          </div>
          <div class="flex-1">
            <div class="flex justify-between gap-3">
              <h3 class="font-display text-h2">{{ selectedService?.name }}</h3>
              <span class="font-display text-h2">{{ eur(selectedService?.price_cents) }}</span>
            </div>
            <p v-if="selectedService?.description" class="t-muted text-sm mt-1 line-clamp-2">{{ selectedService?.description }}</p>
          </div>
        </div>

        <div class="card-sm mt-6 !bg-surface-container-low">
          <div class="flex items-center justify-between py-1">
            <span class="t-label-muted flex items-center gap-2"><Calendar class="w-4 h-4" /> Fecha y hora</span>
            <span class="t-body">{{ fmtFullDateTime(selectedSlot.start) }}</span>
          </div>
          <div class="divider my-3"></div>
          <div class="flex items-center justify-between py-1">
            <span class="t-label-muted flex items-center gap-2"><User class="w-4 h-4" /> Barbero</span>
            <span class="t-body">{{ employeeName }}</span>
          </div>
        </div>

        <div class="mt-6 space-y-2">
          <div class="flex justify-between t-muted text-sm">
            <span>Subtotal</span><span>{{ eur(selectedService?.price_cents) }}</span>
          </div>
          <div class="flex justify-between t-muted text-sm">
            <span>Impuestos (incluidos)</span><span>—</span>
          </div>
          <div class="divider !my-3"></div>
          <div class="flex justify-between items-baseline">
            <span class="t-label">Total</span>
            <span class="font-display text-h1">{{ eur(selectedService?.price_cents) }}</span>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 t-micro text-center pt-6 divider">
          <div class="flex items-center justify-center gap-2"><ShieldCheck class="w-4 h-4 text-primary" /> Encriptado SSL</div>
          <div class="flex items-center justify-center gap-2"><CheckCircle2 class="w-4 h-4 text-primary" /> Compra segura</div>
        </div>
      </div>

      <div class="md:col-span-5 card">
        <h3 class="t-label mb-6">Notas y pago</h3>

        <label class="label">Notas (opcional)</label>
        <textarea v-model="notes" rows="3" placeholder="Indicaciones, alergias, preferencias…" class="input mb-6"></textarea>

        <p v-if="error" class="text-error text-sm mb-3">{{ error }}</p>

        <div class="space-y-3">
          <button class="btn-primary w-full" :disabled="submitting" @click="book(true)">
            <CreditCard class="w-4 h-4" /> Confirmar pago seguro · {{ eur(selectedService?.price_cents) }}
          </button>
          <button class="btn-secondary w-full" :disabled="submitting" @click="book(false)">
            Reservar y pagar luego
          </button>
        </div>

        <p class="t-micro mt-6 text-center">Pago procesado por Stripe</p>
      </div>
    </section>

    <!-- Modal de Google Calendar -->
    <div v-if="showCalendarModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeCalendarModal">
      <div class="card max-w-md w-full text-center">
        <div class="w-16 h-16 rounded-full bg-primary/20 grid place-items-center mx-auto mb-4">
          <Check class="w-8 h-8 text-primary" />
        </div>
        <h2 class="font-display text-h2 mb-2">¡Cita reservada!</h2>
        <p class="t-muted mb-6">
          Tu cita para <strong>{{ selectedService?.name }}</strong> el {{ fmtFullDateTime(lastAppointment?.start_at) }} ha sido confirmada.
        </p>

        <div class="space-y-3">
          <a
            :href="googleCalendarLink"
            target="_blank"
            class="btn-primary w-full inline-flex justify-center"
          >
            <ExternalLink class="w-4 h-4" />
            Añadir a Google Calendar
          </a>
          <button class="btn-secondary w-full" @click="closeCalendarModal">
            No, gracias. Ver mis citas
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
