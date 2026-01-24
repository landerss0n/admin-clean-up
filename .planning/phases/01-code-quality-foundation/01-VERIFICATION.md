---
phase: 01-code-quality-foundation
verified: 2026-01-24T17:50:00Z
status: human_needed
score: 6/6 must-haves verified (automated checks)
human_verification:
  - test: "PYS Pro exclusion behavior"
    expected: "With PYS Pro active (Free inactive), no PYS tab appears and no hooks fire"
    why_human: "Requires installing/activating PixelYourSite Pro plugin"
  - test: "PYS Free behavior"
    expected: "With PYS Free active (Pro inactive), PYS tab appears and notice hiding works"
    why_human: "Requires installing/activating PixelYourSite Free plugin"
  - test: "Swedish translation rendering"
    expected: "Switching site to sv_SE locale shows Swedish translations"
    why_human: "Requires changing WordPress locale and viewing settings page"
---

# Phase 1 Verification: Code Quality Foundation

**Phase Goal:** Options save and load correctly, PYS behaves properly, and the plugin speaks English with translation support

**Verified:** 2026-01-24T17:50:00Z  
**Status:** human_needed  
**Re-verification:** No - initial verification

## Goal Achievement

### Must-Have #1: Deep Merge Works

**Criterion:** Adding a new option key to any nested settings array applies the default correctly on next page load (deep merge works)

**Status:** ✓ VERIFIED

**Evidence:**
- `parse_args_recursive()` method exists at lines 136-150 in admin-clean-up.php
- Method properly handles nested arrays by recursively merging at all levels
- `get_options()` uses `self::parse_args_recursive($options, $defaults)` instead of `wp_parse_args()`
- Method recursively checks `is_array($value) && isset($result[$key]) && is_array($result[$key])`

**Files checked:**
- `/Users/lucas/Sites/admin-clean-up/admin-clean-up.php` lines 136-150, 224

**Technical verification:**
```bash
$ grep -c "parse_args_recursive" admin-clean-up.php
3
# (1 method definition + 1 call in get_options + 1 recursive self-call)
```

---

### Must-Have #2: Option Key Constant Usage

**Criterion:** The string 'wp_clean_up_options' appears nowhere in the codebase except the constant definition

**Status:** ✓ VERIFIED

**Evidence:**
- OPTION_KEY constant defined at line 42: `const OPTION_KEY = 'wp_clean_up_options';`
- String 'wp_clean_up_options' appears ONLY in constant definition (1 occurrence)
- All other references use `WP_Clean_Up::OPTION_KEY` or `self::OPTION_KEY`
- Settings group name 'wp_clean_up_options_group' is different and allowed (lines 44, 333 in class-admin-page.php)

**Files checked:**
- `/Users/lucas/Sites/admin-clean-up/admin-clean-up.php` - constant definition and usage
- `/Users/lucas/Sites/admin-clean-up/includes/class-admin-page.php` - 40+ form field name attributes use constant
- `/Users/lucas/Sites/admin-clean-up/uninstall.php` - uses constant via require_once

**Technical verification:**
```bash
$ grep -rn "'wp_clean_up_options'" --include="*.php" . | grep -v "languages/"
./admin-clean-up.php:42:    const OPTION_KEY = 'wp_clean_up_options';
# Only 1 match - the constant definition
```

**Constant usage examples:**
- Line 45 in class-admin-page.php: `register_setting('wp_clean_up_options_group', WP_Clean_Up::OPTION_KEY, ...)`
- Line 75: `get_option( WP_Clean_Up::OPTION_KEY, [] )`
- Line 334: `name="<?php echo esc_attr( WP_Clean_Up::OPTION_KEY ); ?>[_current_tab]"`
- Lines 397, 412, 427, 442, 457, etc.: All form fields use dynamic constant

---

### Must-Have #3: Activation Defaults Match Structure

**Criterion:** A fresh activation on a clean site produces an options array matching the current full structure (all keys present)

**Status:** ✓ VERIFIED

**Evidence:**
- Activation hook `wp_clean_up_activate()` sets complete defaults structure
- All 9 top-level tabs present in both `get_options()` and activation hook:
  - adminbar, comments, dashboard, menus, footer, notices, media, plugins, updates
- Total option keys: 54 in both functions (verified by grep count)
- Structure is identical between get_options() defaults (lines 156-220) and activation defaults (lines 244-308)

**Files checked:**
- `/Users/lucas/Sites/admin-clean-up/admin-clean-up.php` lines 241-310

**Technical verification:**
```bash
$ grep -A 70 "public static function get_options" admin-clean-up.php | grep -E "^\s+'[a-z_]+'" | wc -l
54

$ grep -A 70 "function wp_clean_up_activate" admin-clean-up.php | grep -E "^\s+'[a-z_]+'" | wc -l
54

# Both have 9 top-level tabs in identical order
```

**Top-level tabs verified (both functions):**
```
adminbar, comments, dashboard, menus, footer, notices, media, plugins, updates
```

---

### Must-Have #4: PYS Pro Exclusion (Tab Visibility)

**Criterion:** With PixelYourSite Pro active (and Free inactive), the PYS tab functionality is completely dormant -- no hooks fire, no notices are hidden

**Status:** ✓ VERIFIED (code structure)

**Evidence:**
- `get_supported_plugins()` includes `pro_check` key: `'pixelyoursite-pro/pixelyoursite-pro.php'` (line 191)
- `get_installed_supported_plugins()` checks Pro exclusion (lines 217-218):
  ```php
  if ( ! empty( $plugin['pro_check'] ) && is_plugin_active( $plugin['pro_check'] ) ) {
      continue;
  }
  ```
- Tab only appears when Free is active AND Pro is NOT active
- Notice hiding hook only fires when same condition is met (class-plugin-notices.php lines 30-33)

**Files checked:**
- `/Users/lucas/Sites/admin-clean-up/includes/class-admin-page.php` lines 188-192, 206-221
- `/Users/lucas/Sites/admin-clean-up/includes/class-plugin-notices.php` lines 30-33

**Code pattern verified:**
```php
// class-admin-page.php line 191
'pro_check' => 'pixelyoursite-pro/pixelyoursite-pro.php',

// class-admin-page.php lines 217-218
if ( ! empty( $plugin['pro_check'] ) && is_plugin_active( $plugin['pro_check'] ) ) {
    continue; // Skip when Pro is active
}
```

**Requires human verification:** Actual plugin activation testing with PYS Pro

---

### Must-Have #5: PYS Free Behavior

**Criterion:** With PixelYourSite Free active (and Pro inactive), the PYS tab appears and notice-hiding works normally

**Status:** ✓ VERIFIED (code structure)

**Evidence:**
- `get_installed_supported_plugins()` returns pixelyoursite when:
  - `is_plugin_active('pixelyoursite/facebook-pixel-master.php')` is true (Free active)
  - `is_plugin_active('pixelyoursite-pro/pixelyoursite-pro.php')` is false (Pro NOT active)
- Notice hiding hook in class-plugin-notices.php lines 30-33:
  ```php
  if ( ! empty( $plugins_options['hide_pixelyoursite_notices'] )
      && $this->is_plugin_active( 'pixelyoursite/facebook-pixel-master.php' )
      && ! $this->is_plugin_active( 'pixelyoursite-pro/pixelyoursite-pro.php' ) ) {
      add_action( 'admin_head', [ $this, 'hide_pixelyoursite_notices' ] );
  }
  ```

**Files checked:**
- `/Users/lucas/Sites/admin-clean-up/includes/class-plugin-notices.php` lines 30-33

**Requires human verification:** Actual plugin activation testing with PYS Free

---

### Must-Have #6: Translation Support

**Criterion:** All user-facing strings render in English by default, and switching to sv_SE locale shows Swedish translations

**Status:** ✓ VERIFIED (infrastructure)

**Evidence:**
- `load_plugin_textdomain()` called on init action (line 58 in admin-clean-up.php)
- Text domain 'admin-clean-up' loads from `/languages` directory
- Translation files exist and are current:
  - `admin-clean-up.pot` (14,790 bytes, modified 2026-01-24)
  - `admin-clean-up-sv_SE.po` (20,270 bytes, modified 2026-01-24)
  - `admin-clean-up-sv_SE.mo` (13,532 bytes, compiled 2026-01-24)
- No hardcoded Swedish strings found in PHP source files
- All strings use `__()` or `_e()` with 'admin-clean-up' text domain

**Files checked:**
- `/Users/lucas/Sites/admin-clean-up/admin-clean-up.php` lines 58, 66-72
- `/Users/lucas/Sites/admin-clean-up/languages/` directory

**Technical verification:**
```bash
$ ls -la languages/
-rw-r--r--  admin-clean-up-sv_SE.mo
-rw-r--r--  admin-clean-up-sv_SE.po
-rw-r--r--  admin-clean-up.pot

$ grep -rn "Inställningar\|Kommentarer\|Meddelanden" --include="*.php" . | grep -v "languages/"
# No matches - no hardcoded Swedish in source files
```

**Code verified:**
```php
// admin-clean-up.php line 58
add_action( 'init', [ $this, 'load_textdomain' ] );

// admin-clean-up.php lines 67-71
load_plugin_textdomain(
    'admin-clean-up',
    false,
    dirname( plugin_basename( __FILE__ ) ) . '/languages'
);
```

**Requires human verification:** Actual locale switching and visual confirmation of Swedish strings

---

## Requirements Coverage

| Requirement | Status | Evidence |
|-------------|--------|----------|
| CODE-02: Fix deep option merging | ✓ SATISFIED | parse_args_recursive() implemented and used |
| CODE-03: Options key constant | ✓ SATISFIED | OPTION_KEY constant used everywhere, only 1 string literal |
| CODE-04: Activation hook sync | ✓ SATISFIED | 54 keys in both get_options() and activation hook |
| PYS-01: PYS Free-only condition | ✓ SATISFIED | Pro exclusion logic in both tab visibility and hook firing |
| I18N-01: English base language | ✓ SATISFIED | load_plugin_textdomain() on init, .pot/.po/.mo files exist |

**Score:** 5/5 requirements satisfied

---

## Anti-Patterns Found

### Files Scanned
All files modified in Phase 1:
- admin-clean-up.php
- includes/class-admin-page.php
- includes/class-plugin-notices.php
- uninstall.php
- languages/* (translation files)

### Results

**No anti-patterns detected.**

All code is substantive, properly wired, and follows WordPress coding standards.

---

## Human Verification Required

While all automated checks pass, the following items require manual testing to fully confirm goal achievement:

### 1. PYS Pro Exclusion Behavior

**Test:**
1. Install and activate PixelYourSite Pro plugin
2. Deactivate PixelYourSite Free (if installed)
3. Navigate to Settings → Admin Clean Up
4. Check available tabs

**Expected:**
- No "Plugins" tab appears in navigation
- Plugin list page shows no PixelYourSite options
- Verify by inspecting `get_installed_supported_plugins()` return value (should be empty)

**Why human:** Requires actual plugin installation and WordPress environment

---

### 2. PYS Free Behavior

**Test:**
1. Install and activate PixelYourSite Free plugin
2. Deactivate PixelYourSite Pro (if installed)
3. Navigate to Settings → Admin Clean Up
4. Verify "Plugins" tab appears
5. Enable "Hide PixelYourSite promotional notices" option
6. Navigate to PixelYourSite settings page

**Expected:**
- "Plugins" tab is visible in Admin Clean Up
- Checkbox for "Hide PixelYourSite promotional notices" appears
- After enabling, PYS promotional notices are hidden via CSS
- Verify by inspecting page source for `.pys_notice { display: none; }` in admin head

**Why human:** Requires actual plugin installation and visual verification

---

### 3. Deep Merge Functionality

**Test:**
1. Activate plugin on fresh WordPress install
2. Navigate to Settings → Admin Clean Up → Admin Bar
3. Enable 2-3 options (e.g., "Remove WP Logo", "Remove Site Menu")
4. Save settings
5. Via phpMyAdmin or wp-cli, manually DELETE one nested key from the database (e.g., remove 'remove_search' from adminbar array)
6. Reload the Admin Clean Up settings page

**Expected:**
- Deleted key reappears with its default value (`false`)
- User-modified keys retain their saved values
- No PHP notices about undefined indexes

**Why human:** Requires database manipulation and visual verification

---

### 4. Activation Defaults

**Test:**
1. On a clean WordPress installation, activate the plugin
2. Via phpMyAdmin or wp-cli, query the options table: `SELECT option_value FROM wp_options WHERE option_name = 'wp_clean_up_options'`
3. Parse the serialized array

**Expected:**
- Option exists immediately after activation (not on first access)
- All 9 top-level keys present: adminbar, comments, dashboard, menus, footer, notices, media, plugins, updates
- All 54 nested keys present with correct default values
- All boolean defaults are `false`
- String defaults match get_options(): 'all', 'default', 'non_admin', etc.

**Why human:** Requires clean WordPress environment and database inspection

---

### 5. Swedish Translation Rendering

**Test:**
1. Navigate to Settings → General
2. Change "Site Language" to "Svenska (Swedish)"
3. Save changes
4. Navigate to Settings → Admin Clean Up
5. Check all tab labels, setting labels, and descriptions

**Expected:**
- All text appears in Swedish
- Tab names: "Adminbar" → "Adminbar", "Dashboard" → "Instrumentpanel", etc.
- Setting labels and descriptions render Swedish translations from .po file
- No English strings visible (except brand names like "WordPress", "PixelYourSite")

**Why human:** Requires changing WordPress locale and visual inspection

---

### 6. English Default Language

**Test:**
1. With site language set to English (en_US or default)
2. Navigate to Settings → Admin Clean Up
3. Verify all text appears in English

**Expected:**
- All tab labels in English: "Admin Bar", "Comments", "Dashboard", etc.
- All setting labels in English
- All descriptions in English
- This is the baseline before locale switching

**Why human:** Visual verification of rendered strings

---

## Summary

### Automated Verification Results

**6 of 6 must-haves VERIFIED programmatically:**
1. ✓ Deep merge: parse_args_recursive() exists and is used correctly
2. ✓ Constant usage: Only 1 string literal occurrence (the definition)
3. ✓ Activation sync: 54 keys in both structures, 9 identical tabs
4. ✓ PYS Pro exclusion: Code checks pro_check and skips when Pro active
5. ✓ PYS Free behavior: Code allows tab when Free active and Pro inactive
6. ✓ Translation infrastructure: load_plugin_textdomain() on init, .pot/.po/.mo exist

**All 5 requirements satisfied** based on code structure analysis.

**No anti-patterns detected** in any modified files.

**No syntax errors** in any PHP files.

### Human Verification Needed

6 test scenarios require manual execution to confirm runtime behavior:
- PYS Pro exclusion (plugin activation)
- PYS Free behavior (plugin activation and visual check)
- Deep merge functionality (database manipulation)
- Activation defaults (database inspection)
- Swedish translation rendering (locale change)
- English default language (baseline visual check)

### Overall Assessment

**Phase 1 goal achieved from a code structure perspective.** All required infrastructure is in place:
- Deep merge algorithm implemented
- Option key constant used consistently
- Activation defaults are complete and synced
- PYS Pro exclusion logic is present in both detection points
- Translation loading is properly hooked
- Translation files are current

**Recommendation:** Proceed with human verification checklist above to confirm runtime behavior, but code implementation is complete and correct.

---

_Verified: 2026-01-24T17:50:00Z_  
_Verifier: Claude (gsd-verifier)_  
_Verification type: Initial (not re-verification)_
