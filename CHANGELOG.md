# Changelog

All notable changes to `bangla-slug-laravel` will be documented in this file.

## Unreleased

- Initial release: phonetic Bangla to Banglish slugs, English loanword and place-name dictionary, acronym and dotted-initial detection, suffix handling, length limit, `Str::banglaSlug()` macro and `BanglaSlug` facade. `unique()` accepts an optional "is this slug taken?" closure and retries with a new suffix.
- Handles split `ো`/`ৌ` (`ে` + `া`/`ৗ`), Assamese `ৰ`/`ৱ`, and malformed UTF-8 without dropping the surrounding Bangla text. `unique()` of empty text returns just the number, and `max_length: 0` disables the limit.
