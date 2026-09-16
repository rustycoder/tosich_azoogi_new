# Outline accents

Hero and heading outline phrases are CMS fields, not Blade literals.

- Pair every `accent_html()` call with a `{key}_accent` field (e.g. `hero.title` + `hero.title_accent`).
- Seed the current outlined substring so public HTML stays the same until an editor changes it.
- Empty accent renders the heading with no `<span>`. Newlines in the heading become `<br>`.
- Audience already follows this (`Title accent`, `Card accent`). Match that pattern on other pages, including multiline heroes (About, Data Centre, AI Lighting caps) and the LED Calculator.
- Do not hardcode outline `<span>`s in Blade. Products stay catalogue-driven and are not page-meta.
