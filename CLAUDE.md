# Admin Clean Up

WordPress plugin that cleans up and simplifies the admin interface. Settings page with 10 tabs controlling various admin elements.

## Architecture

```
admin-clean-up.php          Main plugin class (WP_Clean_Up singleton, OPTION_KEY constant, module loading)
includes/
  class-admin-page.php      Settings page UI (10 tab render methods, sidebar nav, form handling)
  class-components.php      UI component library (static methods: render_card, render_toggle, render_setting_group, render_radio_group, render_text_input, render_select)
  class-admin-bar.php       Removes admin bar elements (logo, site menu, +New, search, howdy)
  class-admin-menus.php     Hides admin menu items per role (non_admin, non_editor, all)
  class-comments.php        Disables all comment functionality site-wide
  class-dashboard.php       Removes dashboard widgets (welcome, at a glance, activity, quick draft, events)
  class-footer.php          Removes/customizes admin footer text and version
  class-notices.php         Hides update notices, all notices, screen options, help tab
  class-clean-filenames.php Cleans uploaded filenames (special chars, spaces, lowercase)
  class-plugin-notices.php  Hides plugin-specific nag screens (PixelYourSite, Elementor, Yoast, Complianz, GTM4WP)
  class-site-health.php     Removes/disables Site Health entirely
  class-updates.php         Controls auto-updates (core, plugins, themes, emails, nags)
  class-frontend.php        Frontend cleanup (jQuery Migrate console message)
assets/css/admin.css        Settings page styles (WordPress-native colors, BEM components)
languages/                  Swedish translation (sv_SE.po/.mo)
```

## Key Patterns

- **Options storage**: Single array in `wp_options` under `WP_Clean_Up::OPTION_KEY` (`wp_clean_up_options`). Nested by tab: `[adminbar][remove_wp_logo]`, `[menus][remove_posts]`, etc.
- **Component library**: All UI via `WP_Clean_Up_Components::render_*()` static methods. Output buffering (`ob_start/ob_get_clean`) captures component HTML, then passed as `content` to `render_card()`.
- **Toggle hidden input**: Hidden input with value `0` BEFORE checkbox ensures unchecked state submits. Form order: hidden=0 then checkbox=1.
- **CSS**: WordPress-native colors via custom properties (`--acu-*`). Uses `--wp-admin-theme-color` for accent. BEM class naming (`acu-settings`, `acu-card__header`, `acu-toggle__input`).
- **Translations**: Textdomain `admin-clean-up`. All user-facing strings wrapped in `__()` or `esc_html_e()`. Swedish translations in `languages/`.
- **Tab switching**: URL param `?tab=adminbar`. Active tab stored in hidden input. Each tab has `render_{tab}_tab($options)` method.
- **Module pattern**: Each feature is a class instantiated in `init_modules()`. Checks relevant options and hooks into WordPress only when enabled.
- **Activation**: `register_activation_hook` sets full default options array (all keys present, all values 0/default).

## Adding a New Plugin to "Plugins" Tab

1. Add detection in `class-plugin-notices.php` → `get_supported_plugins()` array
2. Add hide logic in same class (hooks to remove the plugin's notices)
3. Add option key to `get_default_options()` in main class and `sanitize_options()`
4. The render is dynamic — loops `get_installed_supported_plugins()` and renders toggles automatically

## Adding a New Tab

1. Add to `get_tabs()` array in `class-admin-page.php`
2. Create `render_{name}_tab($options)` method using component library
3. Add option keys to `get_default_options()` and `sanitize_options()`
4. Create feature class in `includes/class-{name}.php`
5. Load and init in main plugin class
6. Add translations to `.po` file, recompile `.mo`

## Test Site Sync

```bash
rsync -av --delete "/Users/lucas/Sites/admin-clean-up/" "/Users/lucas/Local Sites/testar-plugins/app/public/wp-content/plugins/admin-clean-up/" --exclude='.planning' --exclude='.git' --exclude='.DS_Store'
```

## Conventions

- WordPress coding standards (tabs, Yoda conditions, escaping output)
- Field names must never change (backwards compatibility with saved options)
- `phpcs:ignore` with explanation when intentionally skipping escaping (e.g., pre-escaped component content)
- Translators comments above `sprintf(__(...))` calls with placeholders
- No `load_plugin_textdomain()` — WordPress auto-loads since 4.6

## Swedish Translations (REQUIRED)

**All new user-facing strings MUST be translated to Swedish.** After adding new features:

1. Wrap all strings in `__( 'String', 'admin-clean-up' )` or `esc_html_e( 'String', 'admin-clean-up' )`
2. Add entries to `languages/admin-clean-up-sv_SE.po`:
   ```
   #: includes/class-file.php
   msgid "English string"
   msgstr "Svensk översättning"
   ```
3. Compile to .mo: `msgfmt -o languages/admin-clean-up-sv_SE.mo languages/admin-clean-up-sv_SE.po`

### Translation Guidelines
- Use formal Swedish ("du" not "ni")
- Keep technical terms when appropriate (e.g., "frontend", "dashboard")
- Match WordPress Swedish admin terminology where possible
- Keep brand names unchanged (Yoast, Complianz, etc.)
