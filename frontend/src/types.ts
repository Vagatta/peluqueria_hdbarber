export type Role = 'client' | 'admin' | 'employee'

export interface User {
  id: number
  name: string
  email: string
  phone?: string | null
  role: Role
  avatar?: string | null
}

export interface Service {
  id: number
  name: string
  slug: string
  description?: string | null
  duration_minutes: number
  price_cents: number
  image?: string | null
  active: boolean
  _price_eur?: number // campo auxiliar para UI admin
}

export interface Employee {
  id: number
  name: string
  position?: string | null
  avatar?: string | null
  active: boolean
  working_hours?: Record<string, { start: string; end: string }[]>
  services?: Pick<Service, 'id' | 'name'>[]
}

export type AppointmentStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show'
export type PaymentStatusAppt = 'unpaid' | 'paid' | 'refunded' | 'failed'

export interface Appointment {
  id: number
  user_id: number
  service_id: number
  employee_id: number | null
  start_at: string
  end_at: string
  status: AppointmentStatus
  payment_status: PaymentStatusAppt
  notes?: string | null
  service?: Service
  employee?: Employee | null
}

export interface Slot {
  start: string
  end: string
  employee_id: number | null
}

export interface Payment {
  id: number
  amount_cents: number
  currency: string
  status: 'pending' | 'processing' | 'succeeded' | 'failed' | 'refunded' | 'cancelled'
  created_at: string
  appointment?: Appointment
  user?: User
}

export interface DashboardStats {
  appointments_today: number
  appointments_week: number
  revenue_month_cents: number
  clients_total: number
  upcoming: Appointment[]
}
