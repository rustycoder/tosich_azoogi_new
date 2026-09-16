# Public typography

Use the shared tokens on `:root` in `style_demo.css`. Do not invent a second heading or caption scale. Do not hardcode `font-size` in `px` or `clamp()` for content type.

```css
font-family: var(--font-sans);          /* Google Sans Flex, self-hosted */
font-family: var(--font-outline);       /* same family as --font-sans — outline accents only */
font-size: var(--fs-h2);                /* clamp 36–64px — page heroes only (slide-title, *-title, *-hero h1) */
font-size: var(--fs-h2-section);        /* clamp 24–28px — .h2 and in-page section titles; product H1 */
font-size: var(--fs-h3);                /* clamp 22–28px — subheads */
font-size: var(--fs-card-title);        /* clamp 11–26px — overlay titles on image cards */
font-size: var(--fs-lead);              /* clamp 15–18px — intro / hero paragraphs */
font-size: var(--fs-card-title-sm);     /* clamp 13–22px — smaller overlay titles */
font-size: var(--fs-body);              /* clamp 14–17px — running copy — also set on html, body */
font-size: var(--fs-meta);              /* clamp 12–14px — card blurbs, counts */
font-size: var(--fs-kicker);            /* clamp 10–13px — labels, nav, buttons, spec tables */
font-size: var(--fs-caption);           /* clamp 7–11px — overlay tags, micro labels */
```

- Host `Google Sans Flex` from `public/assets/fonts`. Do not load fonts from Google Fonts or another CDN. Do not add a second public typeface for outline accents.
- Type tokens use `clamp()` so they scale with the viewport. `--fs-h2` may reach 64px and is for page heroes and home stat numbers (`.stat .num`). `.h2` and every other content token use `--fs-h2-section` or smaller (max 28px).
- Public pages use `--font-sans` for body and headings. Write `var(--font-sans)`, not the family name.
- Hero accent words and About numbering use `--font-outline` (`var(--font-sans)`) with `-webkit-text-stroke` (transparent fill). Stroke uses `var(--accent)` only — do not use `#8cc63f` or another lime. One tight `drop-shadow` (about `0.1em`, mid-opacity accent) is allowed. Do not blink, flash, or animate outline accents. Do not stack a large glow.
- Do not use Cormorant, Inter, `--font-serif`, or another display face on the site.
- Image-card mosaics (home projects, related projects) keep their grid and **scale** cards and caption type. Do not stack those cards to one column.
- Content columns (forms, project info, article body) may still stack on small screens.
- Overlay captions stay small. Do not give them body or heading sizes.
- Product card titles (home marquee, products gallery, catalogue cards) use `--fs-meta`. Do not use `--fs-card-title` or `--fs-card-title-sm` there — those are overlay titles only.
- Form controls use `max(16px, var(--fs-body))` so iOS does not zoom on focus.
- Allowed hardcoded sizes: logo wordmark, close/icon buttons, badge counts, DIM overlay, decorative `.about-why-ghost` numbers.
