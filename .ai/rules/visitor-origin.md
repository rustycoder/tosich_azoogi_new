# Visitor origin

- Store `ip_address` (max 45), ISO-2 `country`, and `user_agent` (max 191) on public enquiries and datasheet exports.
- Prefer CDN headers (`CF-Connecting-IP`, `CF-IPCountry`, CloudFront, and similar). Skip Cloudflare unknowns (`XX`, `T1`).
- For public IPs with no country header, look up `https://ipwho.is/{ip}` with a short timeout. Never look up private or reserved IPs. Do not add a GeoIP Composer package.
- Show country, IP, and device on enquiry dialog facts (always, with — when unknown). On datasheet exports, show those fields in the info modal, not as an Origin column.
- Dashboard home is an **Overview**: date under the title, then **Engagement**, **Enquiries**, and **Datasheets** cards, then pending Quote / Product / Contact lists. Origin cards use a Country/Device switch and a horizontal percentage bar chart of the top 10 groups (no total). Engagement is a yearly area chart of monthly enquiry and datasheet-export totals for the current year. Enquiry counts only include types the signed-in user can manage. Unknown country or device groups as Unknown. Home cards share the same header: title on the left, actions or count on the right. Do not add Projects, Products, Pages, Sections, or Staff shortcut cards on home.
