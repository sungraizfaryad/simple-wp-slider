# WordPress.org assets

These files are uploaded to the WP.org SVN `/assets/` directory after plugin approval,
NOT inside the plugin ZIP. The `.distignore` excludes this directory from the ZIP.

## Required files

- `banner-1544x500.png` — top of plugin listing (high-res) — **generated**
- `banner-772x250.png` — small variant — **generated**
- `icon-256x256.png` — square icon (high-res) — **generated**
- `icon-128x128.png` — small variant — **generated**
- `screenshot-1.png` … `screenshot-N.png` — referenced in readme.txt screenshots section

Banner + icon were generated via Google Imagen 4.0 (sync mode, $0.04 total) on 2026-05-22.

Screenshots TBD — captured from a live install of the plugin with seeded slides.

## How to deploy to SVN

After tag `v2.0.0` is pushed to GitHub:

```bash
mkdir -p ~/svn && cd ~/svn
svn co https://plugins.svn.wordpress.org/simple-wp-slider/ simple-wp-slider-svn
cd simple-wp-slider-svn
# copy banner/icon/screenshots to assets/
cp /Users/sungraizfaryad/Local\ Sites/media-usage-inspector/app/public/wp-content/plugins/simple-wp-slider/.wordpress-org/*.png assets/
svn add --force assets/
svn ci -m "Release 2.0.0 banner + icon"
```
