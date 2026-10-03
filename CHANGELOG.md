# Changelog

All notable changes to `bangla-slug-laravel` will be documented in this file.

## 1.0.3 - 2026-10-03

- A letter name right after a dot is read as an initial even without a trailing dot (এস. ডি.পি → s-d-p).
- Word-initial ক্ষ is `kh` instead of `kkh` (ক্ষেত → khet); added ক্ষুদ্র → khudro and ময়দান → moydan.

## 1.0.2 - 2026-09-29

- Common Bangla words use natural Banglish spellings (গবেষণা → gobeshona, কর্মকর্তা → kormokorta, পূর্ব → purbo, ড. → dr).

## 1.0.1 - 2026-09-29

- A parenthetical that only repeats the preceding words as an acronym, such as "(UCC)", is dropped instead of duplicating the slug.

## 1.0.0

- Initial release: phonetic Bangla to Banglish slugs, English loanword and place-name dictionary, acronym and dotted-initial detection, suffix handling, length limit, `Str::banglaSlug()` macro and `BanglaSlug` facade. `unique()` accepts an optional "is this slug taken?" closure and retries with a new suffix.
- Handles split `ো`/`ৌ` (`ে` + `া`/`ৗ`), Assamese `ৰ`/`ৱ`, and malformed UTF-8 without dropping the surrounding Bangla text. `unique()` of empty text returns just the number, and `max_length: 0` disables the limit.
