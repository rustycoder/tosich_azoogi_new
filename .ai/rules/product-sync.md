# Product Airtable sync

- Persist Airtable image URLs as-is. Do not download or cache images locally during sync.
- Do not recreate `public/assets/img/products`, `public/assets/img/icons`, or `public/assets/img/attribute_icon`.
- `media_url()` already leaves `http://` and `https://` paths unchanged for storefront and dashboard covers.
- Schedule `products:sync` hourly (`Schedule::command('products:sync')->hourly()->withoutOverlapping()`), not every two hours.
