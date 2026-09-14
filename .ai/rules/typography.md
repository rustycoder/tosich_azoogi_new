# Public typography

Use the shared tokens on `:root` in `style_demo.css`. Do not invent a second heading or caption scale. Do not hardcode `font-size` in `px` or `clamp()` for content type.

```css
font-family: var(--font-sans);          /* Google Sans Flex, self-hosted */
font-family: var(--font-outline);       /* Proba Pro, self-hosted — outline accents only */
font-size: var(--fs-h2);                /* page heroes (Casambi / Silvair / DALI titles too) */
font-size: var(--fs-h2-section);        /* in-page section titles; product H1 */
font-size: var(--fs-h3);                /* subheads */
font-size: var(--fs-lead);              /* intro / hero paragraphs */
font-size: var(--fs-body);              /* running copy — also set on html, body */
font-size: var(--fs-kicker);            /* labels, nav, buttons, spec tables */
font-size: var(--fs-meta);              /* card blurbs, counts */
font-size: var(--fs-caption);           /* overlay tags, micro labels */
font-size: var(--fs-card-title);        /* overlay titles on image cards */
font-size: var(--fs-card-title-sm);     /* smaller card titles */
```

- Host `Google Sans Flex` and `Proba Pro` from `public/assets/fonts`. Do not load fonts from Google Fonts or another CDN.
- Public pages use `--font-sans` for body and headings. Write `var(--font-sans)`, not the family name.
- Hero accent words and About numbering use `--font-outline` with `-webkit-text-stroke` (transparent fill). Do not fill those in solid green.
- Do not use Cormorant, Inter, `--font-serif`, or another display face on the site.
- Image-card mosaics (home projects, related projects) keep their grid and **scale** cards and caption type. Do not stack those cards to one column.
- Content columns (forms, project info, article body) may still stack on small screens.
- Overlay captions stay small. Do not give them body or heading sizes.
- Form controls use `max(16px, var(--fs-body))` so iOS does not zoom on focus.
- Allowed hardcoded sizes: logo wordmark, close/icon buttons, badge counts, DIM overlay, decorative `.about-why-ghost` numbers.
