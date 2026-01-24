# Requirements: Admin Clean Up

## Active Requirements (v1)

### Code Quality

| ID | Requirement | Description |
|----|-------------|-------------|
| CODE-01 | Code optimization | Resilient to WordPress updates, proper use of stable APIs, defensive coding, BEM CSS, custom properties |
| CODE-02 | Fix deep option merging | `wp_parse_args` doesn't deep-merge nested arrays, causing defaults to not apply correctly |
| CODE-03 | Options key constant | Replace hardcoded `'wp_clean_up_options'` strings with a class constant |
| CODE-04 | Activation hook sync | Activation defaults are outdated compared to actual options structure |

### Feature Fixes

| ID | Requirement | Description |
|----|-------------|-------------|
| PYS-01 | PYS Free-only condition | PixelYourSite notice hiding only activates when Free version is active (not Pro) |
| I18N-01 | English base language | Remove hardcoded Swedish, proper translation support, update .pot/.po/.mo files |

### UI

| ID | Requirement | Description |
|----|-------------|-------------|
| UI-01 | Premium UI redesign | Modern, polished settings page with toggles, cards, visual hierarchy -- not boring native WordPress |

## Validated (Existing Features -- Not In Scope)

- Admin bar element removal
- Comment disabling
- Dashboard widget removal
- Admin menu hiding with role-based control
- Footer text/version customization
- Admin notices control
- Media filename cleaning
- PixelYourSite notice hiding
- WordPress update control
- Site Health disable
- Sidebar navigation layout

## Out of Scope

- Adding new cleanup features beyond what exists
- Multisite support
- Plugin distribution (wordpress.org submission)
- Automated testing suite
- Settings search
- Export/import configuration
- Onboarding wizard

## Traceability

| Requirement | Phase | Status |
|-------------|-------|--------|
| CODE-01 | Phase 2 | Pending |
| CODE-02 | Phase 1 | Pending |
| CODE-03 | Phase 1 | Pending |
| CODE-04 | Phase 1 | Pending |
| PYS-01 | Phase 1 | Pending |
| I18N-01 | Phase 1 | Pending |
| UI-01 | Phase 3 | Pending |

**Coverage:** 7/7 requirements mapped.
