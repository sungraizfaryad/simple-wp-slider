# Simple WP Slider

Multi-slider plugin for WordPress with shortcode, Gutenberg block, image + video slides, and Swiper-powered frontend.

[WordPress.org plugin page →](https://wordpress.org/plugins/simple-wp-slider)

## Features

- Unlimited named sliders (each as a `swps_slider` Custom Post Type)
- Image, self-hosted video, YouTube, and Vimeo slides
- Per-slide alt / caption / link / CTA
- Per-slider Swiper.js settings (autoplay, dots, arrows, effect, breakpoints, aspect ratio)
- WAI-ARIA carousel markup
- Gutenberg block + starter pattern
- Silent auto-migration from v1.x

## Development

```bash
git clone https://github.com/sungraizfaryad/simple-wp-slider
cd simple-wp-slider
npm install
composer install
```

Build assets:

```bash
npm run build   # production
npm start       # watch mode
```

Tests + lint:

```bash
vendor/bin/phpunit    # WP PHPUnit suite (requires wp-env or local WP test install)
npm run lint:js
npm run lint:css
vendor/bin/phpcs --standard=phpcs.xml.dist
```

Local dev environment via wp-env (optional):

```bash
npx wp-env start
```

## Architecture

| Layer | File |
|---|---|
| Plugin bootstrap | `simple-wp-slider.php` → `SWPS_Plugin::instance()` |
| CPT registration | `includes/class-swps-cpt.php` (`swps_slider`) |
| Post meta + REST | `includes/class-swps-meta.php` (slides + settings + schema-version) |
| Sanitization | `includes/class-swps-sanitizer.php` (pure) |
| REST API | `includes/class-swps-rest.php` (`swps/v1` namespace) |
| Frontend renderer | `includes/class-swps-renderer.php` (WAI-ARIA carousel HTML) |
| Shortcode | `includes/class-swps-shortcode.php` (`[simplewpslider]`) |
| Asset enqueue | `includes/class-swps-assets.php` (conditional, deferred) |
| Video parser | `includes/class-swps-video.php` (YouTube / Vimeo URL → id) |
| v1 → v2 migrator | `includes/class-swps-migrator.php` (silent, idempotent) |
| Gutenberg block | `includes/class-swps-block.php` + `src/block/*` |
| Admin React UI | `src/admin/*` (SlideManager, SlideEditorModal, SettingsPanel) |
| Frontend Swiper bundle | `src/frontend/index.js` + `style.scss` |

## License

GPL-2.0-or-later. See `LICENSE.txt`.
