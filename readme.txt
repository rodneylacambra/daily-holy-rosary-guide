=== Holy Rosary ===
Contributors: rodneylacambra
Tags: rosary, catholic, prayer, religion, devotion
Requires at least: 5.9
Tested up to: 6.7
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A beautiful, interactive step-by-step Holy Rosary prayer guide for your WordPress site.

== Description ==

Holy Rosary brings a fully interactive, step-by-step Rosary prayer guide to your WordPress website. Perfect for Catholic parishes, prayer groups, schools, and personal devotion sites.

= Features =

* Interactive bead tracker with animated rosary — gold beads, blue decade beads, and a heart centerpiece
* Auto-detects today's mystery set (Joyful, Luminous, Sorrowful, Glorious)
* Full step-by-step guidance through all 59 beads + cross
* Complete prayer texts: Apostles' Creed, Our Father, Hail Mary, Glory Be, Fatima Prayer
* Closing prayers: Hail Holy Queen, Final Prayer
* Additional prayers: Litany of the Blessed Virgin Mary, St. Michael Prayer, Prayer to Saint Joseph
* Prayer streak tracker (requires login)
* Personal prayer intentions
* Community prayer wall with moderation
* Simple shortcode: [holy_rosary]
* Gutenberg block support
* Translation-ready (i18n)

= Usage =

Add the Rosary to any page or post:

`[holy_rosary]`

With options:

`[holy_rosary mystery="joyful" show_wall="false"]`

= Shortcode Attributes =

* `mystery` — auto | joyful | luminous | sorrowful | glorious (default: auto)
* `show_wall` — true | false (default: true)
* `show_stats` — true | false (default: true)

== Installation ==

1. Upload the `holy-rosary` folder to `/wp-content/plugins/`
2. Activate the plugin from the **Plugins** menu in WordPress
3. Add `[holy_rosary]` to any page or post
4. Configure settings under **Holy Rosary → Settings**

== Frequently Asked Questions ==

= Does this work without an account? =
Yes. The Rosary guide works for all visitors. Prayer streak tracking and personal intentions require a WordPress user account.

= Can I use multiple instances on one page? =
Yes. You can add the shortcode multiple times on the same page.

= Is the prayer wall moderated? =
By default, yes. All submitted intentions require admin approval before appearing publicly. You can disable moderation under Holy Rosary → Settings.

= Will my data be deleted if I uninstall? =
Only if you enable "Remove Data on Uninstall" in the Settings. By default, your data is preserved.

== Screenshots ==

1. The interactive Rosary bead tracker with heart centerpiece
2. Step-by-step prayer guidance with full prayer texts
3. Admin settings page
4. Prayer wall moderation

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release.
