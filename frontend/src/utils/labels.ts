// Traducciones de estados para la UI
export const statusLabels: Record<string, string> = {
  pending: 'Pendiente',
  confirmed: 'Confirmada',
  cancelled: 'Cancelada',
  completed: 'Completada',
  no_show: 'No asistió'
}

export const paymentLabels: Record<string, string> = {
  unpaid: 'Pendiente de pago',
  paid: 'Pagado',
  refunded: 'Reembolsado',
  failed: 'Error de pago'
}

// Clases CSS para los chips (mantener compatibilidad con código existente)
export const statusChipClasses: Record<string, string> = {
  pending: 'chip-warn',
  confirmed: 'chip-solid',
  cancelled: 'chip-danger',
  completed: 'chip-info',
  no_show: 'chip'
}

export const paymentChipClasses: Record<string, string> = {
  unpaid: 'chip-warn',
  paid: 'chip-success',
  refunded: 'chip-info',
  failed: 'chip-danger'
}
