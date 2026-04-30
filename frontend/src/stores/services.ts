import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/api/client'
import type { Service } from '@/types'

export const useServicesStore = defineStore('services', () => {
  const items = ref<Service[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetch() {
    loading.value = true
    try {
      const { data } = await api.get<Service[]>('/api/services')
        items.value = data
    } finally { loading.value = false }
  }

  async function create(payload: Partial<Service>) {
    const { data } = await api.post<Service>('/api/admin/services', payload)
    items.value.unshift(data)
    return data
  }

  async function update(id: number, payload: Partial<Service>) {
    const { data } = await api.patch<Service>(`/api/admin/services/${id}`, payload)
    const i = items.value.findIndex(s => s.id === id)
    if (i >= 0) items.value[i] = data
    return data
  }

  async function remove(id: number) {
    await api.delete(`/api/admin/services/${id}`)
    items.value = items.value.filter(s => s.id !== id)
  }

  return { items, loading, error, fetch, create, update, remove }
})
