# .well-known/slashpages

[Slash pages](https://slashpages.net/) like `/about`, `/now` or `/uses` are great, but there is no way for a tool to find them, other than guessing the URL.

This plugin lists the slash pages of a WordPress site as an [OPML](http://opml.org/) document at `/.well-known/slashpages` (and `/.well-known/slashpages.opml`), following the [`.well-known/slashpages` spec](https://github.com/pfefferle/well-known-slashpages).

## Settings

Under *Settings → Slash Pages* you find all slash pages listed on slashpages.net. For every one you can choose:

- a page of your site. If you have a page with the same slug (for example `now`), it is already selected.
- a custom URL, for pages that are not WordPress pages.
- nothing, then it is not listed.

## In the browser

The OPML comes with a small script, so it is also readable in a browser. It is the same approach as in [.well-known/feeds](https://github.com/pfefferle/wordpress-well-known-feeds), without XSLT.

## Filters

- `well_known_slashpages`: the slash pages that can be set on the settings page.
- `well_known_slashpages_links`: the slash pages before they are served.

## Development

```bash
npm install
npx wp-env start
composer install
composer test:wp-env
composer lint
```
