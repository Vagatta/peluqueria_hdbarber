import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '@/api/client'
import type { Appointment, Slot } from '@/types'

export const useAppointmentsStore = defineStore('appointments', () => {
  const items = ref<Appointment[]>([])
  const loading = ref(false)

  async function fetch() {
    loading.value = true
    try {
      const { data } = await api.get<Appointment[]>('/api/appointments')
      items.value = data
    } finally { loading.value = false }
  }

  async function availability(serviceId: number, date: string, employeeId?: number | null): Promise<Slot[]> {
    const { data } = await api.get<{ slots: Slot[] }>('/api/availability', {
      params: { service_id: serviceId, date, employee_id: employeeId || undefined }
    })
    return data.slots
  }

  async function create(payload: { service_id: number; employee_id?: number | null; start_at: string; notes?: string }) {
    const { data } = await api.post<Appointment>('/api/appointments', payload)
    items.value.unshift(data)
    return data
  }

  async function cancel(id: number) {
    const { data } = await api.post<Appointment>(`/api/appointments/${id}/cancel`)
    const i = items.value.findIndex(a => a.id === id)
    if (i >= 0) items.value[i] = data
    return data
  }

  async function checkout(id: number): Promise<{ url: string }> {
    const { data } = await api.post<{ url: string; id: string }>(`/api/appointments/${id}/checkout`)
    return data
  }

  async function adminUpdate(id: number, payload: Partial<Appointment> & { start_at?: string; payment_status?: string }) {
    const { data } = await api.patch<Appointment>(`/api/admin/appointments/${id}`, payload)
    const i = items.value.findIndex(a => a.id === id)
    if (i >= 0) items.value[i] = data
    return data
  }

  async function adminCreate(payload: { user_id: number; service_id: number; employee_id?: number | null; start_at: string; notes?: string }) {
    const { data } = await api.post<Appointment>('/api/admin/appointments', payload)
    items.value.unshift(data)
    return data
  }

  return { items, loading, fetch, availability, create, cancel, checkout, adminUpdate, adminCreate }
})
