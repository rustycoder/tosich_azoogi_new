---
trigger: always_on
---

# Azoogi UI Style Guide

This style guide documents the core design system, responsive layout systems, component catalog, interactive motion behaviors, and scripts. It serves as a single source of truth for maintainers implementing, extending, or refactoring the Azoogi front-end.

---

## 1. Design Tokens & Styling System

The Azoogi interface employs a modern, dark-by-default, glassmorphism-enhanced design with adaptive contrast scaling for light mode.

### 1.1 Color Palettes & Custom Variables
Theme states toggle via `[data-theme="light"]` on the `<html>` element.

| Variable | Dark Theme (Default) | Light Theme | Description |
| :--- | :--- | :--- | :--- |
| `--bg` | `#0b0b0b` | `#ffffff` | Primary background |
| `--bg-2` | `#111111` | `#ffffff` | Secondary component background |
| `--ink` | `#f3f3f3` | `#111111` | Primary text/foreground |
| `--muted` | `#9a9a9a` | `#555555` | Secondary text |
| `--line` | `rgba(255, 255, 255, 0.12)` | `rgba(0, 0, 0, 0.12)` | Primary thin border/divider |
| `--accent` | `#67d04e` (Lime Green) | `#3aa028` (Dark Green) | Accent highlights / hover state |
| `--accent-2` | `#3aa028` (Olive Green) | `#2d7a1e` (Forest Green) | Outline button border |
| `--pure-bg` | `#0b0b0b` | `#ffffff` | Absolute background |
| `--pure-text` | `#ffffff` | `#111111` | Absolute text |
| `--always-white`| `#ffffff` | `#ffffff` | Fixed white, ignoring themes |
| `--rgba-line` | `rgba(255, 255, 255, 0.1)` | `rgba(0, 0, 0, 0.1)` | Component borders |
| `--rgba-hover` | `rgba(255, 255, 255, 0.05)`| `rgba(0, 0, 0, 0.05)` | Hover state bg |
| `--nav-bg` | `rgba(10, 10, 10, 0.95)` | `rgba(255, 255, 255, 0.98)` | Nav overlay background |
| `--nav-text` | `#ffffff` | `#ffffff` (Darkens when solid) | Navigation links |
| `--card-bg` | `rgba(255, 255, 255, 0.03)`| `rgba(0, 0, 0, 0.03)` | Subtle card fills |
| `--card-hover` | `rgba(255, 255, 255, 0.06)`| `rgba(0, 0, 0, 0.06)` | Hover state card fills |
| `--border-light`| `rgba(255, 255, 255, 0.08)`| `rgba(0, 0, 0, 0.08)` | Thin lines |
| `--shadow` | `rgba(0, 0, 0, 0.5)` | `rgba(0, 0, 0, 0.1)` | Drop-shadow |
| `--thumb-bg` | `rgba(255, 255, 255, 0.2)` | `rgba(0, 0, 0, 0.2)` | Scrollbar thumb bg |
| `--track-bg` | `rgba(0, 0, 0, 0.2)` | `rgba(0, 0, 0, 0.05)` | Scrollbar track bg |

### 1.2 Typography & Spacing
*   **Primary Font Family**: `'Inter', sans-serif` for labels, menus, body copy, and metadata (Weights: `300` to `800`).
*   **Secondary Heading Font Family**: `'Cormorant Garamond', serif` for logo text, section titles (`h2`), and secondary headings (`h3`).
*   **Font Rendering**: `-webkit-font-smoothing: antialiased` globally.
*   **Key Scales**:
    *   Hero Slide Title: `clamp(40px, 6.5vw, 96px)` (line-height: 1.02).
    *   Section Heading (`.h2`): `clamp(36px, 4.5vw, 64px)` (line-height: 1.05, letter-spacing: -.01em).
    *   Body Copy: Line height `1.55`, standard bottom padding `15px`.
*   **Wrappers**: Site wrapper `.wrap` (Max-width `1400px`, padding `28px`), Narrow `.wrap-sm` (Max-width `1200px`, padding `28px`). Standard padding: `.section-pad` (`140px 0`). Smooth mandatory scrolling applied: `scroll-behavior: smooth; scroll-snap-type: y mandatory`.

---

## 2. Interactive Component Catalog

### 2.1 Fixed Header & Navigation
Composed of an utility top bar `.util` and primary navigation bar.
*   **Scrolling Transition**: Transparent over hero, transitions to glassmorphic `.topbar.solid` (background: `rgba(11,11,11,0.85)` / `rgba(255,255,255,0.85)`, `backdrop-filter: blur(14px)`) past `40px` scroll.
*   **Text Links Hover Animation**: Underline slides in from left:
    ```css
    .menu a:after {
      content: ""; position: absolute; left: 0; right: 0; bottom: -4px; height: 1px;
      background: var(--accent); transform: scaleX(0); transform-origin: left;
      transition: transform .35s ease;
    }
    .menu a:hover:after { transform: scaleX(1); }
    ```

### 2.2 Mega Menu Dropdown
Hovering `.has-dropdown` (desktop) or toggling class `.open` displays full-width absolute menu `.mega-menu`.
*   **Layout**: 2-column sidebar-panel structure (`height: 600px` / `80vh`).
    *   Left tab list `.mega-tabs` (`width: 250px`, custom scrollbar, `.mega-tab.active` highlight color).
    *   Right panel area `.mega-panels` (flex-grow: 1, sticky subheaders, `.subcategory-products-grid`).
*   **Grid Specs**: `grid-template-columns: repeat(9, 1fr)` with `gap: 12px` for product cards.
*   **CTA Card**: View-all button block:
    ```css
    .view-all-card {
      background: var(--accent); color: var(--pure-bg); border-radius: 4px;
      aspect-ratio: 1/1; font-size: 10px; transition: all 0.2s;
    }
    .view-all-card:hover { background: var(--pure-text); transform: translateY(-3px); }
    ```

### 2.3 Hero Slider
Fullscreen banner containing `.slide` elements absolutely stacked. Active slide gets `.active`.
*   **Effects**: Zoom transition animation and scroll parallax zoom using `--z` and `--ty`.
*   **Ken Burns Zoom**:
    ```css
    .slide.active::before { animation: kenburns 22s ease-out both; }
    @keyframes kenburns {
      0% { transform: scale(1.05) translate3d(0, var(--ty, 0), 0); }
      100% { transform: scale(1.4) translate3d(0, var(--ty, 0), 0); }
    }
    ```
*   **Controls**: Slider line indicator fills `.line .fill` using dynamic CSS variable `--p` (0 to 1), index counter (`#cur` and `#tot`), and Play/Pause toggle `#pp` switching SVGs.

### 2.4 Values Deck ("Why Azoogi")
Card stack with sticky offsets stacking under `<ul id="cards">`:
```css
.card-main { position: sticky; top: 0; padding-top: calc(var(--index) * var(--card-top-offset)); }
@supports (animation-timeline: view()) {
  .card__content {
    animation: linear scale forwards;
    animation-timeline: --cards-element-scrolls-in-body;
    animation-range: exit-crossing var(--start-range) exit-crossing var(--end-range);
  }
  @keyframes scale { to { transform: scale(calc(1.1 - calc(0.1 * var(--reverse-index)))); } }
}
```

### 2.5 Interactive Cityscape
Container `.stage` is `aspect-ratio: 16/9` absolute-positioned hotspot nodes with pulsing rings.
*   **Hotspot Pulsing Effects**:
    ```css
    .hotspot .ring, .hotspot .ring2 {
      position: absolute; inset: -6px; border-radius: 50%;
      border: 2px solid var(--accent); opacity: .7; animation: ripple 2.4s ease-out infinite;
    }
    .hotspot .ring2 { animation-delay: 1.2s; }
    @keyframes ripple {
      0% { transform: scale(.6); opacity: .85; }
      80%, 100% { transform: scale(2.6); opacity: 0; }
    }
    ```
*   **Tooltip Panel (`.tip`)**: Inset panel fading in on hover. Displays thumbnail, code, and description.

### 2.6 Product Catalog Grid
*   **Desktop Layout**: 2-column sidebar split (`grid-template-columns: 160px 1fr`, gap `24px`).
*   **Filter drawer**: Category sidebar `.products-sidebar` slides out on mobile (`left: -300px` to `0` with `.open`).
*   **Attribute pills**: Flex row wrapper `.products-filters` holding scrollable pills.
*   **Card `.compact-card`**: Flex-column layout with hover translation and accent border highlight.

### 2.7 Featured Projects Showcase
*   **Masonry Layout**: 12-column grid (`grid-template-columns: repeat(12, 1fr)`) with span 7 / 5 row templates.
*   **Interactions**: Project card image scale hover transition (`transform: scale(1.07)` over `1.4s`).
*   **Product badges**: Nested list `.project-products` holding rounded thumbnail links.

---

## 3. Micro-Animations & Motion Transitions

*   **Scroll Reveals**: Reveal elements fade and translate up once intersecting:
    ```css
    .reveal { opacity: 0; transform: translateY(40px); transition: opacity 1s ease, transform 1s ease; }
    .reveal.in { opacity: 1; transform: none; }
    ```
*   **Bouncing Scroll Cue**: Animates infinite top-to-bottom scale fill.
*   **Theme Switch Icon Swap**: SVG rotation & scaling:
    ```css
    .theme-btn svg { position: absolute; width: 16px; height: 16px; transition: transform 0.5s, opacity 0.5s; }
    .theme-btn .moon-icon { opacity: 0; transform: rotate(90deg) scale(0); }
    :root[data-theme="light"] .theme-btn .sun-icon { opacity: 0; transform: rotate(-90deg) scale(0); }
    :root[data-theme="light"] .theme-btn .moon-icon { opacity: 1; transform: rotate(0) scale(1); }
    ```

---

## 4. Javascript Interaction Logic

The interactive elements are controlled by native Javascript operations located in [demo.html](file:///Users/prajunadhikary/Desktop/Projects/azoogi/demo.html#L5985-L6224).

### 4.1 Theme Controls & Logo Swap
*   `toggleTheme()` updates custom `data-theme` attribute, saves to `localStorage`, and triggers `updateLogos()`.
*   `updateLogos(theme)` dynamically alternates topbar logo sources to black/white variations.

### 4.2 Hero Slider Auto-loop & Playback
Uses `requestAnimationFrame` loop, tracks slide duration time, and interpolates progress parameter `--p` mapped to active slider lines. Control interruptions stop timer.

### 4.3 Scroll Observers
*   **Reveal Observer**: IntersectionObserver (`threshold: 0.12`) triggers `.reveal` classes once visible.
*   **Stat counter**: IntersectionObserver (`threshold: 0.5`) starts quadratic ease-out increments from `0` to target numbers.

### 4.4 Viewport Parallax Scroll Calculations
Calculates relative scroll parameters (`p = y / vh`) on window scroll, setting `--z` zoom scaling and `--ty` vertical offsets.

### 4.5 Mega Menu Scroll Indicators
*   Monitors `scrollHeight - scrollTop > clientHeight + 2` values for `.mega-tabs` and `.mega-panels`.
*   Toggles `.scroll-indicator-left` and `.scroll-indicator-right` visibility to guide scroll actions.