_Last updated: 2026-05-23._
_Detail lives in `CLAUDE.md` and cloud memory `project_simple_wp_slider_shipped_state.md`._

## Done

- v2.0.0 shipped to WP.org SVN (trunk r3544385, assets r3544388, tag r3544389).
- Full v2 rewrite: `swps_slider` CPT, JSON post meta, REST namespace `swps/v1`, Swiper.js 11 frontend, React admin metabox, Gutenberg block, YT + Vimeo facades, idempotent v1 to v2 migrator, real uninstall cleanup, 41 PHPUnit tests passing, phpcs / eslint / stylelint clean.
- Smoke-test fixes: admin + frontend CSS enqueue corrected, Publish auto-saves React state via `form#post` submit hook, `README.txt` renamed to lowercase `readme.txt` for SVN.
- Banner, icon, and screenshots 1 through 5 committed to `.wordpress-org/` and now live on the WP.org plugin page.

## Decisions

- Slug stays `simple-wp-slider`. The plugin was already published under that slug in v1; renaming would break installed users.
- Slides + settings live in post meta as JSON, not as child posts. Simpler payload, one DB row, easy REST shape.
- Frontend uses Swiper.js 11. Slick is archived and unmaintained.
- YouTube + Vimeo embeds use a click-to-load facade and `youtube-nocookie.com`. No third-party requests on page load.
- REST `swps/v1` is admin-only, cap-checked per slider post via `edit_post`. Not opened to subscribers even read-only.
- v1 to v2 migration runs silently on `plugins_loaded` priority 5. No admin click required; safe to re-run.

## Next steps

- Awaiting next instruction. Working tree is clean.
- If a 2.0.1 patch is needed: bump `Version:` in `simple-wp-slider.php`, `Stable tag:` and `Tested up to:` in `readme.txt`, add changelog entry, `npm run build`, commit, `git tag X.Y.Z`, rsync to `~/Local Sites/plugins/simple-wp-slider/` (excluding dev files), re-tag there, run `deploy.sh` per `~/Local Sites/plugins/DEPLOY.md`.

## Key files

- `includes/class-swps-plugin.php` (bootstrap, most-edited)
- `simple-wp-slider.php` (header, version)
- `includes/class-swps-rest.php` (REST routes)
- `assets/dist/admin/index.js` (compiled admin bundle)
- `readme.txt` (WP.org listing)
- `src/admin/SliderProvider.js` (React state + auto-save hook)
