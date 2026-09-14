# Products listing filters

Many profiles share the same dimension title (e.g. `50mm (W) x 75mm (H)`). The listing catalog must key and merge items by Airtable product id, never by display name, or Trimless paths leak onto Suspended SKUs (and the reverse).

Category clicks are exclusive. A product matches when its assigned `categories` or a `category_path` node equals the selected name. Do not treat `modelName` as a category — that field is the product title.
