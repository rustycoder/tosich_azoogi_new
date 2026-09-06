# Product Airtable sync

- Persist Airtable image URLs as-is. Do not download or cache images locally during sync.
- Each Airtable attachment has small/large/full thumbnail URLs. Save one URL per attachment (the main `url`), never the thumbnail sizes.
- Persist products, categories, and attributes with chunked `upsert` (not per-row `firstOrNew`/`save`). MySQL 5.6 is remote on the server, so hundreds of round trips make “Saving 120 products” look stuck.
- Do not recreate `public/assets/img/products`, `public/assets/img/icons`, or `public/assets/img/attribute_icon`.
- `media_url()` already leaves `http://` and `https://` paths unchanged for storefront and dashboard covers.
- Schedule `products:sync` hourly (`Schedule::command('products:sync')->hourly()->withoutOverlapping()`), not every two hours.
