/** @type {import('tailwindcss').Config} */
export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{vue,js,ts}'],
  theme: {
    extend: {
      colors: {
        // Urban Grooming monochrome palette
        background: '#121414',
        surface: '#121414',
        'surface-dim': '#121414',
        'surface-bright': '#383939',
        'surface-container-lowest': '#0d0e0f',
        'surface-container-low': '#1b1c1c',
        'surface-container': '#1f2020',
        'surface-container-high': '#292a2a',
        'surface-container-highest': '#343535',
        'surface-variant': '#343535',
        'on-surface': '#e3e2e2',
        'on-surface-variant': '#c4c7c7',
        'on-background': '#e3e2e2',
        outline: '#8e9192',
        'outline-variant': '#444748',
        primary: '#c8c6c5',
        'on-primary': '#313030',
        'primary-container': '#121212',
        'on-primary-container': '#7e7d7d',
        'primary-fixed': '#e5e2e1',
        'primary-fixed-dim': '#c8c6c5',
        secondary: '#c8c6c5',
        'on-secondary': '#303030',
        'secondary-container': '#474747',
        'on-secondary-container': '#b6b5b4',
        tertiary: '#c6c6c6',
        'on-tertiary': '#2f3131',
        'tertiary-fixed': '#e2e2e2',
        error: '#ffb4ab',
        'on-error': '#690005',
        'error-container': '#93000a',
        'on-error-container': '#ffdad6',
        // Compat: keep "brand" as silver mapping so old refs don't blow up
        brand: {
          50: '#f5f5f5',
          100: '#e3e2e2',
          200: '#c8c6c5',
          300: '#a8a7a6',
          400: '#8e9192',
          500: '#5f5e5e',
          600: '#444748',
          700: '#343535',
          800: '#1f2020',
          900: '#121414'
        }
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        display: ['"Space Grotesk"', 'Inter', 'sans-serif']
      },
      fontSize: {
        'display': ['48px', { lineHeight: '1.1', letterSpacing: '-0.02em', fontWeight: '700' }],
        'h1': ['32px', { lineHeight: '1.2', letterSpacing: '-0.01em', fontWeight: '600' }],
        'h2': ['24px', { lineHeight: '1.3', fontWeight: '600' }],
        'body-lg': ['18px', { lineHeight: '1.6', fontWeight: '400' }],
        'body-md': ['16px', { lineHeight: '1.5', fontWeight: '400' }],
        'label': ['14px', { lineHeight: '1', letterSpacing: '0.05em', fontWeight: '600' }],
        'micro': ['11px', { lineHeight: '1', letterSpacing: '0.08em', fontWeight: '600' }]
      },
      borderRadius: {
        DEFAULT: '0.25rem', // 4px (Urban Grooming "soft")
        sm: '0.125rem',
        md: '0.375rem',
        lg: '0.5rem',
        xl: '0.75rem'
      },
      spacing: {
        gutter: '24px',
        margin: '32px'
      },
      boxShadow: {
        'flat': 'none',
        'edge': 'inset 0 0 0 1px #444748'
      }
    }
  },
  plugins: []
}
