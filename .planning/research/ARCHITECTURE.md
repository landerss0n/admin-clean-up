# Architecture Patterns for WordPress Premium Plugin Settings UI

**Domain:** WordPress Admin Settings Interface
**Plugin:** Admin Clean Up — Premium Settings Redesign
**Researched:** 2026-01-24
**Confidence:** HIGH

## Executive Summary

This document outlines the architecture for redesigning Admin Clean Up's settings interface from traditional form-tables to a modern card-based layout. The approach maintains PHP rendering (no React/Vue build step) while leveraging WordPress admin UI patterns and modern CSS architecture.

**Key Decision:** Use the WordPress postbox/metabox pattern as the foundation for card-based UI, combined with custom toggle components and BEM CSS architecture. This provides a native WordPress feel while achieving a modern, premium appearance.

## Recommended Architecture

### High-Level Structure

```
Settings Page Layout:
├── Sidebar Navigation (existing, maintained)
└── Content Area
    └── Card Grid
        ├── Section Card (Admin Bar Settings)
        │   ├── Card Header (title + description)
        │   └── Card Body
        │       └── Setting Groups
        │           └── Individual Settings (toggle/radio/input)
        ├── Section Card (Dashboard Settings)
        └── ...
```

### Component Boundaries

| Component | Responsibility | Communicates With |
|-----------|---------------|-------------------|
| **Settings Renderer** | Main orchestrator in class-admin-page.php | All render methods, form submission |
| **Card Container** | Wraps related settings in card UI | Setting groups within cards |
| **Setting Group** | Groups related toggles/inputs | Individual setting components |
| **Toggle Component** | Renders individual toggle switch | Form submission, sanitization |
| **Radio Group** | Renders radio button sets | Form submission, conditional display |
| **Text Input** | Renders text fields with labels | Form submission, validation |
| **Conditional Container** | Shows/hides based on other settings | JavaScript toggle logic |

### Data Flow

```
User Action (Toggle Switch)
    ↓
Browser: Visual feedback (CSS :checked state)
    ↓
Form Submission (POST)
    ↓
PHP: sanitize_options() method
    ↓
WordPress Options API (update_option)
    ↓
Database: wp_options table
    ↓
Page Reload
    ↓
Render: Checked state restored from database
```

## PHP Rendering Architecture

### 1. Component Pattern Structure

**Principle:** Extract reusable rendering functions to eliminate duplication across 9 tabs.

**Implementation:**

```php
// Pattern: Reusable component methods in class-admin-page.php

/**
 * Render a card container
 */
private function render_card( $args ) {
    // $args: title, description, content (callback)
}

/**
 * Render a toggle setting
 */
private function render_toggle_setting( $args ) {
    // $args: name, value, label, description, disabled
}

/**
 * Render a radio group
 */
private function render_radio_group( $args ) {
    // $args: name, value, options, descriptions
}

/**
 * Render a text input
 */
private function render_text_input( $args ) {
    // $args: name, value, label, description, placeholder
}

/**
 * Render a setting group (multiple related settings)
 */
private function render_setting_group( $args ) {
    // $args: title, settings (array of setting configs)
}
```

### 2. Tab Rendering Refactor

**Before (current):** Each tab method outputs inline HTML with form-tables

**After (card-based):** Each tab method calls component rendering functions

```php
// Example: render_adminbar_tab() refactored
private function render_adminbar_tab( $options ) {
    $adminbar_options = isset( $options['adminbar'] ) ? $options['adminbar'] : [];

    // Card 1: Admin Bar Elements
    $this->render_card([
        'title' => __( 'Admin Bar Elements', 'admin-clean-up' ),
        'description' => __( 'Select which elements to remove from the admin bar.', 'admin-clean-up' ),
        'content' => function() use ( $adminbar_options ) {
            $this->render_setting_group([
                'settings' => [
                    [
                        'type' => 'toggle',
                        'name' => 'wp_clean_up_options[adminbar][remove_wp_logo]',
                        'value' => ! empty( $adminbar_options['remove_wp_logo'] ),
                        'label' => __( 'Remove WordPress logo', 'admin-clean-up' ),
                        'description' => __( 'Removes the WordPress logo and its submenu.', 'admin-clean-up' ),
                    ],
                    // ... more settings
                ]
            ]);
        }
    ]);

    // Card 2: Frontend Settings
    $this->render_card([
        'title' => __( 'Frontend Display', 'admin-clean-up' ),
        'content' => function() use ( $adminbar_options ) {
            // ...
        }
    ]);
}
```

### 3. Markup Structure

**Card Container Pattern** (based on WordPress postbox):

```html
<div class="acu-card">
    <div class="acu-card__header">
        <h3 class="acu-card__title">Card Title</h3>
        <p class="acu-card__description">Brief description of settings in this card.</p>
    </div>
    <div class="acu-card__body">
        <!-- Setting groups go here -->
    </div>
</div>
```

**Setting Group Pattern:**

```html
<div class="acu-setting-group">
    <div class="acu-setting">
        <div class="acu-setting__control">
            <label class="acu-toggle">
                <input type="checkbox" name="..." value="1" class="acu-toggle__input">
                <span class="acu-toggle__slider"></span>
            </label>
        </div>
        <div class="acu-setting__content">
            <div class="acu-setting__label">
                <strong>Setting Name</strong>
            </div>
            <p class="acu-setting__description">Description text explaining what this does.</p>
        </div>
    </div>
    <!-- More settings -->
</div>
```

**Toggle Switch Pattern** (CSS-only, no JavaScript):

```html
<label class="acu-toggle">
    <input type="checkbox" name="wp_clean_up_options[tab][setting]" value="1" <?php checked( $value ); ?> class="acu-toggle__input">
    <span class="acu-toggle__slider"></span>
</label>
```

## CSS Architecture

### 1. BEM Naming Convention

**Why BEM:**
- Low specificity, high reusability
- Clear component boundaries
- Easy to understand relationships
- No conflicts with WordPress core styles

**Namespace:** `acu-` (Admin Clean Up prefix to avoid conflicts)

**Structure:**

```
Block:    .acu-card
Element:  .acu-card__header
Modifier: .acu-card--highlighted

Block:    .acu-toggle
Element:  .acu-toggle__slider
Modifier: .acu-toggle--disabled
```

### 2. File Organization

```
assets/css/
├── admin.css (existing, enhanced)
│   ├── Layout (sidebar + content wrapper)
│   ├── Navigation (tabs)
│   ├── Components
│   │   ├── Cards (.acu-card)
│   │   ├── Toggles (.acu-toggle)
│   │   ├── Setting Groups (.acu-setting-group)
│   │   ├── Radio Groups (.acu-radio-group)
│   │   └── Text Inputs (.acu-text-input)
│   ├── Utilities (spacing, colors)
│   └── Responsive (mobile adjustments)
```

**Alternative:** Split into multiple files (optional for larger plugins):

```
assets/css/
├── admin-layout.css (page structure)
├── admin-components.css (cards, toggles, etc.)
└── admin.css (imports both)
```

### 3. Component CSS Patterns

**Card Component:**

```css
/* Block */
.acu-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);
    margin-bottom: 20px;
}

/* Elements */
.acu-card__header {
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f1;
}

.acu-card__title {
    margin: 0 0 4px 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.acu-card__description {
    margin: 0;
    font-size: 13px;
    color: #646970;
}

.acu-card__body {
    padding: 20px;
}

/* Modifier (optional) */
.acu-card--highlighted {
    border-left: 3px solid #2271b1;
}
```

**Toggle Switch Component:**

```css
/* Block */
.acu-toggle {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
}

/* Elements */
.acu-toggle__input {
    opacity: 0;
    width: 0;
    height: 0;
}

.acu-toggle__slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #8c8f94;
    border-radius: 24px;
    transition: 0.3s;
}

.acu-toggle__slider::before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.3s;
}

/* States */
.acu-toggle__input:checked + .acu-toggle__slider {
    background-color: #2271b1; /* WordPress blue */
}

.acu-toggle__input:checked + .acu-toggle__slider::before {
    transform: translateX(20px);
}

.acu-toggle__input:focus + .acu-toggle__slider {
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2271b1;
}

/* Modifier */
.acu-toggle--disabled .acu-toggle__slider {
    opacity: 0.5;
    cursor: not-allowed;
}
```

**Setting Group Component:**

```css
.acu-setting-group {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.acu-setting {
    display: flex;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #f0f0f1;
}

.acu-setting:last-child {
    border-bottom: none;
}

.acu-setting__control {
    flex-shrink: 0;
}

.acu-setting__content {
    flex: 1;
    min-width: 0;
}

.acu-setting__label {
    font-size: 13px;
    color: #1d2327;
    margin-bottom: 4px;
}

.acu-setting__description {
    margin: 0;
    font-size: 12px;
    color: #646970;
    line-height: 1.5;
}
```

### 4. Grid Layout for Cards

```css
.acu-cards-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

/* Optional: 2-column layout for wider screens */
@media (min-width: 1200px) {
    .acu-cards-grid--two-column {
        grid-template-columns: repeat(2, 1fr);
    }
}
```

### 5. Color System

Align with WordPress admin color scheme:

```css
:root {
    /* WordPress admin colors */
    --acu-primary: #2271b1;      /* WordPress blue */
    --acu-primary-dark: #135e96;
    --acu-text: #1d2327;          /* Dark text */
    --acu-text-subtle: #646970;   /* Gray text */
    --acu-border: #c3c4c7;        /* Border gray */
    --acu-border-light: #f0f0f1;  /* Light border */
    --acu-background: #fff;
    --acu-background-subtle: #f6f7f7;
    --acu-success: #00a32a;
    --acu-warning: #dba617;
    --acu-error: #d63638;
}
```

## Form Submission Handling

### Challenge

Custom UI elements (toggles instead of checkboxes) must maintain compatibility with existing form submission.

### Solution

**No changes required.** The toggle pattern uses standard checkbox inputs:

```html
<!-- This IS a checkbox, just styled differently -->
<input type="checkbox" name="wp_clean_up_options[tab][setting]" value="1">
```

### Benefits

1. **Native HTML form behavior** — works with WordPress Settings API
2. **Progressive enhancement** — if CSS fails, falls back to checkbox
3. **Accessibility** — screen readers see real checkboxes
4. **No JavaScript required** — form submits normally

### Conditional Display (JavaScript Enhancement)

For settings with show/hide logic (e.g., role selector for menu hiding):

```javascript
// Small inline script in footer
jQuery(document).ready(function($) {
    // Enable/disable role selector based on checkbox
    $('.acu-toggle__input').on('change', function() {
        var $setting = $(this).closest('.acu-setting');
        var $conditionalFields = $setting.find('.acu-conditional');

        if ($(this).is(':checked')) {
            $conditionalFields.show();
        } else {
            $conditionalFields.hide();
        }
    });
});
```

**Markup for conditional fields:**

```html
<div class="acu-setting">
    <div class="acu-setting__control">
        <label class="acu-toggle">
            <input type="checkbox" name="..." class="acu-toggle__input" data-controls="menu-role-select">
            <span class="acu-toggle__slider"></span>
        </label>
    </div>
    <div class="acu-setting__content">
        <strong>Hide "Settings" menu</strong>

        <!-- Conditional field -->
        <div class="acu-conditional" id="menu-role-select" style="margin-top: 8px;">
            <select name="...">
                <option>All except administrators</option>
                <option>All except administrators & editors</option>
                <option>All users</option>
            </select>
        </div>
    </div>
</div>
```

## WordPress Admin UI Patterns Reference

### Postbox/Metabox Pattern

WordPress uses `.postbox` class for dashboard widgets and meta boxes. This is the native card pattern.

**Key characteristics:**
- White background
- Light border
- Subtle shadow
- Draggable header (optional)
- Collapsible (optional)

**Why not use it directly:**
- Comes with draggable/sortable JavaScript we don't need
- Title markup includes handles and buttons
- Better to create simplified, custom version

**What to borrow:**
- Visual styling (border, shadow, spacing)
- Header/body structure
- WordPress color palette

### WordPress Form Components

Maintain compatibility with WordPress admin styling:

| Component | WordPress Class | Custom Alternative |
|-----------|----------------|-------------------|
| Checkbox | `<input type="checkbox">` | `.acu-toggle` (styled checkbox) |
| Radio | `<input type="radio">` | `.acu-radio` (enhanced radio) |
| Text | `.regular-text` | Keep WordPress class, add wrapper |
| Select | `<select>` | Keep native, style minimally |
| Button | `.button-primary` | Use WordPress classes |

## Patterns to Follow

### Pattern 1: Card-Based Section Grouping

**What:** Group related settings into visual cards rather than long form tables

**When:** Tab has 3+ distinct setting categories

**Example:**

```php
// Dashboard tab: 3 cards
render_card( 'Widget Settings' );     // Welcome panel, At a Glance, etc.
render_card( 'Site Health' );         // Site health specific settings
render_card( 'Advanced Options' );    // Power user settings
```

**Benefits:**
- Visual hierarchy
- Scannable interface
- Logical grouping
- Premium feel

### Pattern 2: Toggle-First Design

**What:** Use toggle switches for all boolean settings (replace checkboxes)

**When:** Any on/off, yes/no, enable/disable setting

**Why:**
- Modern interface standard
- Clear visual state (on = blue, off = gray)
- Larger touch target (mobile friendly)
- Premium plugin expectation

**Implementation:** CSS-only toggle using checkbox hack (no JavaScript)

### Pattern 3: Inline Descriptions

**What:** Place descriptions directly below/beside the control, not in separate column

**When:** All settings (part of card-based design)

**Benefits:**
- Better readability
- More space for descriptions
- Mobile-friendly (no table columns to collapse)

### Pattern 4: Progressive Disclosure

**What:** Hide advanced options until primary setting is enabled

**When:** Settings with sub-options (role selectors, custom text inputs)

**Example:**
```
[Toggle] Hide "Settings" menu
    └── [Dropdown] Hide for: All except administrators  (only shown if toggle ON)
```

**Implementation:** JavaScript show/hide based on toggle state

### Pattern 5: Reusable Component Methods

**What:** Create private rendering methods for each UI component

**When:** Any UI element used 2+ times

**Benefits:**
- DRY principle (Don't Repeat Yourself)
- Consistent markup across all tabs
- Easy to update (change once, applies everywhere)
- Testable in isolation

## Anti-Patterns to Avoid

### Anti-Pattern 1: Mixing Form-Table and Card Layouts

**What goes wrong:** Inconsistent UI with some tabs using old form-table, others using cards

**Why bad:** Confusing user experience, looks unfinished

**Instead:** Migrate all tabs to card layout in one phase

### Anti-Pattern 2: Overly Complex JavaScript

**What goes wrong:** Adding React/Vue for simple toggle states

**Why bad:**
- Build step complexity
- Slower page loads
- Breaks without JavaScript
- Overkill for this use case

**Instead:** Use CSS for styling, minimal jQuery for progressive disclosure

### Anti-Pattern 3: Custom CSS That Fights WordPress

**What goes wrong:** Using `!important` everywhere, overriding WordPress core styles

**Why bad:**
- Breaks in WordPress updates
- Conflicts with admin color schemes
- Hard to maintain

**Instead:** Use WordPress color variables, extend rather than override

### Anti-Pattern 4: Inline Styles in PHP

**What goes wrong:**
```php
echo '<div style="margin: 10px; padding: 5px; border: 1px solid #ccc;">';
```

**Why bad:**
- Can't override with CSS
- Scattered styling logic
- Hard to maintain responsive design

**Instead:** Use CSS classes, keep all styling in CSS files

### Anti-Pattern 5: Duplicated Rendering Code

**What goes wrong:** Copy-pasting toggle HTML across 9 tab methods

**Why bad:**
- Update one toggle, must update in 9 places
- Inconsistencies creep in
- Hard to refactor

**Instead:** Extract to reusable component methods

## Accessibility Considerations

### 1. Semantic HTML

Use proper semantic elements:

```html
<!-- Good -->
<label class="acu-toggle">
    <input type="checkbox" name="..." aria-label="Remove WordPress logo">
    <span class="acu-toggle__slider"></span>
</label>

<!-- Bad -->
<div class="toggle" onclick="...">
    <div class="slider"></div>
</div>
```

### 2. Keyboard Navigation

All interactive elements must be keyboard accessible:

- Toggles: Tab to focus, Space to toggle
- Radio groups: Arrow keys to select
- Text inputs: Tab to focus, type to edit

**Implementation:** Native HTML inputs handle this automatically

### 3. ARIA Labels

Add descriptive labels where visual context isn't enough:

```html
<input type="checkbox" name="..." aria-describedby="setting-description">
<p id="setting-description">This removes the WordPress logo...</p>
```

### 4. Focus Indicators

Ensure visible focus states:

```css
.acu-toggle__input:focus + .acu-toggle__slider {
    box-shadow: 0 0 0 2px #fff, 0 0 0 4px #2271b1;
    outline: none;
}
```

### 5. Color Contrast

Follow WCAG 2.1 AA standards:

- Text on background: 4.5:1 minimum
- Large text (18px+): 3:1 minimum
- UI components: 3:1 minimum

WordPress admin colors already meet these standards.

## Scalability Considerations

### At 9 Tabs (Current)

**Approach:** Component-based rendering with card layout

**Concerns:**
- None — architecture handles this well
- Each tab renders independently
- Shared components ensure consistency

### At 20+ Tabs (Future Growth)

**Approach:** Consider tab grouping or search

**Concerns:**
- Sidebar navigation becomes crowded
- Consider sub-tabs or accordion navigation
- Add search/filter for settings

**Mitigation:**
- Keep component architecture flexible
- Settings data could be config-driven (arrays)
- Consider lazy loading tab content

### At 50+ Settings Per Tab

**Approach:** Multi-column card layout

**Concerns:**
- Single column becomes too long
- Scrolling fatigue

**Mitigation:**
```css
@media (min-width: 1400px) {
    .acu-cards-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
```

## Migration Strategy

### Phase 1: Create Component Methods

1. Add new component rendering methods to `class-admin-page.php`
2. Test each component method in isolation
3. Keep existing render methods unchanged

### Phase 2: Create CSS Architecture

1. Add new CSS to `assets/css/admin.css`
2. Use namespaced classes (`.acu-*`) to avoid conflicts
3. Test in various browsers and WordPress versions

### Phase 3: Migrate One Tab

1. Refactor `render_adminbar_tab()` to use new components
2. Test thoroughly (visual, functional, accessibility)
3. Compare with other tabs to ensure consistency

### Phase 4: Migrate Remaining Tabs

1. Refactor each tab one at a time
2. Test after each migration
3. Remove old form-table code when all tabs migrated

### Phase 5: Polish

1. Add JavaScript enhancements (conditional display)
2. Optimize responsive behavior
3. Accessibility audit
4. Performance check

## Testing Strategy

### Visual Testing

- [ ] All tabs render correctly
- [ ] Cards align properly
- [ ] Toggles display in both states
- [ ] Responsive layout works on mobile
- [ ] Works with all WordPress admin color schemes

### Functional Testing

- [ ] Form submission saves all settings
- [ ] Toggle state persists after save
- [ ] Conditional fields show/hide correctly
- [ ] Validation messages display properly
- [ ] Settings API integration unchanged

### Compatibility Testing

- [ ] Works with latest WordPress version
- [ ] Works with older supported WordPress versions
- [ ] Works in all major browsers (Chrome, Firefox, Safari, Edge)
- [ ] Works with screen readers (NVDA, JAWS, VoiceOver)
- [ ] Works with keyboard-only navigation

## Sources

### WordPress Official Documentation
- [Custom Settings Page – Plugin Handbook](https://developer.wordpress.org/plugins/settings/custom-settings-page/)
- [Custom Meta Boxes – Plugin Handbook](https://developer.wordpress.org/plugins/metadata/custom-meta-boxes/)
- [FormToggle – Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/components/form-toggle/)
- [Component Reference – Block Editor Handbook](https://developer.wordpress.org/block-editor/reference-guides/components/)

### Settings Page Architecture
- [5 Ways to Create a WordPress Plugin Settings Page](https://deliciousbrains.com/create-wordpress-plugin-settings-page/)
- [Settings API Explained | Press Coders](https://presscoders.com/wordpress-settings-api-explained/)
- [How To Design And Style Your WordPress Plugin Admin Panel](https://onextrapixel.com/how-to-design-and-style-your-wordpress-plugin-admin-panel/)

### WordPress Admin UI Components
- [WP-Admin Reference – Reference elements from wp-admin](https://wpadmin.bracketspace.com/)
- [GitHub - bueltge/wordpress-admin-style](https://github.com/bueltge/wordpress-admin-style)
- [Integrating With WordPress' UI: Meta Boxes on Custom Pages | Envato Tuts+](https://code.tutsplus.com/articles/integrating-with-wordpress-ui-meta-boxes-on-custom-pages--wp-26843)

### Toggle Switch Implementations
- [On/Off Toggle Replacement for WordPress Checkboxes – getButterfly](https://getbutterfly.com/on-off-toggle-replacement-for-wordpress-checkboxes/)
- [How To Create a Toggle Switch](https://www.w3schools.com/howto/howto_css_switch.asp)
- [Great CSS Toggle Switch Options You Can Use On Your Site](https://www.sliderrevolution.com/resources/css-toggle-switch/)

### BEM CSS Architecture
- [Master CSS Naming Conventions in 2025: BEM, OOCSS, SMACSS, SUIT CSS, and Beyond | Medium](https://medium.com/@wmukhtar/master-css-naming-conventions-in-2025-bem-oocss-smacss-suit-css-and-beyond-c3afe583c92b)
- [BEM by Example: Best Practices for BEM CSS Naming](https://sparkbox.com/foundry/bem_by_example)
- [BEM — Block Element Modifier](https://getbem.com/)
- [Quick Tip: BEM Naming and WordPress Filters for Navigation | Envato Tuts+](https://webdesign.tutsplus.com/tutorials/quick-tip-bem-naming-and-wordpress-filters-for-navigation--cms-31268)

### PHP Component Patterns
- [How To Develop Reusable Components In WordPress](https://makeitwork.press/reusable-components-wordpress/)
- [GitHub - electro-modules/matisse: A component-based template engine for PHP](https://github.com/electro-modules/matisse)

### Premium Plugin UI Examples
- [ACF | Advanced Custom Fields Plugin for WordPress](https://www.advancedcustomfields.com/)
- [uiXpress - Modern WordPress Admin Theme](https://www.uipress.co/)

## Conclusion

This architecture provides a practical, maintainable approach to modernizing the Admin Clean Up settings UI:

1. **PHP-rendered components** — No build step, no framework complexity
2. **BEM CSS architecture** — Clear naming, low specificity, highly maintainable
3. **WordPress native patterns** — Extends postbox/metabox styling, feels native
4. **Progressive enhancement** — Works without JavaScript, enhanced with it
5. **Accessibility first** — Semantic HTML, keyboard navigation, screen reader support

The card-based UI with toggle switches provides a premium appearance while maintaining simplicity and WordPress compatibility. The component-based rendering approach ensures consistency across all 9 tabs and makes future additions straightforward.
