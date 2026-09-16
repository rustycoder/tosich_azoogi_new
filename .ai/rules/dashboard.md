# Dashboard chrome

The dashboard layout loads `style_demo.css` and shares the public `theme` localStorage key (`data-theme`).

- Default is light: the layout HTML is `data-theme="light"`. Saved `localStorage.theme === 'dark'` is the only override. `:root` still holds dark tokens; `:root[data-theme="light"]` is the light surface map.
- Put the theme toggle in the sidebar user actions as `#theme-toggle`, between Settings and Log out, as a `dash-user-btn` icon button (not the public switch). Reuse `site-theme.js`. Bootstrap `data-theme` in the dashboard layout head the same way as the public layout.
- Do not let admin `.btn` / `.btn.primary` use `--pure-bg` or `--pure-text` for label color. Ghost buttons use `color: var(--dash-ink)`; primary buttons keep `color: #fff`.
- Paint admin panels with `--dash-bg`, `--dash-card`, `--dash-fill`, `--dash-line`, and `--dash-ink`. Do not hardcode `#fff` card backgrounds.
- The sidebar stays dark in both themes. Dark toolbars (`.dash-visual-tools`) keep light button text.
- Dashboard scrollbars match the product-filter chrome: 4px thumb, `var(--dash-line)`, transparent track. Do not use the OS default thick bar on `.dash-main` or nested panes.
