# Simple WP Slider — session orientation

Load only when working in this folder. New sessions should read this first so they do not have to re-derive intent from source.

## 1. What this plugin is

- WP.org name: **Simple WP Slider** (https://wordpress.org/plugins/simple-wp-slider)
- Folder: `simple-wp-slider/`
- Slug: `simple-wp-slider`
- Text domain: `simple-wp-slider`
- PHP prefix: `SWPS_` for classes and constants, `swps_` for functions, options, hooks
- GitHub: https://github.com/sungraizfaryad/simple-wp-slider
- Current version: **2.0.0** (shipped to WP.org SVN on 2026-05-23)

Multi-slider plugin for WordPress. Each slider is a Custom Post Type (`swps_slider`) holding a JSON list of slides (image, self-hosted video, YouTube, Vimeo) and per-slider Swiper.js settings. Renders on the frontend through the `[simplewpslider]` shortcode or a Gutenberg block, both backed by the same server-side renderer. Privacy-friendly YouTube/Vimeo facades activate only on click.

## 2. Repo layout

Two physical copies of the plugin exist on this Mac.

- **Canonical dev repo (this folder)** — `~/Local Sites/media-usage-inspector/app/public/wp-content/plugins/simple-wp-slider/`. Has `.git/`, full source (`src/`, `tests/`, `node_modules/`, `vendor/`), and is wired to GitHub. `main` branch is the one shipped. This is where you edit.
- **Deploy stage** — `~/Local Sites/plugins/simple-wp-slider/`. Fresh git init, only the 45 runtime files needed for WP.org SVN, single commit and a `2.0.0` tag. Rebuilt by `rsync` from this folder before each SVN release. Never edit it by hand — it gets overwritten.

There is no separate "build zip" target. WP.org is the distribution channel. The SVN deploy is run from `~/Local Sites/plugins/deploy.sh` against the stage folder. See [[reference_wp_plugin_svn_deploy_guide]] for the deploy flow.

## 3. Don't trip these mines

Specific to this plugin. Pull from `git log --grep=fix` + smoke-test discoveries.

- **`webpack` emits `style-index.css`, not `index.css`.** The admin enqueue must point at `style-index.css` (single file). The frontend enqueue must load BOTH `index.css` (Swiper base from JS imports) AND `style-index.css` (scss aspect-ratio + caption rules). Get this wrong and the slider renders without a 16:9 frame or stacks vertically without horizontal layout. See `admin/class-swps-admin.php` and `includes/class-swps-assets.php`.
- **Classic Publish/Update does not save slides on its own.** Slides and settings live in React state inside the metabox. The classic WP `<form id="post">` submit is intercepted in `src/admin/SliderProvider.js` to call the REST `saveSlider()` first, then `form.submit()`. If you rip that `useEffect` out, every Publish click silently throws the slider state away.
- **`aspect_ratio` whitelist uses colons.** Valid values are `auto`, `16:9`, `4:3`, `1:1`. Sending `16x9` or `16/9` gets silently coerced to `auto`. See `class-swps-sanitizer.php::ASPECTS`.
- **Sanitizer preserves two underscore-prefixed fields.** `_legacy_url` (used by the v1 migrator) and `_vimeo_thumb` (set by the admin's oembed-resolve call). Do not strip them when refactoring `sanitize_slide()`.
- **`_blank` link targets get `noopener noreferrer` auto-appended.** Dedupe runs via `array_unique()` after the force-append. Don't add a second dedupe loop or you'll break the dedup test.
- **WP.org SVN is case-sensitive.** The readme must be lowercase `readme.txt`. macOS is case-insensitive by default so this is invisible until SVN rejects the deploy. The file was renamed via `git mv README.txt readme-temp.txt && git mv readme-temp.txt readme.txt` to land it on disk as lowercase.
- **REST namespace is admin-only.** `swps/v1/sliders/{id}` cap-checks `edit_post` per object. Anonymous reads return 403, by design. Do not relax this to `read` to "make oembed work."

## 4. How to develop here (Local by Flywheel)

Site name: **media-usage-inspector**. Local install is at `~/Local Sites/media-usage-inspector/app/public/`. Admin login is `admin` / `admin` (HTTP only, dev site).

WP-CLI socket recipe (dynamic run-id, dynamic PHP path):

```bash
PHP="/Users/sungraizfaryad/Library/Application Support/Local/lightning-services/php-8.4.18+1/bin/darwin-arm64/bin/php"
WP="/opt/homebrew/Cellar/wp-cli/2.12.0/bin/wp"
RUN="/Users/sungraizfaryad/Library/Application Support/Local/run"
SITE_RID() { grep -rl "$1" "$RUN"/*/conf 2>/dev/null | sed -E "s#$RUN/([^/]+)/.*#\1#" | sort -u | head -1; }
SOCK="$RUN/$(SITE_RID media-usage-inspector)/mysql/mysqld.sock"
$PHP -d mysqli.default_socket="$SOCK" -d pdo_mysql.default_socket="$SOCK" \
    $WP --path="/Users/sungraizfaryad/Local Sites/media-usage-inspector/app/public" plugin list
```

Browser smoke-test URL: http://media-usage-inspector.local/wp-admin/. Use Playwright if available. Slider admin: Sliders menu → Add New Slider.

Rebuild after JS or scss changes: `npm run build`. Watch mode: `npm start`.

## 5. Architecture map

| File | Responsibility |
|---|---|
| `simple-wp-slider.php` | Header, defines `SWPS_*` constants, hooks `SWPS_Plugin::instance` on `plugins_loaded` |
| `includes/class-swps-plugin.php` | Singleton bootstrap. Instantiates every subsystem on `plugins_loaded` |
| `includes/class-swps-cpt.php` | Registers the `swps_slider` Custom Post Type |
| `includes/class-swps-meta.php` | Registers `_swps_slides` + `_swps_settings` + `_swps_schema_version` post meta with REST exposure |
| `includes/class-swps-sanitizer.php` | Pure static sanitizers for settings + slide records. Whitelists, preserves `_legacy_url` + `_vimeo_thumb` |
| `includes/class-swps-rest.php` | REST namespace `swps/v1`. Admin-gated CRUD for sliders, oembed-resolve, dismissable-notice routes |
| `includes/class-swps-renderer.php` | Builds Swiper-compatible HTML for a slider. WAI-ARIA carousel pattern |
| `includes/class-swps-shortcode.php` | `[simplewpslider]` with v1 back-compat via `swps_legacy_default_slider` option |
| `includes/class-swps-assets.php` | Conditional frontend enqueue. Marks usage from the renderer, enqueues Swiper bundle + CSS only when needed |
| `includes/class-swps-block.php` | Gutenberg block + `hero-slider` starter pattern + Sliders pattern category |
| `includes/class-swps-video.php` | YouTube + Vimeo URL parser, thumbnail helper |
| `includes/class-swps-migrator.php` | Detects legacy `wpss_basics` option from v1 and converts it to a Default Slider CPT post. Idempotent |
| `includes/class-swps-i18n.php` | Loads text domain |
| `admin/class-swps-admin.php` | Slides metabox host (`#swps-admin-root` mount), admin bundle enqueue, dismissable migration notice |
| `src/admin/*` | React metabox: SliderProvider, SlideManager, SlideEditorModal, SettingsPanel, SlideList |
| `src/block/*` | Block edit / save / render delegating to `SWPS_Renderer` |
| `src/frontend/*` | Swiper initialization, prefers-reduced-motion handling, YT/Vimeo facade activation |
| `uninstall.php` | Removes all swps_slider posts, options, transients, user meta on plugin delete |

## 6. Tests

PHPUnit (41 tests). Requires the WP test suite at `${WP_TESTS_DIR}` or the default `${TMPDIR}wordpress-tests-lib/`.

```bash
vendor/bin/phpunit
npm run lint:js
npm run lint:css
vendor/bin/phpcs --standard=phpcs.xml.dist
```

All four were green at the 2.0.0 release.

## 7. Build & ship

Build artifacts: `npm run build` writes to `assets/dist/{admin,block,frontend}/`. These ARE committed.

SVN deploy to WP.org: do not build a zip here. Stage to `~/Local Sites/plugins/simple-wp-slider/` (strip `src/`, `node_modules/`, `vendor/`, `tests/`, `docs/`, dev configs via `rsync --exclude`), then run `~/Local Sites/plugins/deploy.sh` from the parent directory. Full guide: `~/Local Sites/plugins/DEPLOY.md`.

The exact non-interactive invocation:

```bash
cd ~/Local\ Sites/plugins
rm -rf /tmp/simple-wp-slider
printf 'simple-wp-slider\n\n\n\n\n\n\ny\n' | ./deploy.sh
```

Seven `\n` between the slug and the `y`. Six newlines is a real footgun — Q7 (SVN username) eats the `y` and auth fails.

`.distignore` lists what to exclude from any tarball. `deploy.sh` ignores it and reads its own `.svnignore` (or falls back to a default list).

## 8. When in doubt

- `progress.md` in this folder — short status: done, decisions, next, hot files.
- Cloud memory at `~/.claude/projects/-Users-sungraizfaryad-Local-Sites-media-usage-inspector/memory/` — look for `project_simple_wp_slider_*` and `reference_simple_wp_slider_*`.
- `wp-admin/` and `wp-includes/` are WordPress core — never edit.
