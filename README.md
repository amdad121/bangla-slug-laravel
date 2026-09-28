# Bangla Slug - Readable Banglish URL Slugs for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/amdadulhaq/bangla-slug-laravel.svg?style=flat-square)](https://packagist.org/packages/amdadulhaq/bangla-slug-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/amdad121/bangla-slug-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/amdad121/bangla-slug-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/amdad121/bangla-slug-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/amdad121/bangla-slug-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/amdadulhaq/bangla-slug-laravel.svg?style=flat-square)](https://packagist.org/packages/amdadulhaq/bangla-slug-laravel)
[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=flat-square&logo=php)](https://php.net)
[![Laravel Version](https://img.shields.io/badge/Laravel-12%2F13-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Sponsor](https://img.shields.io/badge/Sponsor-%E2%9D%A4-pink?style=flat-square&logo=github)](https://github.com/sponsors/amdad121)

> Turn Bangla titles into clean, readable Banglish slugs — the way people in Bangladesh actually write them.

## Why?

Laravel's `Str::slug()` transliterates Bangla one letter at a time. It drops the vowel every Bangla consonant carries, and it has no idea that `হাসপাতাল` is just "hospital":

| Title | `Str::slug()` | `Str::banglaSlug()` |
| --- | --- | --- |
| সোনার তরী | `sonar-tree` | `sonar-tari` |
| পহেলা বৈশাখ | `phela-boisakh` | `pahela-boishakh` |
| ঢাকা বিশ্ববিদ্যালয় | `dhaka-biswbidzalz` | `dhaka-university` |
| ঢাকা মেডিকেল কলেজ হাসপাতাল | `dhaka-medikel-klej-haspatal` | `dhaka-medical-college-hospital` |
| কক্সবাজার সমুদ্র সৈকত | `kksbajar-smudr-soikt` | `coxs-bazar-samudra-soikat` |
| মোবাইল ব্যাংকিং | `mobail-bzangking` | `mobile-banking` |

This package reads Bangla phonetically, knows ~830 place names and English loanwords, detects acronyms, and leaves English text alone.

## Quick Start

### 1. Install via Composer

```bash
composer require amdadulhaq/bangla-slug-laravel
```

### 2. Generate slugs

```php
use Illuminate\Support\Str;

Str::banglaSlug('আমার সোনার বাংলা'); // amar-sonar-bangla
```

That's it — the service provider, `Str` macro and facade are auto-discovered.

## Features

- **Phonetic Transliteration** - Keeps or drops the inherent vowel the way Bangla is spoken (`কলম` → `kalam`, `করবে` → `karbe`)
- **Conjunct Aware** - Handles ya/ba/ra-phala, doubled consonants, `ক্ষ`, `জ্ঞ`, `ঙ্গ`, anusvara and visarga
- **English Loanwords** - Bangla-spelled English words come out in English (`ক্রিকেট` → `cricket`, `কম্পিউটার` → `computer`)
- **Acronym Detection** - Words spelled from English letter names become acronyms (`বিটিভি` → `btv`, `আইসিইউ` → `icu`)
- **Dotted Initials** - `এ. পি. জে. আবদুল কালাম` → `a-p-j-abdul-kalam`
- **Suffix Handling** - Known words keep their spelling with common suffixes (`ঢাকার` → `dhakar`)
- **Unicode Safe** - Normalizes nukta variants, split `ো`/`ৌ`, zero-width joiners and Bangla digits, and drops malformed UTF-8 bytes without losing the text around them
- **English Untouched** - Text without Bangla gives exactly the same result as `Str::slug()`
- **Length Limit** - Long headlines are cut at a word boundary, never mid-word
- **Configurable** - Add your own words without touching code
- **Developer Tools** - Pint, Pest, Rector, and Larastan included

## Support & Sponsorship

Building and maintaining high-quality open-source packages takes hundreds of hours of dedicated time. If this package saves you time, please consider supporting the project.

> **[Sponsor the Project](https://github.com/sponsors/amdad121)**
> Ensure the package stays actively maintained, receives rapid bug fixes, and continuous feature updates by becoming a monthly sponsor.

## Table of Contents

- [Installation](#installation)
- [Usage](#usage)
    - [Three Ways to Call It](#three-ways-to-call-it)
    - [Unique Slugs](#unique-slugs)
    - [Custom Separator](#custom-separator)
- [Recipes](#recipes)
    - [Eloquent Models](#eloquent-models)
    - [Controllers and APIs](#controllers-and-apis)
    - [Filament Forms](#filament-forms)
    - [Guaranteed-Unique Slugs](#guaranteed-unique-slugs)
    - [Regenerating Existing Slugs](#regenerating-existing-slugs)
- [Configuration](#configuration)
    - [Adding Your Own Words](#adding-your-own-words)
    - [Writing Good Entries](#writing-good-entries)
    - [Length Limit](#length-limit)
- [How It Works](#how-it-works)
- [Examples](#examples)
- [Limitations](#limitations)
- [API Reference](#api-reference)
- [Troubleshooting](#troubleshooting)
- [Development](#development)
- [FAQ](#faq)

## Installation

### Requirements

- **PHP**: 8.3, 8.4, or 8.5
- **Laravel**: 12.x or 13.x
- **Extensions**: `mbstring` (already required by Laravel)

### Install via Composer

```bash
composer require amdadulhaq/bangla-slug-laravel
```

No migrations, no setup. Publishing the config is only needed if you want to [add your own words](#adding-your-own-words).

## Usage

### Three Ways to Call It

All three give the same result.

```php
// 1. Str macro — shortest, works anywhere
use Illuminate\Support\Str;

Str::banglaSlug('পদ্মা সেতু'); // padma-setu

// 2. Facade
use AmdadulHaq\BanglaSlug\Facades\BanglaSlug;

BanglaSlug::generate('একুশে ফেব্রুয়ারি'); // ekushe-february

// 3. Dependency injection — easiest to fake in tests
use AmdadulHaq\BanglaSlug\BanglaSlug;

public function __construct(private BanglaSlug $slugs) {}

$this->slugs->generate('অনলাইন শপিং'); // online-shopping
```

The service is a singleton, so the word list is prepared once per request no matter how many slugs you generate.

### Unique Slugs

`unique()` appends a random number from 1 to 999999, so two posts with the same title get different slugs:

```php
BanglaSlug::unique('ঈদ মোবারক'); // eid-mobarak-482913
BanglaSlug::unique('ঈদ মোবারক'); // eid-mobarak-70215
```

On its own, the random suffix makes a collision very unlikely, not impossible. Pass a closure that says whether a slug is already taken, and `unique()` retries with a new number until it finds a free one:

```php
$slug = BanglaSlug::unique(
    $request->validated('title'),
    fn (string $slug): bool => Post::withTrashed()->where('slug', $slug)->exists(),
);
```

If 100 attempts in a row are all taken — which in practice means the closure always returns `true` — a `RuntimeException` is thrown instead of looping forever. Keep a unique index on the column either way, since two requests at the same instant can still race.

### Custom Separator

```php
BanglaSlug::generate('আমার সোনার বাংলা', '_'); // amar_sonar_bangla
Str::banglaSlug('আমার সোনার বাংলা', '_');       // amar_sonar_bangla
```

## Recipes

### Eloquent Models

Fill the slug automatically when a model is created, and leave it alone on update so published URLs never change by accident:

```php
use AmdadulHaq\BanglaSlug\Facades\BanglaSlug;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Post $post): void {
            $post->slug ??= BanglaSlug::unique(
                $post->title,
                fn (string $slug): bool => Post::query()->where('slug', $slug)->exists(),
            );
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
```

With `getRouteKeyName()`, `Route::get('/posts/{post}', ...)` resolves `/posts/pahela-boishakh-4521` directly.

### Controllers and APIs

```php
use AmdadulHaq\BanglaSlug\BanglaSlug;

public function store(StorePostRequest $request, BanglaSlug $slugs): JsonResponse
{
    $post = Post::create([
        ...$request->validated(),
        'slug' => $slugs->unique(
            $request->validated('title'),
            fn (string $slug): bool => Post::query()->where('slug', $slug)->exists(),
        ),
    ]);

    return PostResource::make($post)->response()->setStatusCode(201);
}
```

### Filament Forms

Fill the slug as the title is typed, only when creating:

```php
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

TextInput::make('title')
    ->required()
    ->live(onBlur: true)
    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create'
        ? $set('slug', Str::banglaSlug($state ?? ''))
        : null),

TextInput::make('slug')
    ->required()
    ->unique(ignoreRecord: true)
    ->alphaDash(),
```

On the edit page, add a suffix action to the slug field so editors can regenerate it on purpose:

```php
use Filament\Actions\Action;
use Filament\Schemas\Components\Utilities\Get;

TextInput::make('slug')
    ->suffixAction(
        Action::make('regenerateSlug')
            ->icon('heroicon-m-arrow-path')
            ->visible(fn (string $operation): bool => $operation !== 'view')
            ->action(fn (Get $get, Set $set) => $set('slug', Str::banglaSlug((string) $get('title')))),
    ),
```

### Guaranteed-Unique Slugs

When you want a clean slug with no number, and a counter only on collision (`khela`, `khela-2`, `khela-3`):

```php
use Illuminate\Support\Str;

function uniqueSlug(string $title, ?int $ignoreId = null): string
{
    $base = Str::banglaSlug($title) ?: 'item';
    $slug = $base;

    for ($counter = 2; Category::query()
        ->where('slug', $slug)
        ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
        ->exists(); $counter++) {
        $slug = "{$base}-{$counter}";
    }

    return $slug;
}
```

### Regenerating Existing Slugs

Moving from `Str::slug()`? Rebuild old slugs in a one-off command, keeping any numeric suffix:

```php
use AmdadulHaq\BanglaSlug\Facades\BanglaSlug;

Post::query()->lazyById()->each(function (Post $post): void {
    $suffix = preg_match('/-(\d+)$/', $post->slug, $matches) ? '-'.$matches[1] : '';
    $slug = BanglaSlug::generate($post->title).$suffix;

    if ($slug !== $post->slug) {
        $post->timestamps = false;
        $post->forceFill(['slug' => $slug])->saveQuietly();
    }
});
```

> **Heads up:** changing slugs changes public URLs. Old links, search results and anything a mobile app cached by slug will stop matching. Consider redirects from old slugs.

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag="bangla-slug-config"
```

`config/bangla-slug.php` has three keys:

| Key | Default | Purpose |
| --- | --- | --- |
| `words` | ~830 entries | Whole Bangla words with a fixed spelling |
| `max_length` | `80` | Slugs are cut at the last whole word within this many characters; `0` disables the limit |
| `suffixes` | `ের`, `এর`, `তে`, `কে`, `র`, `ে` | Endings still matched after a known word |

### Adding Your Own Words

Open the published file and add entries to `words`:

```php
'words' => [
    // ... the package's defaults ...

    // Your project
    'মিরপুর' => 'mirpur',
    'জরুরি' => 'emergency',
    'ফুডপান্ডা' => 'foodpanda',
],
```

> **Note:** Laravel merges package config one level deep, so a published `words` array **replaces** the package defaults instead of adding to them. The published file already contains every default word, so keep them in place and add yours below. After upgrading the package, re-publish (`--force`) or copy any new default words across.

Values may contain hyphens for multi-word spellings, for example `'বাসস্ট্যান্ড' => 'bus-stand'`.

### Writing Good Entries

- **One word per key.** Titles are matched word by word, so a key with a space (`'উচ্চ বিদ্যালয়'`) never matches; add each word on its own. Entries also match whole words only, so `'কাল' => 'kal'` will never break `কালিয়াকৈর`.
- **Skip suffixed forms.** Add `ঢাকা` once; `ঢাকার`, `ঢাকাতে` and `ঢাকাকে` are matched through `suffixes`.
- **Add each common spelling.** Bangla often has more than one spelling (`একাডেমি`/`একাডেমী`, `গ্রিন`/`গ্রীন`). Add each one you see in your data.
- **Don't worry about Unicode forms.** `য়`, `ড়`, `ঢ়` typed as one character or as letter plus nukta, and `অ্যা` vs `এ্যা`, are normalized before lookup.
- **Leave acronyms out.** `বিটিভি`, `আইসিইউ` and similar are detected automatically.

### Length Limit

Slugs longer than `max_length` are cut at the last separator that fits, so no word is left half-spelled:

```php
config(['bangla-slug.max_length' => 20]);

Str::banglaSlug('বাংলাদেশ ক্রিকেট দলের নতুন অধিনায়ক'); // bangladesh-cricket
```

The random suffix from `unique()` is added after the limit.

## How It Works

Digits are converted and Unicode is normalized first. Then each Bangla word goes through these steps; the first match wins:

1. **Dotted initial** — a single letter name followed by a dot (`আর.` → `r`)
2. **Known word** — the `words` config, with or without a known suffix
3. **Acronym** — a word made only of two or more English letter names (`বিটিভি` → `btv`). A lone letter name like `আর` or `কে` stays a word, since those are real Bangla words too
4. **Phonetic transliteration** — the word is split into syllables, then the inherent vowel is kept or dropped:
    - kept at the start of a word (`কলম` → **ka**lam)
    - dropped at the end of a word (`কলম` → kala**m**)
    - dropped between two voiced syllables (`করবে` → `karbe`)
    - kept at the end after a phala or doubled consonant (`কেন্দ্র` → `kendra`, `অন্ন` → `anna`)
    - kept before a conjunct (`কর্মকর্তা` → `karmakarta`)

The result then goes through Laravel's `Str::slug()` and the length limit. English text never reaches steps 1–4.

## Examples

| Bangla | Slug |
| --- | --- |
| আমার সোনার বাংলা | `amar-sonar-bangla` |
| পহেলা বৈশাখ | `pahela-boishakh` |
| একুশে ফেব্রুয়ারি | `ekushe-february` |
| ঈদ মোবারক ২০২৬ | `eid-mobarak-2026` |
| রবীন্দ্রনাথ ঠাকুর | `rabindranath-thakur` |
| কাজী নজরুল ইসলাম | `kazi-nazrul-islam` |
| পদ্মা সেতু | `padma-setu` |
| কক্সবাজার সমুদ্র সৈকত | `coxs-bazar-samudra-soikat` |
| ঢাকা বিশ্ববিদ্যালয় | `dhaka-university` |
| বাংলা একাডেমি | `bangla-academy` |
| বাংলাদেশ ক্রিকেট দল | `bangladesh-cricket-dal` |
| মোবাইল ব্যাংকিং | `mobile-banking` |
| জাতীয় পরিচয়পত্র | `jatiyo-parichayapatra` |
| বিটিভি | `btv` |
| এ. পি. জে. আবদুল কালাম | `a-p-j-abdul-kalam` |
| Hello বাংলাদেশ! | `hello-bangladesh` |

## Limitations

- **Transliteration, not translation.** Bangla words stay Banglish (`জরুরি` → `jaruri`). Only words in `words` get an English spelling.
- **English brand names need entries.** A Bangla-spelled brand not in `words` comes out phonetically (`ফুডপান্ডা` → `fudpanda`, not `foodpanda`). No rule can recover the original English spelling.
- **One spelling per vowel.** The inherent vowel is always written `a` (`কলম` → `kalam`). Words written with `o` in everyday Banglish (`খবর` → `khobor`) come from the word list.
- **Rule-based.** Bangla pronunciation has exceptions; words that come out oddly can be fixed with an entry in `words`.

## API Reference

### `BanglaSlug::generate(string $text, string $separator = '-'): string`

Builds a slug from Bangla, English or mixed text. Text without Bangla gives the same result as `Str::slug()`. Returns an empty string when there is nothing to slug (empty text, only punctuation or emoji).

### `BanglaSlug::unique(string $text, ?Closure $exists = null): string`

Same as `generate()` plus `-` and a random number from 1 to 999999. If the text gives an empty slug (only punctuation or emoji), the result is just the number. With `$exists`, retries with a new number while `$exists($slug)` returns `true`; throws `RuntimeException` after 100 taken attempts.

### `Str::banglaSlug(string $text, string $separator = '-'): string`

Macro for `generate()`.

## Troubleshooting

<details>
<summary><strong>Call to undefined method Illuminate\Support\Str::banglaSlug()</strong></summary>

The service provider isn't loaded. If you disabled package discovery, register it in `bootstrap/providers.php`:

```php
AmdadulHaq\BanglaSlug\BanglaSlugServiceProvider::class,
```

</details>

<details>
<summary><strong>My new word has no effect</strong></summary>

Clear the config cache so the published file is read again:

```bash
php artisan config:clear
```

Also make sure the key is the whole word as it appears in the title, without surrounding punctuation.

</details>

<details>
<summary><strong>New default words are missing after an upgrade</strong></summary>

Your published `config/bangla-slug.php` replaces the package's word list. Re-publish with `--force` (then re-add your own words), or copy the new entries across.

</details>

## Development

### Code Quality Tools

```bash
# Rector (code refactoring)
composer refactor
composer refactor:check

# Laravel Pint (code style)
composer lint
composer lint:check

# Pest (testing)
composer test
composer test-coverage

# Larastan (static analysis)
composer analyse
```

### Adding a Word to the Package

Add the entry to `config/bangla-slug.php` under the matching section (places, institutions, loanwords…). If it fixes a rule rather than a single word, add a case to `tests/BanglaSlugTest.php`.

## FAQ

<details>
<summary><strong>Why not just use <code>Str::slug()</code>?</strong></summary>

`Str::slug()` transliterates letter by letter and ignores the inherent vowel, so `কলম` becomes `klm`. This package reads Bangla phonetically. See [Why?](#why).

</details>

<details>
<summary><strong>Will it change my existing English slugs?</strong></summary>

No. Text without Bangla characters produces exactly the same slug as `Str::slug()`.

</details>

<details>
<summary><strong>Does it translate Bangla words to English?</strong></summary>

No. Bangla words are transliterated (`জরুরি` → `jaruri`). Only words in the `words` config get a fixed English spelling.

</details>

<details>
<summary><strong>Can I use it for non-slug text, like filenames?</strong></summary>

Yes. Pass `_` or `-` as the separator; the output is always lowercase `a-z`, `0-9` and the separator.

</details>

<details>
<summary><strong>Is it fast enough for bulk imports?</strong></summary>

Yes. It is pure string work with no database or network calls, and the word list is prepared once per request.

</details>

## Contributing

We welcome contributions! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Changelog

See [CHANGELOG](CHANGELOG.md) for recent changes.

## Security

Please review [our security policy](../../security/policy) for reporting vulnerabilities.

## Credits

![Contributors](https://contrib.rocks/image?repo=amdad121/bangla-slug-laravel)

## License

The MIT License (MIT). See [License File](LICENSE.md) for details.

---

<p align="center">Made with ❤️ for the Laravel community</p>
