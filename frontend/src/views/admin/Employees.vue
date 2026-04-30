<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api } from '@/api/client'
import { useServicesStore } from '@/stores/services'
import type { Employee } from '@/types'
import { Plus, Pencil, Trash2, Clock } from 'lucide-vue-next'

const items = ref<Employee[]>([])
const loading = ref(false)
const editing = ref<any>(null)
const isNew = ref(false)
const editingHours = ref<any>(null) // empleado cuyo horario se edita
const services = useServicesStore()

const DAYS = [
  { key: 'mon', label: 'Lunes' },
  { key: 'tue', label: 'Martes' },
  { key: 'wed', label: 'Miércoles' },
  { key: 'thu', label: 'Jueves' },
  { key: 'fri', label: 'Viernes' },
  { key: 'sat', label: 'Sábado' },
  { key: 'sun', label: 'Domingo' },
]

async function fetchAll() {
  loading.value = true
  try {
    const { data } = await api.get<Employee[]>('/api/admin/employees')
    items.value = data
  } finally { loading.value = false }
}

onMounted(() => { fetchAll(); services.fetch() })

function startCreate() {
  editing.value = { name: '', position: '', active: true, service_ids: [] }
  isNew.value = true
}
function startEdit(e: Employee) {
  editing.value = { ...e, service_ids: (e.services || []).map(s => s.id) }
  isNew.value = false
}
async function save() {
  if (isNew.value) {
    await api.post('/api/admin/employees', editing.value)
  } else {
    await api.patch(`/api/admin/employees/${editing.value.id}`, editing.value)
  }
  editing.value = null
  fetchAll()
}
async function remove(e: Employee) {
  if (!confirm(`¿Eliminar "${e.name}"?`)) return
  await api.delete(`/api/admin/employees/${e.id}`)
  fetchAll()
}

// --- Horarios ---
function openHours(e: Employee) {
  // Clonar el objeto working_hours para edición
  const wh: any = {}
  for (const d of DAYS) {
    wh[d.key] = (e.working_hours?.[d.key] || []).map((r: any) => ({ ...r }))
  }
  editingHours.value = { employee: e, wh }
}

function isDayOpen(wh: any, day: string): boolean {
  return (wh[day]?.length ?? 0) > 0
}

function toggleDay(wh: any, day: string) {
  if (isDayOpen(wh, day)) {
    wh[day] = []
  } else {
    wh[day] = [{ start: '09:30', end: '14:00' }, { start: '16:00', end: '20:00' }]
  }
}

function addRange(wh: any, day: string) {
  wh[day].push({ start: '09:00', end: '14:00' })
}

function removeRange(wh: any, day: string, i: number) {
  wh[day].splice(i, 1)
}

async function saveHours() {
  const { employee, wh } = editingHours.value
  await api.patch(`/api/admin/employees/${employee.id}`, { working_hours: wh })
  editingHours.value = null
  fetchAll()
}

function dayRangeSummary(e: Employee, day: string): string {
  const ranges = e.working_hours?.[day] || []
  if (!ranges.length) return 'Cerrado'
  return ranges.map((r: any) => `${r.start}–${r.end}`).join(', ')
}
</script>

<template>
  <div>
    <div class="flex items-end justify-between mb-gutter gap-4">
      <div>
        <span class="t-label-muted">Equipo</span>
        <h1 class="font-display text-display mt-1">Empleados</h1>
      </div>
      <button class="btn-primary" @click="startCreate"><Plus class="w-4 h-4"/>Nuevo</button>
    </div>

    <div v-if="loading" class="t-muted">Cargando…</div>
    <div v-else-if="!items.length" class="card text-center py-12 t-muted">Sin empleados todavía.</div>
    <div v-else class="grid sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
      <div v-for="e in items" :key="e.id" class="card">
        <div class="flex justify-between items-start gap-2">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-12 h-12 rounded-full bg-surface-container-high border border-outline-variant grid place-items-center text-h2 font-display uppercase shrink-0">
              {{ e.name?.charAt(0) || '?' }}
            </div>
            <div class="min-w-0">
              <p class="font-display text-h2 truncate">{{ e.name }}</p>
              <p class="t-muted text-sm truncate">{{ e.position || '—' }}</p>
            </div>
          </div>
          <span class="chip" :class="e.active ? 'chip-success' : 'chip-danger'">{{ e.active ? 'Activo' : 'Inactivo' }}</span>
        </div>

        <!-- Resumen horario semanal -->
        <div class="mt-3 space-y-0.5">
          <div v-for="d in DAYS" :key="d.key" class="flex items-center gap-2 text-xs">
            <span class="w-6 t-label-muted shrink-0">{{ d.label.slice(0, 2) }}</span>
            <span :class="dayRangeSummary(e, d.key) === 'Cerrado' ? 'text-on-surface-variant/50' : 't-muted'">
              {{ dayRangeSummary(e, d.key) }}
            </span>
          </div>
        </div>

        <p class="t-muted text-xs mt-3 line-clamp-2 min-h-[2rem]">
          {{ (e.services||[]).map(s=>s.name).join(' · ') || 'Sin servicios asignados' }}
        </p>
        <div class="mt-4 pt-4 divider flex gap-2">
          <button class="btn-ghost !py-2 !px-3 flex-1" @click="startEdit(e)"><Pencil class="w-3.5 h-3.5"/>Editar</button>
          <button class="btn-ghost !py-2 !px-3 flex-1" @click="openHours(e)"><Clock class="w-3.5 h-3.5"/>Horario</button>
          <button class="btn-danger !py-2 !px-3" @click="remove(e)"><Trash2 class="w-3.5 h-3.5"/></button>
        </div>
      </div>
    </div>

    <!-- Modal editar empleado -->
    <Transition
      enter-from-class="opacity-0" enter-active-class="transition-opacity duration-150"
      leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150"
    >
      <div v-if="editing" class="fixed inset-0 z-40 grid place-items-center bg-black/70 backdrop-blur-sm p-4" @click.self="editing = null">
        <div class="card-elev w-full max-w-md shadow-edge">
          <h2 class="font-display text-h1 mb-6">{{ isNew ? 'Nuevo empleado' : 'Editar empleado' }}</h2>
          <div class="space-y-4">
            <div><label class="label">Nombre</label><input v-model="editing.name" class="input"/></div>
            <div><label class="label">Posición</label><input v-model="editing.position" class="input"/></div>
            <div>
              <label class="label">Servicios</label>
              <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 bg-surface-container-low border border-outline-variant rounded">
                <label v-for="s in services.items" :key="s.id" class="inline-flex items-center gap-2 text-sm text-on-surface">
                  <input type="checkbox" :value="s.id" v-model="editing.service_ids" class="accent-primary"/>
                  {{ s.name }}
                </label>
              </div>
            </div>
            <label class="inline-flex items-center gap-2 text-on-surface text-sm">
              <input type="checkbox" v-model="editing.active" class="accent-primary"/> Activo
            </label>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button class="btn-ghost" @click="editing = null">Cancelar</button>
            <button class="btn-primary" @click="save">Guardar</button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal editar horarios -->
    <Transition
      enter-from-class="opacity-0" enter-active-class="transition-opacity duration-150"
      leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150"
    >
      <div v-if="editingHours" class="fixed inset-0 z-40 grid place-items-center bg-black/70 backdrop-blur-sm p-4" @click.self="editingHours = null">
        <div class="card-elev w-full max-w-lg shadow-edge max-h-[90vh] overflow-y-auto">
          <h2 class="font-display text-h1 mb-1">Horario semanal</h2>
          <p class="t-muted text-sm mb-6">{{ editingHours.employee.name }}</p>

          <div class="space-y-4">
            <div v-for="d in DAYS" :key="d.key" class="border border-outline-variant rounded p-4">
              <div class="flex items-center justify-between mb-3">
                <span class="t-label">{{ d.label }}</span>
                <label class="inline-flex items-center gap-2 text-sm text-on-surface cursor-pointer">
                  <input
                    type="checkbox"
                    :checked="isDayOpen(editingHours.wh, d.key)"
                    @change="toggleDay(editingHours.wh, d.key)"
                    class="accent-primary"
                  />
                  <span>{{ isDayOpen(editingHours.wh, d.key) ? 'Abierto' : 'Cerrado' }}</span>
                </label>
              </div>

              <div v-if="isDayOpen(editingHours.wh, d.key)" class="space-y-2">
                <div v-for="(range, i) in editingHours.wh[d.key]" :key="i" class="flex items-center gap-2">
                  <input type="time" v-model="range.start" class="input !py-1.5 flex-1 text-sm" />
                  <span class="t-muted text-xs">—</span>
                  <input type="time" v-model="range.end" class="input !py-1.5 flex-1 text-sm" />
                  <button class="btn-danger !py-1 !px-2" @click="removeRange(editingHours.wh, d.key, i)">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </div>
                <button class="btn-ghost !py-1.5 !px-3 !text-xs w-full mt-1" @click="addRange(editingHours.wh, d.key)">
                  <Plus class="w-3 h-3" /> Añadir franja
                </button>
              </div>
              <p v-else class="text-xs t-muted italic">Sin actividad este día</p>
            </div>
          </div>

          <div class="mt-6 flex justify-end gap-2">
            <button class="btn-ghost" @click="editingHours = null">Cancelar</button>
            <button class="btn-primary" @click="saveHours">Guardar horario</button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
