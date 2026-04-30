---
name: Urban Grooming System
colors:
  surface: '#121414'
  surface-dim: '#121414'
  surface-bright: '#383939'
  surface-container-lowest: '#0d0e0f'
  surface-container-low: '#1b1c1c'
  surface-container: '#1f2020'
  surface-container-high: '#292a2a'
  surface-container-highest: '#343535'
  on-surface: '#e3e2e2'
  on-surface-variant: '#c4c7c7'
  inverse-surface: '#e3e2e2'
  inverse-on-surface: '#303031'
  outline: '#8e9192'
  outline-variant: '#444748'
  surface-tint: '#c8c6c5'
  primary: '#c8c6c5'
  on-primary: '#313030'
  primary-container: '#121212'
  on-primary-container: '#7e7d7d'
  inverse-primary: '#5f5e5e'
  secondary: '#c8c6c5'
  on-secondary: '#303030'
  secondary-container: '#474747'
  on-secondary-container: '#b6b5b4'
  tertiary: '#c6c6c6'
  on-tertiary: '#2f3131'
  tertiary-container: '#101212'
  on-tertiary-container: '#7c7d7d'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#e5e2e1'
  primary-fixed-dim: '#c8c6c5'
  on-primary-fixed: '#1c1b1b'
  on-primary-fixed-variant: '#474646'
  secondary-fixed: '#e4e2e1'
  secondary-fixed-dim: '#c8c6c5'
  on-secondary-fixed: '#1b1c1c'
  on-secondary-fixed-variant: '#474747'
  tertiary-fixed: '#e2e2e2'
  tertiary-fixed-dim: '#c6c6c6'
  on-tertiary-fixed: '#1a1c1c'
  on-tertiary-fixed-variant: '#454747'
  background: '#121414'
  on-background: '#e3e2e2'
  surface-variant: '#343535'
typography:
  headline-lg:
    fontFamily: Space Grotesk
    fontSize: 48px
    fontWeight: '700'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Space Grotesk
    fontSize: 32px
    fontWeight: '600'
    lineHeight: '1.2'
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Space Grotesk
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.5'
  label-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '600'
    lineHeight: '1.0'
    letterSpacing: 0.05em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 48px
  xl: 80px
  gutter: 24px
  margin: 32px
---

## Brand & Style
The design system is rooted in the "Modern Industrial" aesthetic, mirroring the tactile environment of a high-end urban barber shop. It targets a discerning clientele that values precision, cleanliness, and a sophisticated masculine atmosphere. 

The visual style is a blend of **Minimalism** and **Corporate Modern**, prioritizing structured layouts and high-quality finishes. The emotional response should be one of immediate trust and premium service—evoking the smell of sandalwood and the precision of a straight razor. It avoids unnecessary flourishes, opting instead for architectural rigor and a "less is more" philosophy.

## Colors
The palette is monochromatic and mood-driven, pulling directly from the charcoal seating and slate-gray walls of the interior. 

- **Primary (Deep Charcoal):** Used for the core canvas and backgrounds to create a sense of depth and luxury.
- **Secondary (Slate Gray):** Used for UI layering, such as cards and containers, to provide subtle contrast against the primary background.
- **Tertiary (Metallic Silver):** Reserved for high-priority calls to action, borders, and accents. It represents the polished steel of barbering tools.
- **Neutral (Muted Gray):** Utilized for secondary text and decorative elements to ensure the interface doesn't feel overly aggressive.

## Typography
The typography strategy pairs industrial character with utilitarian clarity. 

**Space Grotesk** is the voice of the brand, used for all headlines. Its geometric quirks and "ink-trap" inspired details provide an urban, tech-forward feel. For body copy, **Inter** provides maximum readability and a neutral tone that balances the expressive nature of the headlines. Label text should frequently utilize uppercase styling with increased letter spacing to mimic the technical labeling found on grooming product packaging.

## Layout & Spacing
The design system employs a **Fixed Grid** model to maintain a sense of organization and "groomed" structure. A 12-column grid is used for desktop views, transitioning to a 4-column grid for mobile.

Spacing follows a strict 8px rhythm. Content density is kept moderate to high; white space (or "dark space") is used intentionally to separate service categories and pricing tiers, mirroring the organized stations of a master barber. Large margins (32px+) are used at the edges of the viewport to frame the content like a gallery piece.

## Elevation & Depth
Depth in this design system is achieved through **Tonal Layers** and **Low-Contrast Outlines** rather than traditional shadows.

Surfaces are "stacked" using progressively lighter shades of gray. A base level starts at `#121212`, while an elevated card sits at `#1C1C1C`. To define these edges further, a 1px solid border in `#2C2C2C` or a semi-transparent silver is applied. This creates a tactile, "machined" look that feels more premium and physical than soft, ambient shadows. If a shadow is required for a floating element (like a modal), it must be a sharp, low-blur shadow that mimics hard directional studio lighting.

## Shapes
The shape language is primarily linear and sharp. A "Soft" roundedness level (4px) is applied to maintain a professional, high-end feel without becoming overly aggressive or "brutalist." 

This slight rounding suggests quality and craftsmanship—like the beveled edge of a mirror or the corner of a leather chair. Interactive elements like buttons and input fields should strictly adhere to this 4px radius to ensure a cohesive, organized appearance across the entire platform.

## Components

### Buttons
Buttons are the primary expression of the "Silver Accents" requested.
- **Primary:** Metallic Silver background with Black text. No shadow, sharp 4px corners.
- **Secondary:** Transparent background with a 1px Silver border and Silver text.
- **Hover State:** Subtle inner glow or a transition to a slightly brighter slate gray.

### Input Fields
Inputs should feel like technical forms. Use a dark background (#1C1C1C) with a bottom-only border or a very subtle full stroke. Labels sit above the input in the "Label-MD" uppercase style.

### Cards
Cards are used for service menus and barber profiles. They feature a #1C1C1C background and a 1px stroke in #2C2C2C. Images within cards should use a desaturated color profile or high-contrast black and white to match the brand aesthetic.

### Chips & Tags
Use chips for "Available Today" or "Master Barber" designations. These are small, pill-shaped or slightly rounded containers with low-contrast backgrounds and high-contrast silver text.

### Additional Components
- **Service Lists:** Use thin silver dividers (0.5px) between list items to maintain the sleek, organized feel.
- **Booking Calendar:** A high-contrast grid where the selected date is highlighted with a silver "machined" border.