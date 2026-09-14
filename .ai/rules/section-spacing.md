# Section spacing

Adjacent sections add `padding-bottom` of the one above to `padding-top` of the one below. Keep **both** paddings, and **halve each**, so the meeting gap is the intended total.

```css
/* BAD — 20px + 20px = 40px between sections */
.cb-band { padding: 20px 0; }

/* BAD — dropped bottom padding; content sits on the band edge */
.cb-band { padding: 20px 0 0; }

/* GOOD — 10px + 10px = 20px between sections */
.cb-band { padding: 10px 0; }
.cb-cta  { padding: 10px 0 24px; }
```

- Use equal halved top and bottom on stacked `*-band` / home section blocks (`padding: X 0`, not `padding: X 0 0`).
- The visible gap is top + bottom. If 20px total is needed, each side is 10px.
- Keep extra padding-bottom on the page CTA (or last block) so the footer does not sit on the content.
- Navbar/hero clearance (`120px` / `140px` page padding-top) is unrelated — leave it.
- Card, table, and control padding is unrelated — leave it alone.
