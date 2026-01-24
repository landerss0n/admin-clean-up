# Admin Clean Up

## What This Is

A WordPress plugin that cleans up and simplifies the admin interface by removing unnecessary elements, hiding plugin bloat, and giving site owners control over what appears in their backend. Built for developers and agencies who manage WordPress sites for clients.

## Core Value

The settings page must feel premium and be immediately understandable — every option clear at a glance, with a modern UI that doesn't look like default WordPress.

## Requirements

### Validated

- ✓ Admin bar element removal (logo, site menu, new content, search, howdy) — existing
- ✓ Comment disabling (complete removal from all surfaces) — existing
- ✓ Dashboard widget removal (welcome, at a glance, activity, quick draft, events, site health) — existing
- ✓ Admin menu hiding with role-based control — existing
- ✓ Footer text/version customization — existing
- ✓ Admin notices control (update notices, all notices, screen options, help tab) — existing
- ✓ Media filename cleaning (special chars, spaces, lowercase) — existing
- ✓ PixelYourSite notice hiding (promotional elements, nag screens, upsells) — existing
- ✓ WordPress update control (core, plugin, theme auto-updates, emails, nags) — existing
- ✓ Site Health disable — existing
- ✓ Sidebar navigation layout with responsive mobile support — existing

### Active

- [ ] Premium UI redesign — modern, polished settings page that feels premium (toggles, cards, visual hierarchy, not boring native WordPress)
- [ ] Code optimization — resilient to WordPress updates, proper use of stable APIs, defensive coding
- [ ] PYS Free-only condition — PixelYourSite notice hiding only activates when Free version is active (not Pro)
- [ ] English base language with proper translation support (remove hardcoded Swedish, update .pot/.po/.mo)
- [ ] Fix deep option merging — `wp_parse_args` doesn't deep-merge nested arrays, causing defaults to not apply correctly
- [ ] Options key constant — replace hardcoded `'wp_clean_up_options'` strings with a class constant
- [ ] Activation hook sync — activation defaults are outdated compared to actual options structure

### Out of Scope

- Adding new cleanup features beyond what exists — focus is UI/code quality
- Multisite support — not in scope for this version
- Plugin distribution (wordpress.org submission) — internal/client use
- Automated testing suite — not requested

## Context

- WordPress plugin, PHP-only backend (no JS frameworks)
- Currently uses standard `form-table` pattern with checkboxes — functional but visually boring
- Has sidebar navigation already (not top tabs) — layout is good, content rendering needs the premium feel
- ACF settings pages are the design inspiration: clean, modern, premium without being flashy
- PixelYourSite Free path: `pixelyoursite/facebook-pixel-master.php`
- PixelYourSite Pro path: `pixelyoursite-pro/pixelyoursite-pro.php` (Pro doesn't show nag screens)
- Plugin is by Digiwise (digiwise.se)
- No external dependencies — pure WordPress APIs
- Current CSS already has decent structure, needs evolution not replacement

## Test Site

After any testable changes, sync to the Local Sites test environment:
```
rsync -av --delete "/Users/lucas/Sites/admin-clean-up/" "/Users/lucas/Local Sites/testar-plugins/app/public/wp-content/plugins/admin-clean-up/" --exclude='.planning' --exclude='.git'
```

## Constraints

- **Tech stack**: PHP + CSS only, no JavaScript frameworks, no build tools — must work as a drop-in WordPress plugin
- **WordPress API**: Use only stable, documented WordPress hooks and functions (no internal/private APIs)
- **Compatibility**: WordPress 6.0+ and PHP 7.4+
- **No external deps**: No Composer, no npm, no third-party libraries

## Key Decisions

| Decision | Rationale | Outcome |
|----------|-----------|---------|
| Premium UI with toggles/cards over native form-tables | User wants modern, non-boring feel like ACF | — Pending |
| Clean neutral colors + bold/premium layout | Grays/whites/subtle accents, darker sidebar, strong contrast, distinct sections | — Pending |
| PYS notice hiding only for Free version | Pro version doesn't have the nag screens | — Pending |
| English as base language | International best practice, Swedish as translation | — Pending |
| Pure CSS for UI (no JS framework) | Keep plugin lightweight, no build step | — Pending |

---
*Last updated: 2026-01-24 after initialization*
