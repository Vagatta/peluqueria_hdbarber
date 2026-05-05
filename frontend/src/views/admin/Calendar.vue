<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useAppointmentsStore } from '@/stores/appointments'
import { useServicesStore } from '@/stores/services'
import { api } from '@/api/client'
import type { Service, Slot, Appointment, PaymentStatusAppt, Employee, Client } from '@/types'
import DatePicker from '@/components/DatePicker.vue'
import { Plus, X, Check, Clock, User } from 'lucide-vue-next'

// Convierte un valor datetime-local ("2026-04-30T10:00") a ISO 8601 con offset local
// para que el backend reciba la hora correcta independientemente de la zona horaria
function localToIso(datetimeLocal: string): string {
  const d = new Date(datetimeLocal)
  // toISOString() da UTC, necesitamos el offset local
  const offset = -d.getTimezoneOffset()
  const sign = offset >= 0 ? '+' : '-'
  const pad = (n: number) => String(Math.floor(Math.abs(n))).padStart(2, '0')
  return d.getFullYear() + '-' +
    pad(d.getMonth() + 1) + '-' +
    pad(d.getDate()) + 'T' +
    pad(d.getHours()) + ':' +
    pad(d.getMinutes()) + ':00' +
    sign + pad(offset / 60) + ':' + pad(offset % 60)
}

const appts = useAppointmentsStore()
const services = useServicesStore()
// Fecha local en formato YYYY-MM-DD (evitar que toISOString() devuelva fecha UTC errónea)
function todayLocal(): string {
  const d = new Date()
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`
}
// Obtiene la fecha local YYYY-MM-DD de un string ISO del backend
function dateLocal(iso: string): string {
  const d = new Date(iso)
  return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`
}

const date = ref<string>(todayLocal())

// Empleados y clientes para los selectores
const employees = ref<Employee[]>([])
interface Client { id: number; name: string; email: string }
const clients = ref<Client[]>([])

onMounted(async () => {
  await Promise.all([
    appts.fetch(),
    services.fetch(),
    api.get<Employee[]>('/api/admin/employees').then(r => { employees.value = r.data }),
    api.get<Client[]>('/api/admin/clients').then(r => { clients.value = r.data }),
  ])
})

const dayAppts = computed<Appointment[]>(() =>
  appts.items
    .filter(a => dateLocal(a.start_at) === date.value && a.status !== 'cancelled')
    .sort((a, b) => a.start_at.localeCompare(b.start_at))
)

const hours = Array.from({ length: 14 }, (_, i) => i + 8) // 8..21

function topFor(a: Appointment) {
  const d = new Date(a.start_at)
  return (d.getHours() - 8) * 60 + d.getMinutes()
}
function heightFor(a: Appointment) {
  const start = new Date(a.start_at).getTime()
  const end = new Date(a.end_at).getTime()
  return Math.max(32, (end - start) / 60000)
}

// --- Estado del modal de creación ---
const creating = ref(false)
const createForm = ref({ user_id: 0, service_id: 0, employee_id: null as number | null, start_at: '', notes: '' })
const createError = ref('')
const createLoading = ref(false)

function openCreate() {
  createForm.value = { user_id: 0, service_id: 0, employee_id: null, start_at: `${date.value}T10:00`, notes: '' }
  createError.value = ''
  creating.value = true
}

async function submitCreate() {
  if (!createForm.value.user_id || !createForm.value.service_id || !createForm.value.start_at) {
    createError.value = 'Cliente, servicio y hora son obligatorios.'
    return
  }
  createLoading.value = true
  createError.value = ''
  try {
    await appts.adminCreate({
      user_id: createForm.value.user_id,
      service_id: createForm.value.service_id,
      employee_id: createForm.value.employee_id,
      start_at: localToIso(createForm.value.start_at),
      notes: createForm.value.notes || undefined,
    })
    creating.value = false
  } catch (e: any) {
    createError.value = e?.response?.data?.message || Object.values(e?.response?.data?.errors || {})[0] as string || 'Error al crear la cita.'
  } finally {
    createLoading.value = false
  }
}

// --- Estado del panel lateral de edición ---
const selected = ref<Appointment | null>(null)
const editForm = ref({ status: '', payment_status: '', employee_id: null as number | null, start_at: '', notes: '' })
const editError = ref('')
const editLoading = ref(false)

function openEdit(a: Appointment) {
  selected.value = a
  editForm.value = {
    status: a.status,
    payment_status: a.payment_status,
    employee_id: a.employee_id,
    start_at: a.start_at.slice(0, 16),
    notes: a.notes || '',
  }
  editError.value = ''
}

async function submitEdit() {
  if (!selected.value) return
  editLoading.value = true
  editError.value = ''
  try {
    await appts.adminUpdate(selected.value.id, {
      status: editForm.value.status as Appointment['status'],
      payment_status: editForm.value.payment_status as PaymentStatusAppt,
      employee_id: editForm.value.employee_id,
      start_at: localToIso(editForm.value.start_at),
      notes: editForm.value.notes || undefined,
    })
    selected.value = null
  } catch (e: any) {
    editError.value = e?.response?.data?.message || Object.values(e?.response?.data?.errors || {})[0] as string || 'Error al actualizar.'
  } finally {
    editLoading.value = false
  }
}

async function quickStatus(a: Appointment, status: Appointment['status']) {
  try { await appts.adminUpdate(a.id, { status }) } catch (e: any) { alert(e?.response?.data?.message || 'Error') }
}

const statusColors: Record<string, string> = {
  pending: 'border-yellow-500/60 bg-yellow-500/5',
  confirmed: 'border-primary/60 bg-primary/5',
  completed: 'border-emerald-500/60 bg-emerald-500/5',
  cancelled: 'border-red-500/40 bg-red-500/5',
  no_show: 'border-zinc-500/40 bg-zinc-500/5',
}
</script>

<template>
  <div>
    <div class="mb-gutter flex items-end justify-between gap-4">
      <div>
        <span class="t-label-muted">Operaciones</span>
        <h1 class="font-display text-display mt-1">Agenda</h1>
      </div>
      <button class="btn-primary" @click="openCreate"><Plus class="w-4 h-4" />Nueva cita</button>
    </div>

    <div class="grid md:grid-cols-[18rem_1fr] gap-gutter">
      <DatePicker v-model="date" />

      <!-- Timeline del día -->
      <div class="card !p-0 overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between border-b border-surface-container-highest">
          <span class="t-label">{{ new Date(date + 'T12:00').toLocaleDateString('es-ES', { weekday: 'long', day: '2-digit', month: 'long' }) }}</span>
          <span class="t-micro">{{ dayAppts.length }} CITAS</span>
        </div>
        <div class="relative" style="height: 840px">
          <!-- Grid de horas -->
          <div class="absolute inset-0 grid" style="grid-template-rows: repeat(14, 60px)">
            <div v-for="h in hours" :key="h" class="border-t border-surface-container-highest relative">
              <span class="absolute -top-2.5 left-3 t-micro bg-surface-container px-1.5">{{ String(h).padStart(2,'0') }}:00</span>
            </div>
          </div>

          <!-- Citas -->
          <div class="absolute inset-x-0 left-16 right-3">
            <div
              v-for="a in dayAppts" :key="a.id"
              class="absolute left-0 right-0 rounded px-2 py-1.5 text-xs border cursor-pointer hover:brightness-110 transition-all overflow-hidden"
              :class="statusColors[a.status] || 'border-outline-variant bg-surface-container-high'"
              :style="{ top: topFor(a) + 'px', height: heightFor(a) + 'px' }"
              @click="openEdit(a)"
            >
              <!-- Vista compacta para citas pequeñas (<35px) -->
              <template v-if="heightFor(a) < 35">
                <div class="t-label text-on-surface truncate leading-tight">{{ a.service?.name }}</div>
              </template>
              <!-- Vista normal -->
              <template v-else>
                <div class="t-label text-on-surface truncate leading-tight">{{ a.service?.name }}</div>
                <div class="text-[10px] text-on-surface-variant/70 truncate mt-0.5 flex items-center gap-1">
                  <User class="w-2.5 h-2.5 inline shrink-0" />{{ a.employee?.name || 'Sin asignar' }}
                </div>
                <!-- Acciones rápidas si hay espacio suficiente -->
                <div v-if="heightFor(a) >= 65" class="absolute bottom-1 right-1 flex gap-1" @click.stop>
                  <button title="Confirmar" class="w-5 h-5 rounded bg-surface/80 border border-outline-variant text-on-surface hover:bg-primary/20 transition-colors" @click="quickStatus(a,'confirmed')">✓</button>
                  <button title="Completar" class="w-5 h-5 rounded bg-surface/80 border border-outline-variant text-on-surface hover:bg-emerald-500/20 transition-colors" @click="quickStatus(a,'completed')">●</button>
                  <button title="Cancelar" class="w-5 h-5 rounded bg-error/10 border border-error/40 text-error hover:bg-error/25 transition-colors" @click="quickStatus(a,'cancelled')">✕</button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Panel lateral de edición / reasignación -->
    <Transition
      enter-from-class="translate-x-full opacity-0" enter-active-class="transition-all duration-200"
      leave-to-class="translate-x-full opacity-0" leave-active-class="transition-all duration-200"
    >
      <div v-if="selected" class="fixed inset-y-0 right-0 z-50 w-full max-w-sm bg-[#121212] border-l border-surface-container-highest shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-6 py-5 border-b border-surface-container-highest">
          <h2 class="font-display text-h1">Editar cita</h2>
          <button class="btn-ghost !p-2" @click="selected = null"><X class="w-4 h-4" /></button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4">
          <!-- Info no editable -->
          <div class="card-sm !p-4 bg-surface-container-low space-y-1">
            <p class="t-label">{{ selected.service?.name }}</p>
            <p class="t-muted text-xs">Cita #{{ selected.id }}</p>
          </div>

          <!-- Reasignar fecha/hora -->
          <div>
            <label class="label flex items-center gap-1.5"><Clock class="w-3.5 h-3.5" />Nueva fecha y hora</label>
            <input type="datetime-local" v-model="editForm.start_at" class="input" />
          </div>

          <!-- Reasignar empleado -->
          <div>
            <label class="label flex items-center gap-1.5"><User class="w-3.5 h-3.5" />Empleado</label>
            <select v-model="editForm.employee_id" class="input">
              <option :value="null">Sin asignar</option>
              <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>

          <!-- Estado -->
          <div>
            <label class="label">Estado de la cita</label>
            <select v-model="editForm.status" class="input">
              <option value="pending">Pendiente</option>
              <option value="confirmed">Confirmada</option>
              <option value="completed">Completada</option>
              <option value="cancelled">Cancelada</option>
              <option value="no_show">No asistió</option>
            </select>
          </div>

          <!-- Estado de pago -->
          <div>
            <label class="label">Estado del pago</label>
            <select v-model="editForm.payment_status" class="input">
              <option value="unpaid">Sin pagar</option>
              <option value="paid">Pagado</option>
              <option value="refunded">Reembolsado</option>
              <option value="failed">Error de pago</option>
            </select>
          </div>

          <!-- Notas -->
          <div>
            <label class="label">Notas internas</label>
            <textarea v-model="editForm.notes" rows="3" class="input" placeholder="Observaciones…"></textarea>
          </div>

          <p v-if="editError" class="text-error text-sm">{{ editError }}</p>
        </div>

        <div class="px-6 py-4 border-t border-surface-container-highest flex gap-2">
          <button class="btn-ghost flex-1" @click="selected = null">Cancelar</button>
          <button class="btn-primary flex-1" :disabled="editLoading" @click="submitEdit">
            <Check class="w-4 h-4" />{{ editLoading ? 'Guardando…' : 'Guardar' }}
          </button>
        </div>
      </div>
    </Transition>

    <!-- Overlay panel lateral -->
    <Transition enter-from-class="opacity-0" enter-active-class="transition-opacity duration-200" leave-to-class="opacity-0" leave-active-class="transition-opacity duration-200">
      <div v-if="selected" class="fixed inset-0 z-40 bg-black/50" @click="selected = null" />
    </Transition>

    <!-- Modal nueva cita -->
    <Transition
      enter-from-class="opacity-0" enter-active-class="transition-opacity duration-150"
      leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150"
    >
      <div v-if="creating" class="fixed inset-0 z-50 grid place-items-center bg-black/70 backdrop-blur-sm p-4" @click.self="creating = false">
        <div class="card-elev w-full max-w-md shadow-edge">
          <h2 class="font-display text-h1 mb-6">Nueva cita</h2>
          <div class="space-y-4">

            <!-- Cliente -->
            <div>
              <label class="label">Cliente</label>
              <select v-model="createForm.user_id" class="input">
                <option :value="0" disabled>Selecciona un cliente…</option>
                <option v-for="c in clients" :key="c.id" :value="c.id">{{ c.name }} — {{ c.email }}</option>
              </select>
            </div>

            <!-- Servicio -->
            <div>
              <label class="label">Servicio</label>
              <select v-model="createForm.service_id" class="input">
                <option :value="0" disabled>Selecciona un servicio…</option>
                <option v-for="s in services.items" :key="s.id" :value="s.id">{{ s.name }} ({{ s.duration_minutes }} min)</option>
              </select>
            </div>

            <!-- Empleado -->
            <div>
              <label class="label">Empleado</label>
              <select v-model="createForm.employee_id" class="input">
                <option :value="null">Sin asignar</option>
                <option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option>
              </select>
            </div>

            <!-- Fecha y hora -->
            <div>
              <label class="label">Fecha y hora</label>
              <input type="datetime-local" v-model="createForm.start_at" class="input" />
            </div>

            <!-- Notas -->
            <div>
              <label class="label">Notas (opcional)</label>
              <textarea v-model="createForm.notes" rows="2" class="input" placeholder="Observaciones…"></textarea>
            </div>

            <p v-if="createError" class="text-error text-sm">{{ createError }}</p>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button class="btn-ghost" @click="creating = false">Cancelar</button>
            <button class="btn-primary" :disabled="createLoading" @click="submitCreate">
              <Check class="w-4 h-4" />{{ createLoading ? 'Creando…' : 'Crear cita' }}
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
