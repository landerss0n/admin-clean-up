# Roadmap: Admin Clean Up

## Overview

Transform Admin Clean Up from a functional-but-boring settings page into a premium, modern WordPress plugin UI. The work proceeds in three phases: first fix the underlying code quality issues (deep merge bugs, hardcoded strings, activation sync, PYS condition, internationalization), then build a resilient CSS/PHP component architecture (BEM, custom properties, reusable render methods), and finally apply the premium redesign across all 9 settings tabs with visual polish.

## Phases

**Phase Numbering:**
- Integer phases (1, 2, 3): Planned milestone work
- Decimal phases (2.1, 2.2): Urgent insertions (marked with INSERTED)

Decimal phases appear between their surrounding integers in numeric order.

- [x] **Phase 1: Code Quality Foundation** - Fix data handling bugs, add constants, PYS condition, and i18n support
- [ ] **Phase 2: UI Architecture** - Build CSS design system and PHP component render methods
- [ ] **Phase 3: Premium Settings Redesign** - Convert all 9 tabs to card/toggle layout with polish

## Phase Details

### Phase 1: Code Quality Foundation
**Goal**: Options save and load correctly, PYS behaves properly, and the plugin speaks English with translation support
**Depends on**: Nothing (first phase)
**Requirements**: CODE-02, CODE-03, CODE-04, PYS-01, I18N-01
**Success Criteria** (what must be TRUE):
  1. Adding a new option key to any nested settings array applies the default correctly on next page load (deep merge works)
  2. The string 'wp_clean_up_options' appears nowhere in the codebase except the constant definition
  3. A fresh activation on a clean site produces an options array matching the current full structure (all keys present)
  4. With PixelYourSite Pro active (and Free inactive), the PYS tab functionality is completely dormant -- no hooks fire, no notices are hidden
  5. All user-facing strings render in English by default, and switching to sv_SE locale shows Swedish translations
**Plans**: 2 plans

Plans:
- [x] 01-01-PLAN.md -- Deep merge, OPTION_KEY constant, and activation sync
- [x] 01-02-PLAN.md -- PYS Free-only condition and internationalization

### Phase 2: UI Architecture
**Goal**: A complete, tested CSS design system and PHP component library exist, ready for tab migration
**Depends on**: Phase 1
**Requirements**: CODE-01
**Success Criteria** (what must be TRUE):
  1. A single demo/test rendering (or first tab converted as proof) shows a card container with header, toggle switches, and setting groups -- all styled via BEM classes with `acu-` prefix
  2. Changing one CSS custom property value (e.g., `--acu-color-primary`) updates the accent color across all components consistently
  3. Toggle switches visually reflect checkbox state without any JavaScript -- toggling works via CSS `:checked` only
  4. Each component (card, toggle, setting group) is rendered by a single PHP method call with parameters -- no duplicated HTML blocks
  5. Components use WordPress admin color scheme variables (`--wp-admin-theme-color`) so they adapt to user-selected color schemes
**Plans**: 3 plans

Plans:
- [x] 02-01-PLAN.md -- CSS design system (custom properties, card, toggle, setting, setting-group BEM components)
- [x] 02-02-PLAN.md -- PHP component render methods (WP_Clean_Up_Components class with hidden input pattern)
- [x] 02-03-PLAN.md -- Gap closure: Convert Comments tab to component-based rendering (proof of concept)

### Phase 3: Premium Settings Redesign
**Goal**: Every settings tab uses the premium card/toggle UI, feels polished, and works reliably
**Depends on**: Phase 2
**Requirements**: UI-01
**Success Criteria** (what must be TRUE):
  1. All 9 settings tabs display as card-based layouts with toggle switches -- no form-table markup remains in rendered settings output
  2. Saving settings on any tab preserves all values correctly (toggling off sends 0 via hidden input, no data loss)
  3. The settings page is fully usable on mobile (782px breakpoint) with 44px minimum touch targets on interactive elements
  4. Visual hierarchy is immediately clear: section cards are visually grouped, toggle labels are readable, descriptions are subordinate, and status is obvious at a glance
  5. Keyboard navigation works through all toggles and controls (Tab to move, Space to toggle) with visible focus indicators
**Plans**: 3 plans

Plans:
- [ ] 03-01-PLAN.md -- Dark theme CSS + page wrapper + new components (radio, text input, select)
- [ ] 03-02-PLAN.md -- Tab migration: Admin Bar, Dashboard, Menus, Footer
- [ ] 03-03-PLAN.md -- Tab migration: Notices, Media, Plugin Cleanup, Updates + visual verification

## Progress

**Execution Order:**
Phases execute in numeric order: 1 --> 2 --> 3

| Phase | Plans Complete | Status | Completed |
|-------|----------------|--------|-----------|
| 1. Code Quality Foundation | 2/2 | Complete | 2026-01-24 |
| 2. UI Architecture | 3/3 | Complete | 2026-01-24 |
| 3. Premium Settings Redesign | 0/3 | Not started | - |
