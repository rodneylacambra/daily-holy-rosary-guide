# Daily Holy Rosary Guide

A beautiful, interactive step-by-step Holy Rosary prayer experience for WordPress websites. Designed for Catholic parishes, ministries, schools, prayer groups, and personal devotion sites.

---

## Features

- Interactive Rosary bead tracker with animated Rosary UI
- Gold beads, blue decade beads, and heart centerpiece
- Auto-detects today's mystery:
  - Joyful Mysteries
  - Luminous Mysteries
  - Sorrowful Mysteries
  - Glorious Mysteries
- Step-by-step guidance through all 59 beads + crucifix
- Full prayer texts included:
  - Apostles' Creed
  - Our Father
  - Hail Mary
  - Glory Be
  - Fatima Prayer
- Closing prayers:
  - Hail Holy Queen
  - Final Prayer
- Additional prayers:
  - Litany of the Blessed Virgin Mary
  - St. Michael Prayer
  - Prayer to Saint Joseph
- Prayer streak tracking (logged-in users)
- Personal prayer intentions
- Community prayer wall with moderation
- Gutenberg block support
- Translation-ready (i18n)
- Easy shortcode integration

---

## Installation

1. Download or clone this repository
2. Upload the plugin folder to:

```bash
/wp-content/plugins/
```

3. Activate the plugin in WordPress
4. Add the shortcode to any page or post:

```php
[holy_rosary]
```

5. Configure settings under:

```text
Holy Rosary → Settings
```

---

## Usage

### Basic Shortcode

```php
[holy_rosary]
```

### With Options

```php
[holy_rosary mystery="joyful" show_wall="false"]
```

---

## Shortcode Attributes

| Attribute | Values | Default |
|---|---|---|
| `mystery` | auto, joyful, luminous, sorrowful, glorious | auto |
| `show_wall` | true, false | true |
| `show_stats` | true, false | true |

---

## Screenshots

1. Interactive Rosary bead tracker with heart centerpiece
2. Step-by-step prayer guidance with full prayer texts
3. Admin settings page
4. Community prayer wall moderation

---

## Frequently Asked Questions

### Does this work without an account?

Yes. The Rosary prayer guide works for all visitors. Prayer streak tracking and personal intentions require a WordPress user account.

### Can I use multiple instances on one page?

Yes. You can add the shortcode multiple times on the same page.

### Is the prayer wall moderated?

Yes, by default. Submitted intentions require admin approval before appearing publicly. Moderation can be disabled in the settings.

### Will my data be deleted if I uninstall the plugin?

Only if you enable **Remove Data on Uninstall** in the plugin settings. By default, user data is preserved.

---

## Requirements

- WordPress 5.9+
- PHP 7.4+

---

## Gutenberg Support

The plugin includes Gutenberg block support for easier integration into modern WordPress layouts.

---

## Translation Ready

Holy Rosary is fully internationalization-ready and supports translation into different languages.

---

## Ideal For

- Catholic parish websites
- Prayer ministry websites
- Online Rosary communities
- Catholic schools
- Marian devotion websites
- Personal prayer blogs

---

## Changelog

### 1.0.0

- Initial release

---

## License

Licensed under the GPL v2 or later.

- License: GPLv2 or later
- License URI: https://www.gnu.org/licenses/gpl-2.0.html

---

## About the Project

Holy Rosary was created to help Catholics pray the Rosary online in a beautiful, guided, and spiritually focused experience directly inside WordPress.

Made with devotion for the Catholic community ❤️
