# Section spacing

Adjacent sections add `padding-bottom` of the one above to `padding-top` of the one below. Keep **both** paddings, and **halve each**, so the meeting gap is the intended total.

Shared tokens live on `:root` in `style_demo.css`:

```css
--section-y: clamp(12px, 1.5vw, 18px);
--hero-min: min(52vh, 480px); /* 58vh / 420px max under 640px */
--hero-pad-y-top: 180px;      /* 160px under 640px — navbar clearance + air */
--hero-pad-y-bottom: 40px;    /* 32px under 640px */
```

```css
/* BAD — 20px + 20px = 40px between sections */
.cb-band { padding: 20px 0; }

/* BAD — dropped bottom padding; content sits on the band edge */
.cb-band { padding: 20px 0 0; }

/* GOOD — token on every stacked band / home section */
.cb-band { padding: var(--section-y) 0; }
.cb-cta  { padding: var(--section-y) 0 calc(var(--section-y) + 16px); }
```

- Use `padding: var(--section-y) 0` on stacked `*-band` / home section blocks, not `padding: X 0 0`.
- Do not override that padding in mobile media queries — the token already scales.
- Keep extra padding-bottom on the page CTA (or last block) so the footer does not sit on the content: `calc(var(--section-y) + 16px)`.
- Home `.hero` is `height: 100vh` / `min-height: 100vh`. Every other public page hero matches AI Lighting: `min-height: var(--hero-min)`, copy/section padding `var(--hero-pad-y-top)` / `var(--hero-pad-y-bottom)`. Image heroes put that padding on `*-hero-copy` and keep `align-items: end`. Text heroes (Projects, Solutions, Casambi, Silvair, DALI Centre, LED Calculator) put padding on the hero itself, use `align-items: start` so copy always starts at the same offset under the nav, and do not also pad `*-main`.
- Audience is the exception for `--hero-min` and `--hero-pad-y-bottom`: electrician / wholesaler (and other title-only audience pages) size the hero to the copy. Top pad matches Projects (`var(--hero-pad-y-top)`). Do not add hero bottom pad, `.card-in` top pad, or `#cards` top pad/margin — those stacked into a tall empty band under the title. Keep footer clearance on `.audience-cards-wrap`. Drop the title’s bottom margin when it is the only hero child.
- Project detail is the exception for `50vh` (cover-as-background). Madrix is the exception for `80vh` (slideshow). Copy still uses the shared hero pad tokens. Description and gallery sit in sections after the project-detail hero.
- Navbar/hero clearance is `--hero-pad-y-top`, not a second `120px` / `140px` on the main wrapper.
- Card, table, and control padding is unrelated — leave it alone.
- Sticky `#cards` stack on home values and AI Lighting insights uses `--card-height: min(48svh, 400px)` (`min(56svh, 400px)` under 720px). Do not raise it on Home or AI Lighting — tall cards float short copy in empty space. Align `.card__content>div` with padding and `align-content: start`, not `place-self: center` + `width: 80%`.
- Audience keeps its own `--card-height: min(72svh, 600px)` (`min(70svh, 520px)` under 720px) on `.audience-page`. Longer Why Azoogi copy must not inherit the compact Home/AI height.
