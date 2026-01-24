# Phase 2: UI Architecture - Research

**Researched:** 2026-01-24
**Domain:** CSS design systems, BEM methodology, CSS custom properties, WordPress admin integration
**Confidence:** HIGH

## Summary

This phase builds a CSS design system and PHP component library for rendering premium UI elements (cards, toggles, setting groups) using BEM methodology and CSS custom properties. The architecture must integrate with WordPress admin color schemes via `--wp-admin-theme-color` variables and support the upcoming WordPress 7.0 design token system (April 2026).

The standard approach is BEM with `acu-` namespace prefix for component classes, CSS custom properties for themeable values stored in `:root`, and CSS-only toggle switches using the `:checked` pseudo-class without JavaScript. PHP components follow a render method pattern where each component is a single method call with parameters, avoiding duplicated HTML blocks.

Key architectural decision: CSS custom properties from the start hedge against WordPress 7.0's design token introduction while maintaining compatibility with current WordPress admin color schemes.

**Primary recommendation:** Use BEM with single-underscore element notation (avoiding grandchild selectors), CSS custom properties for all themeable values, WordPress admin color scheme variables for accent colors, and PHP render methods with parameter arrays for component flexibility.

## Standard Stack

### Core
| Library | Version | Purpose | Why Standard |
|---------|---------|---------|--------------|
| BEM CSS | Methodology | CSS naming convention and architecture | Industry-standard for component-based CSS, prevents specificity wars, self-documenting class names |
| CSS Custom Properties | Native CSS | Design tokens and themeable values | Runtime theming, WordPress 7.0 compatibility, no build step required |
| WordPress Admin Color Schemes | Core API | User-selected admin colors | Native WordPress integration, accessibility compliance |

### Supporting
| Library | Version | Purpose | When to Use |
|---------|---------|---------|-------------|
| CSS `:checked` pseudo-class | Native CSS | Toggle switch state styling | CSS-only interactive components without JavaScript dependency |
| PHP render methods | Native PHP | Component templating | Reusable UI components with parameter-based configuration |

### Alternatives Considered
| Instead of | Could Use | Tradeoff |
|------------|-----------|----------|
| BEM | Tailwind CSS | Tailwind requires build step and npm dependencies; BEM works with plain CSS and WordPress conventions |
| CSS Custom Properties | Sass variables | Sass variables compile-time only (can't adapt to user color schemes); CSS vars enable runtime theming |
| PHP render methods | Template files | Template files require file I/O and path management; methods keep components colocated with logic |

**Installation:**
No installation required - all techniques use native CSS, HTML, and PHP.

## Architecture Patterns

### Recommended Project Structure
```
assets/
├── css/
│   ├── admin.css          # Main admin stylesheet
│   └── components/        # Component-specific CSS (optional split)
includes/
├── class-admin-page.php   # Settings page with render methods
└── components/            # Future: extracted component classes
```

### Pattern 1: BEM Naming with Namespace Prefix
**What:** Block-Element-Modifier methodology with `acu-` prefix to avoid conflicts
**When to use:** All CSS classes for custom components
**Example:**
```css
/* Block: Card container */
.acu-card { }

/* Element: Card header (NOT .acu-card__header__title) */
.acu-card__header { }
.acu-card__title { }
.acu-card__body { }

/* Modifier: Card state/variant */
.acu-card--highlighted { }
.acu-card--collapsed { }
```

**Key rule:** "The double-underscore pattern should appear only once in a selector name." Name elements relative to the block, not DOM depth. Avoid grandchild selectors like `.acu-card__body__text__link` - use `.acu-card__link` instead.

**Source:** [Smashing Magazine - Battling BEM](https://www.smashingmagazine.com/2016/06/battling-bem-extended-edition-common-problems-and-how-to-avoid-them/)

### Pattern 2: CSS Custom Properties as Design Tokens
**What:** CSS variables in `:root` for all themeable values, with fallback values
**When to use:** Colors, spacing, typography, shadows - anything that varies across themes
**Example:**
```css
:root {
  /* Primary tokens - WordPress admin colors */
  --acu-color-primary: var(--wp-admin-theme-color, #2271b1);
  --acu-color-primary-dark: var(--wp-admin-theme-color-darker-10, #135e96);

  /* Semantic tokens */
  --acu-color-background: #fff;
  --acu-color-border: #c3c4c7;
  --acu-color-text: #1d2327;
  --acu-color-text-muted: #646970;

  /* Spacing system */
  --acu-spacing-xs: 4px;
  --acu-spacing-sm: 8px;
  --acu-spacing-md: 16px;
  --acu-spacing-lg: 24px;

  /* Component-specific */
  --acu-card-padding: var(--acu-spacing-lg);
  --acu-card-border-radius: 4px;
  --acu-toggle-size: 20px;
}

.acu-card {
  padding: var(--acu-card-padding);
  border-radius: var(--acu-card-border-radius);
  border: 1px solid var(--acu-color-border);
}
```

**WordPress 7.0 note:** Design tokens arrive April 2026. Current approach is forward-compatible - WordPress tokens can override our variables when available.

**Source:** [Gorilla Logic - Building Design Systems with CSS Variables](https://gorillalogic.com/blog-and-resources/building-design-systems-with-css-variables)

### Pattern 3: CSS-Only Toggle Switch
**What:** Toggle switch using hidden checkbox + label + `:checked` pseudo-class
**When to use:** Binary on/off settings without JavaScript dependency
**Example:**
```html
<!-- Hidden input for unchecked value (0) -->
<input type="hidden" name="setting_name" value="0">
<!-- Checkbox for checked value (1) -->
<input type="checkbox"
       id="setting-id"
       name="setting_name"
       value="1"
       class="acu-toggle__input"
       <?php checked($is_enabled); ?>>
<label for="setting-id" class="acu-toggle">
  <span class="acu-toggle__track"></span>
  <span class="acu-toggle__thumb"></span>
</label>
```

```css
/* Hide native checkbox visually but keep accessible */
.acu-toggle__input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
}

/* Toggle track */
.acu-toggle__track {
  display: block;
  width: 40px;
  height: 20px;
  background: #dcdcde;
  border-radius: 10px;
  transition: background 0.2s;
}

/* Toggle thumb */
.acu-toggle__thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 16px;
  height: 16px;
  background: #fff;
  border-radius: 50%;
  transition: transform 0.2s;
}

/* Checked state - NO JAVASCRIPT */
.acu-toggle__input:checked + .acu-toggle .acu-toggle__track {
  background: var(--acu-color-primary);
}

.acu-toggle__input:checked + .acu-toggle .acu-toggle__thumb {
  transform: translateX(20px);
}
```

**Hidden input pattern:** The hidden input ensures unchecked checkboxes submit "0" to the server, preventing data loss when toggling off.

**Sources:**
- [Alvaro Montoro - Creating a CSS-Only Toggle Switch](https://alvaromontoro.com/blog/68017/creating-a-css-only-toggle-switch)
- [planetOzh - Posting Unchecked Checkboxes](https://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/)

### Pattern 4: PHP Component Render Methods
**What:** Class methods that return component HTML with parameter arrays for configuration
**When to use:** Any reusable UI element (cards, toggles, setting groups, buttons)
**Example:**
```php
class WP_Clean_Up_Components {

    /**
     * Render a setting card
     */
    public static function render_card( $args = [] ) {
        $defaults = [
            'id'          => '',
            'title'       => '',
            'description' => '',
            'content'     => '',
            'modifier'    => '', // BEM modifier class
        ];
        $args = wp_parse_args( $args, $defaults );

        $classes = 'acu-card';
        if ( ! empty( $args['modifier'] ) ) {
            $classes .= ' acu-card--' . sanitize_html_class( $args['modifier'] );
        }
        ?>
        <div class="<?php echo esc_attr( $classes ); ?>" id="<?php echo esc_attr( $args['id'] ); ?>">
            <?php if ( $args['title'] ) : ?>
                <div class="acu-card__header">
                    <h3 class="acu-card__title"><?php echo esc_html( $args['title'] ); ?></h3>
                    <?php if ( $args['description'] ) : ?>
                        <p class="acu-card__description"><?php echo esc_html( $args['description'] ); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="acu-card__body">
                <?php echo $args['content']; // Already escaped in caller ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render a toggle switch
     */
    public static function render_toggle( $args = [] ) {
        $defaults = [
            'name'        => '',
            'value'       => '1',
            'checked'     => false,
            'label'       => '',
            'description' => '',
            'id'          => '',
        ];
        $args = wp_parse_args( $args, $defaults );

        if ( empty( $args['id'] ) ) {
            $args['id'] = 'toggle-' . uniqid();
        }
        ?>
        <div class="acu-setting">
            <!-- Hidden input for unchecked state -->
            <input type="hidden" name="<?php echo esc_attr( $args['name'] ); ?>" value="0">
            <!-- Toggle checkbox -->
            <input type="checkbox"
                   id="<?php echo esc_attr( $args['id'] ); ?>"
                   name="<?php echo esc_attr( $args['name'] ); ?>"
                   value="<?php echo esc_attr( $args['value'] ); ?>"
                   class="acu-toggle__input"
                   <?php checked( $args['checked'] ); ?>>
            <label for="<?php echo esc_attr( $args['id'] ); ?>" class="acu-toggle">
                <span class="acu-toggle__track"></span>
                <span class="acu-toggle__thumb"></span>
            </label>
            <div class="acu-setting__content">
                <label for="<?php echo esc_attr( $args['id'] ); ?>" class="acu-setting__label">
                    <?php echo esc_html( $args['label'] ); ?>
                </label>
                <?php if ( $args['description'] ) : ?>
                    <p class="acu-setting__description"><?php echo esc_html( $args['description'] ); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
```

**Usage:**
```php
// In tab render method
WP_Clean_Up_Components::render_card([
    'title' => 'Admin Bar Settings',
    'description' => 'Configure admin bar elements',
    'content' => WP_Clean_Up_Components::render_toggle([
        'name' => 'adminbar[remove_wp_logo]',
        'checked' => !empty($options['adminbar']['remove_wp_logo']),
        'label' => 'Remove WordPress logo',
        'description' => 'Hides the WP logo from admin bar',
    ]),
]);
```

**Source:** [Refactoring Guru - Composite Pattern in PHP](https://refactoring.guru/design-patterns/composite/php/example)

### Anti-Patterns to Avoid

- **Deep element nesting:** Never use `.acu-card__body__content__text` - flatten to `.acu-card__text`
- **Cross-component selectors:** Never style `.acu-card .acu-button` - use modifiers like `.acu-button--small` instead
- **Nesting BEM classes in CSS:** Never nest selectors (`.acu-card { .acu-card__header { } }`) - keeps specificity low
- **Hardcoding CSS variable values:** Never override design tokens in components (`--acu-color-primary: #ff0000`) - breaks theming
- **JavaScript-dependent toggles:** Never require JavaScript for toggle visual state - use `:checked` only

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Toggle switches | Custom JavaScript click handlers, manual state management | CSS `:checked` + label pseudo-element styling | Accessible by default, works without JS, keyboard navigation free, simpler code |
| Color theming | Manual color replacement across files | CSS custom properties with WordPress admin color variables | Runtime theming, user color scheme support, WordPress 7.0 compatibility |
| Checkbox unchecked submission | JavaScript form serialization hacks | Hidden input with same name before checkbox | HTML-native, no JavaScript needed, standard WordPress pattern |
| Component templating | String concatenation in PHP | Render methods with parameter arrays | Type safety with defaults, reusable patterns, easier testing |

**Key insight:** CSS and HTML have powerful native features for toggles, theming, and forms. JavaScript adds fragility and accessibility overhead for these problems.

## Common Pitfalls

### Pitfall 1: BEM Grandchild Selectors
**What goes wrong:** Creating deeply nested element names like `.acu-card__body__setting__toggle__track` based on DOM structure.
**Why it happens:** Developers mirror HTML nesting in class names, thinking it reflects structure.
**How to avoid:** Name elements relative only to the block. All elements are direct children of the block in BEM naming, regardless of DOM depth. Use `.acu-card__track` even if track is nested 4 levels deep.
**Warning signs:** Class names with multiple `__` double underscores, difficulty moving elements in markup without renaming classes.

**Source:** [Smashing Magazine - Battling BEM](https://www.smashingmagazine.com/2016/06/battling-bem-extended-edition-common-problems-and-how-to-avoid-them/)

### Pitfall 2: CSS Custom Property Scope Conflicts
**What goes wrong:** Setting `--acu-color-primary: #custom` in component CSS overrides the design token, breaking theming.
**Why it happens:** Teams treat CSS variables like Sass variables, setting them anywhere without understanding cascade.
**How to avoid:** Define custom properties ONLY in `:root` for global tokens or component root for component-specific overrides. Never reassign design tokens inside component styles.
**Warning signs:** User color scheme changes don't affect components, inconsistent colors across similar elements.

**Source:** [Nucleus Design System - Be Aware of CSS Custom Properties](https://blog.nucleus.design/be-aware-of-css-custom-properties/)

### Pitfall 3: WordPress Color Scheme Variable Fallbacks
**What goes wrong:** Using `--wp-admin-theme-color` without fallback causes transparent/broken colors when variable is unavailable.
**Why it happens:** Assuming WordPress always provides these variables, forgetting backwards compatibility or non-admin contexts.
**How to avoid:** Always provide fallback: `var(--wp-admin-theme-color, #2271b1)`. Test with color scheme picker disabled.
**Warning signs:** Colors disappear in certain WordPress versions, QA reports "invisible buttons."

### Pitfall 4: Toggle Switch Accessibility Gaps
**What goes wrong:** Hiding checkbox with `display: none` breaks keyboard navigation and screen readers.
**Why it happens:** Developer focuses on visual hiding without understanding accessibility requirements.
**How to avoid:** Use visually-hidden pattern: `position: absolute; opacity: 0; width: 1px; height: 1px;` - keeps element in accessibility tree and tab order.
**Warning signs:** Unable to focus toggle with Tab key, screen readers don't announce on/off state.

**Source:** [Alvaro Montoro - Creating a CSS-Only Toggle Switch](https://alvaromontoro.com/blog/68017/creating-a-css-only-toggle-switch)

### Pitfall 5: Hidden Input Field Ordering
**What goes wrong:** Placing hidden input AFTER checkbox causes both values to submit when checked, creating array `['0', '1']` instead of `'1'`.
**Why it happens:** Not understanding that HTML form submission processes inputs in DOM order.
**How to avoid:** ALWAYS place hidden input immediately BEFORE the checkbox. When unchecked, only hidden submits; when checked, checkbox value overwrites hidden value.
**Warning signs:** Backend receives array values for single checkboxes, saving logic breaks with multiple values.

**Source:** [planetOzh - Posting Unchecked Checkboxes](https://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/)

### Pitfall 6: Cross-Component Styling Instead of Modifiers
**What goes wrong:** Styling `.acu-card .acu-button { font-size: 12px; }` to make buttons smaller in cards.
**Why it happens:** Developers reach for descendant selectors from traditional CSS habits.
**How to avoid:** Create modifier: `.acu-button--small { font-size: 12px; }`. Components remain independent, reusable anywhere.
**Warning signs:** Components only work in specific parent contexts, increased specificity, brittle cascade.

**Source:** [Smashing Magazine - Battling BEM](https://www.smashingmagazine.com/2016/06/battling-bem-extended-edition-common-problems-and-how-to-avoid-them/)

## Code Examples

Verified patterns from official sources:

### WordPress Admin Color Scheme Integration
```css
/* Source: WordPress Core - Gutenberg PR #23048 */
:root {
  /* WordPress provides these variables based on user's color scheme */
  --wp-admin-theme-color: #007cba;
  --wp-admin-theme-color-darker-10: #006ba1;
  --wp-admin-theme-color-darker-20: #005a87;
}

/* Use WordPress colors with fallbacks */
.acu-card--highlighted {
  border-left: 4px solid var(--wp-admin-theme-color, #2271b1);
}

.acu-button--primary {
  background: var(--wp-admin-theme-color, #2271b1);
  border-color: var(--wp-admin-theme-color-darker-10, #135e96);
}

.acu-button--primary:hover {
  background: var(--wp-admin-theme-color-darker-10, #135e96);
  border-color: var(--wp-admin-theme-color-darker-20, #0a4b78);
}
```

### BEM Component Structure
```css
/* Source: BEM Methodology - getbem.com */

/* Block: Independent component */
.acu-setting {
  display: flex;
  align-items: flex-start;
  gap: var(--acu-spacing-md);
  padding: var(--acu-spacing-md) 0;
  border-bottom: 1px solid var(--acu-color-border);
}

/* Elements: Parts of the block */
.acu-setting__content {
  flex: 1;
}

.acu-setting__label {
  font-weight: 500;
  color: var(--acu-color-text);
  cursor: pointer;
}

.acu-setting__description {
  margin: 4px 0 0 0;
  font-size: 12px;
  color: var(--acu-color-text-muted);
}

/* Modifiers: Variations */
.acu-setting--disabled {
  opacity: 0.5;
  pointer-events: none;
}

.acu-setting--highlighted {
  background: #f0f6fc;
  border-left: 3px solid var(--acu-color-primary);
  padding-left: calc(var(--acu-spacing-md) - 3px);
}
```

### Complete Toggle Switch Component
```css
/* Source: Alvaro Montoro - CSS-Only Toggle Switch */

/* Hide checkbox visually but keep accessible */
.acu-toggle__input {
  position: absolute;
  opacity: 0;
  width: 1px;
  height: 1px;
  pointer-events: none;
}

/* Toggle label container */
.acu-toggle {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
  cursor: pointer;
  flex-shrink: 0;
}

/* Toggle background track */
.acu-toggle__track {
  position: absolute;
  top: 0;
  left: 0;
  width: 44px;
  height: 24px;
  background: #dcdcde;
  border-radius: 12px;
  transition: background 0.2s ease;
}

/* Toggle sliding thumb */
.acu-toggle__thumb {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
  transition: transform 0.2s ease;
}

/* Checked state - WordPress admin color */
.acu-toggle__input:checked + .acu-toggle .acu-toggle__track {
  background: var(--wp-admin-theme-color, #2271b1);
}

/* Move thumb when checked */
.acu-toggle__input:checked + .acu-toggle .acu-toggle__thumb {
  transform: translateX(20px);
}

/* Focus state for keyboard navigation */
.acu-toggle__input:focus + .acu-toggle .acu-toggle__track {
  outline: 2px solid var(--wp-admin-theme-color, #2271b1);
  outline-offset: 2px;
}

/* Disabled state */
.acu-toggle__input:disabled + .acu-toggle {
  opacity: 0.5;
  cursor: not-allowed;
}
```

### PHP Component Render Method Pattern
```php
// Source: Composite Pattern - refactoring.guru

/**
 * Setting group component
 */
public static function render_setting_group( $args = [] ) {
    $defaults = [
        'title'    => '',
        'settings' => [], // Array of setting definitions
    ];
    $args = wp_parse_args( $args, $defaults );
    ?>
    <div class="acu-setting-group">
        <?php if ( $args['title'] ) : ?>
            <h4 class="acu-setting-group__title"><?php echo esc_html( $args['title'] ); ?></h4>
        <?php endif; ?>
        <div class="acu-setting-group__items">
            <?php foreach ( $args['settings'] as $setting ) : ?>
                <?php self::render_toggle( $setting ); ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}
```

## State of the Art

| Old Approach | Current Approach | When Changed | Impact |
|--------------|------------------|--------------|--------|
| Sass variables for theming | CSS custom properties | ~2020 | Runtime theming now possible, no compile step for color changes |
| ID selectors or inline styles | BEM methodology | ~2013 | Predictable specificity, component isolation, easier maintenance |
| jQuery for toggle switches | CSS-only with `:checked` | ~2018 | No JavaScript dependency, works without JS, better accessibility |
| WordPress color scheme Sass imports | `--wp-admin-theme-color` CSS variables | WordPress 5.7+ (2021) | User color schemes automatically apply to custom plugins |
| Template files for components | PHP render methods | Modern PHP | Faster (no file I/O), type-safe defaults, easier testing |

**Deprecated/outdated:**
- **Sass color functions on WordPress variables:** WordPress 5.7+ provides darker/lighter variants as separate CSS variables (`--wp-admin-theme-color-darker-10`), don't use `darken($theme-color, 10%)`
- **`appearance: none` for checkbox hiding:** Use visually-hidden pattern instead - `appearance: none` can break in some browsers and removes accessibility features
- **Multiple modifiers in single class:** Don't use `.acu-card--highlighted--collapsed` - use separate classes `.acu-card--highlighted .acu-card--collapsed` for flexibility

## Open Questions

Things that couldn't be fully resolved:

1. **WordPress 7.0 Design Token Variable Names**
   - What we know: WordPress 7.0 (April 2026) will introduce standardized design tokens as CSS custom properties
   - What's unclear: Exact variable naming convention, whether `--wp-admin-theme-color` will be deprecated or aliased
   - Recommendation: Use current `--wp-admin-theme-color` variables with fallbacks. After WordPress 7.0 release, add aliases to new token names if different. Our CSS custom property architecture makes this a simple `:root` update.

2. **PHP Component Class Organization**
   - What we know: Render methods work well for small component libraries, but can become unwieldy with many components
   - What's unclear: At what point to split into separate component classes vs. keeping in single helper class
   - Recommendation: Start with single `WP_Clean_Up_Components` class in Phase 2. If component count exceeds 10, refactor in Phase 3 to separate classes (`WP_Clean_Up_Card`, `WP_Clean_Up_Toggle`, etc.)

3. **BEM Modifier Stacking Approach**
   - What we know: Multiple modifiers can be applied as separate classes (`.acu-card .acu-card--highlighted .acu-card--collapsed`)
   - What's unclear: Whether to combine common modifier pairs into compound classes for performance
   - Recommendation: Use separate modifier classes for flexibility. Only create compound classes if profiling shows CSS performance issues (unlikely at this scale).

## Sources

### Primary (HIGH confidence)
- [BEM Official Methodology](https://en.bem.info/methodology/) - Core BEM principles and naming conventions
- [Smashing Magazine - Battling BEM Extended Edition](https://www.smashingmagazine.com/2016/06/battling-bem-extended-edition-common-problems-and-how-to-avoid-them/) - Common problems and solutions
- [Alvaro Montoro - Creating a CSS-Only Toggle Switch](https://alvaromontoro.com/blog/68017/creating-a-css-only-toggle-switch) - CSS toggle implementation
- [WordPress GitHub - Admin Color Variables](https://github.com/WordPress/WordPress/blob/master/wp-admin/css/colors/_variables.scss) - WordPress admin color scheme structure
- [Make WordPress Core - CSS Custom Properties Introduction](https://make.wordpress.org/core/2021/01/29/introducing-css-custom-properties/) - WordPress CSS variable adoption
- [WordPress Gutenberg PR #23048](https://github.com/WordPress/gutenberg/pull/23048) - Theme color CSS variables implementation

### Secondary (MEDIUM confidence)
- [Gorilla Logic - Building Design Systems with CSS Variables](https://gorillalogic.com/blog-and-resources/building-design-systems-with-css-variables) - Design token architecture
- [Nucleus Design System - Be Aware of CSS Custom Properties](https://blog.nucleus.design/be-aware-of-css-custom-properties/) - CSS variable pitfalls in design systems
- [planetOzh - Posting Unchecked Checkboxes](https://planetozh.com/blog/2008/09/posting-unchecked-checkboxes-in-html-forms/) - Hidden input pattern
- [Refactoring Guru - Composite Pattern in PHP](https://refactoring.guru/design-patterns/composite/php/example) - PHP component patterns
- [MRW Web Design - Standardized Design Tokens WordPress](https://mrwweb.com/standardized-design-tokens-css-wordpress-future/) - WordPress 7.0 design tokens context
- [Atto WP - WordPress 7.0 Complete Guide](https://attowp.com/trends-news/wordpress-7-0-complete-guide-2026/) - WordPress 7.0 features and timeline

### Tertiary (LOW confidence)
- Various tutorials and blog posts on BEM, CSS toggles, and design systems - used for pattern validation but not authoritative

## Metadata

**Confidence breakdown:**
- Standard stack: HIGH - BEM is industry standard, CSS custom properties are W3C standard, WordPress admin colors are core API
- Architecture: HIGH - Patterns verified with official documentation and established methodologies
- Pitfalls: HIGH - Sourced from authoritative design system blogs and Smashing Magazine's comprehensive BEM guide

**Research date:** 2026-01-24
**Valid until:** 2026-04-09 (WordPress 7.0 release - may introduce new design token conventions requiring update)
