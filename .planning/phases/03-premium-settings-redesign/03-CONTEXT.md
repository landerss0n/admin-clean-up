# Phase 3: Premium Settings Redesign - Context

**Gathered:** 2026-01-24
**Status:** Ready for planning

<domain>
## Phase Boundary

Convert all 9 settings tabs from form-table markup to card/toggle layout with premium dark UI. The CSS design system and PHP component library (Phase 2) are built — this phase applies them across every tab and adds visual polish. No new settings or capabilities — just the UI transformation.

</domain>

<decisions>
## Implementation Decisions

### Visual direction
- Dark & sleek theme — inspired by Stripe Dashboard / Linear
- Self-contained dark "island" inside WP admin (not full page takeover)
- Premium feel that looks custom for this plugin, not WordPress-native
- Clean neutrals with subtle accents — grays, dark tones, controlled contrast

### Navigation
- Dark vertical sidebar on the left with tab names
- Active tab highlighted — stays visible while scrolling content
- Sidebar feels integrated with the dark theme (not a separate element)

### Card layout
- Related settings grouped into distinct cards with headers
- Clear visual hierarchy — section cards visually grouped, toggle labels readable
- Descriptions subordinate, status obvious at a glance

### Plugin Cleanup tab (PYS)
- Tab is a general "Plugin Cleanup" concept — PYS is the first entry
- Structure should accommodate additional plugin cleanup entries in the future
- Tab vanishes completely when PYS Free isn't active (no disabled/grayed state)
- All other tabs follow the standard card/toggle pattern — no special cases

### Claude's Discretion
- Exact color palette (research premium dark UI palettes)
- Typography choices (font sizes, weights, spacing)
- Hover states, transitions, micro-interactions
- Card border treatments, shadows, corner radii
- Toggle switch accent color
- Responsive behavior at 782px breakpoint
- Keyboard focus indicator styling
- Save button placement and styling

</decisions>

<specifics>
## Specific Ideas

- Should feel like Stripe Dashboard or Linear — dark, clean, premium
- The settings area should feel like "its own app" inside WordPress admin
- Not too much WP-native appearance — the plugin should have its own visual identity
- Plugin Cleanup tab designed for extensibility (more plugins can be added later)

</specifics>

<deferred>
## Deferred Ideas

None — discussion stayed within phase scope

</deferred>

---

*Phase: 03-premium-settings-redesign*
*Context gathered: 2026-01-24*
