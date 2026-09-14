# Public products page

- `/products` with no `category` query is a parent-category gallery. Use the same cards as the home range marquee (`ProductCatalog::parentCategories()`). Do not render search, tech filters, or product cards there.
- The filtered catalogue (sidebar, search, product grid) only renders when `?category=` is present. Gallery cards, the mega menu, and the home marquee all link to `/products?category={name}`.
