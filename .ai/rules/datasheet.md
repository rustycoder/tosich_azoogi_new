# Custom product datasheet

- The branded A4 datasheet is generated only from **Download Custom Datasheet** on the product page. Disable that button until a configuration option is selected. The left **Datasheet** link is the manufacturer/Airtable PDF when one exists.
- Ask for project name and client name before generating. Persist every export in `product_datasheet_exports` for analytics.
- The prompt is a centered `<dialog>` (`margin: auto`). The global `* { margin: 0 }` reset will pin it to the top-left without that.
- Do not add a PDF Composer package. Render print-ready A4 HTML (`datasheet.css`) and let the browser Save as PDF.
- Snapshot specs, images, and selected configuration on export so the sheet still renders if the live product changes.
