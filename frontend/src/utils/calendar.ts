// Genera enlace para añadir evento a Google Calendar
export function generateGoogleCalendarLink(params: {
  title: string
  start: string // ISO 8601
  end: string // ISO 8601
  description?: string
  location?: string
}): string {
  const { title, start, end, description = '', location = '' } = params

  // Convertir a formato Google Calendar: YYYYMMDDTHHMMSSZ
  const formatDate = (iso: string) => {
    const d = new Date(iso)
    return d.toISOString().replace(/[-:]/g, '').replace(/\.\d{3}/, '')
  }

  const dates = `${formatDate(start)}/${formatDate(end)}`

  const url = new URL('https://calendar.google.com/calendar/render')
  url.searchParams.set('action', 'TEMPLATE')
  url.searchParams.set('text', title)
  url.searchParams.set('dates', dates)
  if (description) url.searchParams.set('details', description)
  if (location) url.searchParams.set('location', location)

  return url.toString()
}
