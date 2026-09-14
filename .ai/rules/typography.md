# Public typography

Use the shared tokens on `:root` in `style_demo.css`. Do not invent a second heading or caption scale.

```css
font-family: var(--font-sans);          /* Google Sans Flex, self-hosted */
font-family: var(--font-outline);       /* Proba Pro, self-hosted — outline accents only */
font-size: var(--fs-h2);                /* page / section display */
font-size: var(--fs-h2-section);        /* in-page section titles */
font-size: var(--fs-lead);              /* intro paragraphs */
font-size: var(--fs-caption);           /* overlay tags on image cards */
font-size: var(--fs-card-title);        /* overlay titles on image cards */
```

- Host `Google Sans Flex` and `Proba Pro` from `public/assets/fonts`. Do not load fonts from Google Fonts or another CDN.
- Public pages use `--font-sans` for body and headings.
- Hero accent words and About numbering use `--font-outline` with `-webkit-text-stroke` (transparent fill). Do not fill those in solid green.
- Do not use Cormorant, Inter, or another display face on the site.
- Image-card mosaics (home projects, related projects) keep their grid and **scale** cards and caption type. Do not stack those cards to one column.
- Content columns (forms, project info, article body) may still stack on small screens.
- Overlay captions stay small. Do not give them body or heading sizes.
