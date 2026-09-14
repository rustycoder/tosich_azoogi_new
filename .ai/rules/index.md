# Rules index

Map file globs to the rule files in this directory. Read every matching file before editing those paths.

| Glob | Rule |
| --- | --- |
| `public/assets/css/**/*.css` | [css-assets.md](css-assets.md), [typography.md](typography.md), [section-spacing.md](section-spacing.md) |
| `public/assets/css/dashboard.css`, `resources/views/layouts/dashboard.blade.php`, `resources/views/dashboard/**/*.blade.php` | [dashboard.md](dashboard.md) |
| `resources/views/**/*.blade.php` | [css-assets.md](css-assets.md) |
| `resources/views/pages/products.blade.php` | [products-filter.md](products-filter.md) |
| `app/helpers.php` | [css-assets.md](css-assets.md) |
| `app/Services/ProductSyncService.php`, `routes/console.php` | [product-sync.md](product-sync.md) |
| `app/Http/Controllers/Site/ProductController.php`, `resources/views/pages/products.blade.php`, `public/assets/css/products.css` | [product-page.md](product-page.md) |
| `app/Services/LedCalculatorService.php`, `public/assets/js/led_calculator.js` | [led-calculator.md](led-calculator.md) |
| `app/Services/ProductDatasheetService.php`, `resources/views/pages/product-datasheet.blade.php`, `public/assets/css/datasheet.css` | [datasheet.md](datasheet.md) |
| `app/Services/VisitorOriginService.php`, `app/Services/PageVisitService.php`, `app/Services/EnquiryService.php`, `app/Services/ProductDatasheetService.php`, `app/Services/DashboardMetricsService.php`, `resources/views/dashboard/home.blade.php` | [visitor-origin.md](visitor-origin.md) |
