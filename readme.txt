=== Simple WP Slider ===
Contributors: sungraizfaryad
Tags: slider, carousel, swiper, gutenberg, video
Requires at least: 6.0
Tested up to: 7.0
Stable tag: 2.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Multi-slider plugin for WordPress with shortcode, Gutenberg block, image + video slides, and Swiper-powered frontend.

== Description ==

Create unlimited named sliders, each with its own settings and per-slide options (alt text, caption, link, call-to-action). Display them anywhere via the `[simplewpslider id="ID"]` shortcode or the bundled Gutenberg block. Powered by Swiper.js 11 — accessible, touch-friendly, no jQuery.

**Slide types**

* Image slides (uses your Media Library, full srcset + lazy-load)
* Self-hosted video slides (MP4)
* YouTube and Vimeo slides (privacy-friendly click-to-load facade)

**Per-slider settings**

* Autoplay, speed, loop, transition (slide / fade)
* Arrows, dots, keyboard navigation
* Slides-per-view + responsive breakpoints
* Aspect ratio enforcement (16:9 / 4:3 / 1:1 / auto) to prevent layout shift
* Respect prefers-reduced-motion

**Accessibility**

* WAI-ARIA carousel pattern
* Drag-reorder via keyboard in the admin
* Full screen-reader live-region announcements via Swiper's a11y module

== Installation ==

1. Upload the plugin via Plugins → Add New → Upload, or install from the WordPress.org plugin directory.
2. Activate the plugin.
3. Go to Sliders → Add New, create a slider, add slides.
4. Insert the slider via the "Simple WP Slider" block or with `[simplewpslider id="123"]`.

== Frequently Asked Questions ==

= I upgraded from 1.x. Where did my images go? =

They were automatically imported into a new slider called "Default Slider" under the Sliders menu. The old `[simplewpslider]` shortcode (with no id) still renders that slider, so existing pages keep working.

= Does this plugin send data anywhere? =

No. Nothing leaves your site. YouTube and Vimeo embeds load only after a visitor clicks the play button, and YouTube uses the `youtube-nocookie.com` domain.

= Can I display the same slider in multiple places? =

Yes. Use the same id with the shortcode or block on any page or template.

== Screenshots ==

1. Sliders list under the Sliders menu.
2. Edit screen: slide manager with drag-reorder and per-slide editor.
3. Slider settings panel: autoplay, dots, arrows, effect, etc.
4. Block-editor preview via the Simple WP Slider block.
5. Frontend example.

== Changelog ==

= 2.0.0 =
* Complete rewrite. Multi-slider support via a new "Sliders" menu (Custom Post Type).
* Dropped Slick (archived) in favor of Swiper.js 11 (modern, accessible, touch-friendly, no jQuery).
* Added Gutenberg block "Simple WP Slider".
* Added per-slide metadata: alt text, caption, link URL + target, CTA.
* Added video slides: self-hosted, YouTube, Vimeo (privacy-friendly facade — embeds load only on click).
* Added per-slider settings: autoplay, speed, loop, effect, arrows, dots, keyboard, slides-per-view, breakpoints, aspect ratio.
* Added WAI-ARIA carousel markup + keyboard drag-reorder + prefers-reduced-motion respect.
* Auto-migration from 1.x: existing images become a "Default Slider" CPT post; the bare `[simplewpslider]` shortcode keeps working.
* Hardened against WordPress.org Plugin Review standards (prefixes, sanitization, escaping, REST permission callbacks, real uninstall cleanup).

= 1.0.2 =
* Tested up to 6.7.2.

= 1.0.1 =
* Tested up to 6.4.

= 1.0 =
* Initial version.

== Upgrade Notice ==

= 2.0.0 =
Major rewrite. Your existing images are imported automatically into a "Default Slider" and the old [simplewpslider] shortcode keeps working. Back up your site before upgrading if you depend on the plugin in production.

== Privacy ==

The plugin makes no outbound requests at runtime. YouTube embeds use `youtube-nocookie.com` and only load after user interaction. Vimeo thumbnails are fetched via Vimeo's public oEmbed endpoint only when an editor adds a Vimeo slide in the admin — never on frontend page loads.
