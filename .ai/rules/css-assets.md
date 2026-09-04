# CSS assets

Keep one stable filename per stylesheet. Never put a version in the filename.

```blade
{{-- BAD --}}
<link rel="stylesheet" href="{{ asset('assets/css/solutions.v-1.7.css') }}">

{{-- GOOD --}}
<link rel="stylesheet" href="{{ versioned_asset('assets/css/solutions.css') }}">
```

- Name files `solutions.css`, `casambi.css`, `style_demo.css` — not `solutions.v-1.7.css`.
- Link with `versioned_asset()` so the URL is `solutions.css?v={filemtime}`. Saving the file is enough to bust the cache.
- Do not keep old copies (`solutions.v-1.5.css`, `solutions.v-1.6.css`). Edit the live file and delete unused versions.
- Do not bump `ASSET_VERSION` for CSS changes.
