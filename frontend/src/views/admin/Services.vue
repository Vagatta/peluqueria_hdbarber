<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useServicesStore } from '@/stores/services'
import type { Service } from '@/types'
import { Plus, Pencil, Trash2 } from 'lucide-vue-next'

const store = useServicesStore()
onMounted(() => store.fetch())

const editing = ref<Partial<Service> | null>(null)
const isNew = ref(false)

function startCreate() {
  editing.value = { name: '', description: '', duration_minutes: 30, price_cents: 1500, active: true, _price_eur: 15.00 }
  isNew.value = true
}
function startEdit(s: Service) {
  editing.value = { ...s, _price_eur: +(s.price_cents / 100).toFixed(2) }
  isNew.value = false
}
async function save() {
  if (!editing.value) return
  // Convertir euros a céntimos antes de guardar
  editing.value.price_cents = Math.round((editing.value._price_eur || 0) * 100)
  if (isNew.value) await store.create(editing.value)
  else if (editing.value.id) await store.update(editing.value.id, editing.value)
  editing.value = null
}
async function remove(s: Service) {
  if (!confirm(`¿Eliminar "${s.name}"?`)) return
  await store.remove(s.id)
}
</script>

<template>
  <div>
    <div class="flex items-end justify-between mb-gutter gap-4">
      <div>
        <span class="t-label-muted">Catálogo</span>
        <h1 class="font-display text-display mt-1">Servicios</h1>
      </div>
      <button class="btn-primary" @click="startCreate"><Plus class="w-4 h-4"/>Nuevo</button>
    </div>

    <div class="card !p-0 overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left">
            <th class="t-label-muted py-4 px-6">Nombre</th>
            <th class="t-label-muted">Duración</th>
            <th class="t-label-muted">Precio</th>
            <th class="t-label-muted">Activo</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in store.items" :key="s.id" class="border-t border-surface-container-highest hover:bg-surface-container-low transition-colors">
            <td class="py-4 px-6">
              <p class="text-on-surface">{{ s.name }}</p>
              <p class="t-muted text-xs truncate max-w-xs">{{ s.description }}</p>
            </td>
            <td class="text-on-surface-variant">{{ s.duration_minutes }} min</td>
            <td class="font-display text-on-surface">{{ (s.price_cents/100).toFixed(2) }} €</td>
            <td><span class="chip" :class="s.active ? 'chip-success' : 'chip-danger'">{{ s.active ? 'Sí' : 'No' }}</span></td>
            <td class="text-right pr-6">
              <div class="inline-flex gap-2">
                <button class="btn-ghost !py-1.5 !px-2 !text-xs" @click="startEdit(s)"><Pencil class="w-3.5 h-3.5"/></button>
                <button class="btn-danger !py-1.5 !px-2 !text-xs" @click="remove(s)"><Trash2 class="w-3.5 h-3.5"/></button>
              </div>
            </td>
          </tr>
          <tr v-if="!store.items.length">
            <td colspan="5" class="py-8 text-center t-muted">Sin servicios todavía.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <Transition
      enter-from-class="opacity-0" enter-active-class="transition-opacity duration-150"
      leave-to-class="opacity-0" leave-active-class="transition-opacity duration-150"
    >
      <div v-if="editing" class="fixed inset-0 z-40 grid place-items-center bg-black/70 backdrop-blur-sm p-4" @click.self="editing = null">
        <div class="card-elev w-full max-w-md shadow-edge">
          <h2 class="font-display text-h1 mb-6">{{ isNew ? 'Nuevo servicio' : 'Editar servicio' }}</h2>
          <div class="space-y-4">
            <div><label class="label">Nombre</label><input v-model="editing.name" class="input"/></div>
            <div><label class="label">Descripción</label><textarea v-model="editing.description" rows="2" class="input"></textarea></div>
            <div class="grid grid-cols-2 gap-3">
              <div><label class="label">Duración (min)</label><input v-model.number="editing.duration_minutes" type="number" min="5" step="5" class="input"/></div>
              <div>
                <label class="label">Precio (€)</label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 t-muted text-sm">€</span>
                  <input v-model.number="editing._price_eur" type="number" min="0" step="0.5" class="input !pl-7"/>
                </div>
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
  </div>
</template>
