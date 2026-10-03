<?php

declare(strict_types=1);

namespace AmdadulHaq\BanglaSlug;

use Closure;
use Illuminate\Support\Str;
use RuntimeException;

class BanglaSlug
{
    private const int MAX_UNIQUE_ATTEMPTS = 100;

    private const string HASANTA = '্';

    private const string INHERENT_VOWEL = 'a';

    /** @var array<string, string> */
    private const array CONSONANTS = [
        'ক' => 'k', 'খ' => 'kh', 'গ' => 'g', 'ঘ' => 'gh', 'ঙ' => 'ng',
        'চ' => 'ch', 'ছ' => 'chh', 'জ' => 'j', 'ঝ' => 'jh', 'ঞ' => 'n',
        'ট' => 't', 'ঠ' => 'th', 'ড' => 'd', 'ঢ' => 'dh', 'ণ' => 'n',
        'ত' => 't', 'থ' => 'th', 'দ' => 'd', 'ধ' => 'dh', 'ন' => 'n',
        'প' => 'p', 'ফ' => 'f', 'ব' => 'b', 'ভ' => 'bh', 'ম' => 'm',
        'য' => 'j', 'র' => 'r', 'ল' => 'l', 'শ' => 'sh', 'ষ' => 'sh',
        'স' => 's', 'হ' => 'h', "\u{09DC}" => 'r', "\u{09DD}" => 'rh', "\u{09DF}" => 'y',
        'ৰ' => 'r', 'ৱ' => 'w',
    ];

    /** @var array<string, string> */
    private const array VOWELS = [
        'অ' => 'a', 'আ' => 'a', 'ই' => 'i', 'ঈ' => 'i', 'উ' => 'u', 'ঊ' => 'u',
        'ঋ' => 'ri', 'এ' => 'e', 'ঐ' => 'oi', 'ও' => 'o', 'ঔ' => 'ou',
    ];

    /** @var array<string, string> */
    private const array VOWEL_SIGNS = [
        'া' => 'a', 'ি' => 'i', 'ী' => 'i', 'ু' => 'u', 'ূ' => 'u', 'ৃ' => 'ri',
        'ে' => 'e', 'ৈ' => 'oi', 'ো' => 'o', 'ৌ' => 'ou',
    ];

    /**
     * Sounds that close a syllable and never carry a vowel of their own.
     *
     * @var array<string, string>
     */
    private const array CODAS = ['ং' => 'ng', 'ঃ' => '', 'ৎ' => 't'];

    /**
     * Conjuncts whose letter-by-letter reading differs from how they are spoken.
     *
     * @var array<string, string>
     */
    private const array CONJUNCTS = ['ক্ষ' => 'kkh', 'জ্ঞ' => 'gg', 'ঙ্গ' => 'ng', 'ঙ্ক' => 'nk'];

    /** @var array<string, string> */
    private const array DIGITS = [
        '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
        '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9',
    ];

    /**
     * য়, ড় and ঢ় can be typed as one code point or as the base letter plus a nukta, ো and ৌ as
     * ে followed by া or ৗ, and অ্যা/এ্যা spell the English "a" sound (অ্যান্ড → and); every
     * spelling must read the same.
     *
     * @var array<string, string>
     */
    private const array NORMALIZE = [
        "\u{09A1}\u{09BC}" => "\u{09DC}", "\u{09A2}\u{09BC}" => "\u{09DD}", "\u{09AF}\u{09BC}" => "\u{09DF}",
        "\u{09C7}\u{09BE}" => "\u{09CB}", "\u{09C7}\u{09D7}" => "\u{09CC}",
        "\u{200C}" => '', "\u{200D}" => '', 'অ্যা' => 'আ', 'এ্যা' => 'আ',
    ];

    /**
     * Bangla spellings of English letter names, already normalized (য় is U+09DF).
     *
     * @var array<string, string>
     */
    private const array LETTER_NAMES = [
        'ডব্লিউ' => 'w', "ও\u{09DF}াই" => 'y', 'এইচ' => 'h', 'এক্স' => 'x', 'কিউ' => 'q',
        'এফ' => 'f', 'জেড' => 'z', 'এল' => 'l', 'এম' => 'm', 'এন' => 'n', 'এস' => 's',
        'আই' => 'i', 'আর' => 'r', 'ইউ' => 'u', 'বি' => 'b', 'সি' => 'c', 'ডি' => 'd',
        'জি' => 'g', 'জে' => 'j', 'কে' => 'k', 'পি' => 'p', 'টি' => 't', 'ভি' => 'v',
        'এ' => 'a', 'ই' => 'e', 'ও' => 'o',
    ];

    /**
     * Letter names that are not Bangla words, so they read as a letter even on their own (এম → m).
     *
     * @var list<string>
     */
    private const array UNAMBIGUOUS_LETTER_NAMES = ['ডব্লিউ', 'এইচ', 'এক্স', 'কিউ', 'এফ', 'জেড', 'এল', 'এম', 'এন', 'এস'];

    /** @var list<string> */
    private const array PHALAS = ['য', 'ব', 'র'];

    /**
     * Consonants that end native conjuncts, which stay voiced at the end of a word (আনন্দ → ananda);
     * English loanwords end in the others (পোস্ট → post).
     *
     * @var list<string>
     */
    private const array VOICED_FINALS = ['ত', 'থ', 'দ', 'ধ', 'ন', 'ণ', 'ঠ', 'ঢ', 'শ', 'ষ', 'গ', 'ঘ', 'ভ', 'ম'];

    /** @var array<string, string> */
    private readonly array $words;

    /**
     * @param  array<string, string>  $words  whole Bangla words with a fixed spelling
     * @param  array<string, string>  $suffixes  endings still matched after a known word
     * @param  int  $maxLength  slugs are cut at the last whole word within this many characters; 0 or less disables the limit
     */
    public function __construct(
        array $words = [],
        private readonly array $suffixes = [],
        private readonly int $maxLength = 80,
    ) {
        $this->words = array_combine(array_map($this->normalize(...), array_keys($words)), $words);
    }

    /**
     * Build a URL slug from Bangla, English or mixed text.
     *
     * Text without Bangla characters gives exactly the same result as Str::slug().
     */
    public function generate(string $text, string $separator = '-'): string
    {
        $text = strtr($this->normalize(mb_scrub($text, 'UTF-8')), self::DIGITS);
        $text = $this->dotInitialsAfterLetterNames($text);

        $transliterated = (string) preg_replace_callback(
            '/([\x{0980}-\x{09FF}]+)([\'’]স(?![\x{0980}-\x{09FF}]))?(\.?)/u',
            fn (array $match): string => ' '.$this->transliterate(
                $match[1][0],
                $match[3][0] === '.' || substr($text, max($match[0][1] - 1, 0), 1) === '.',
            ).($match[2][0] === '' ? '' : 's').' ',
            $text,
            flags: PREG_OFFSET_CAPTURE,
        );

        return $this->limitLength(Str::slug(str_replace('/', ' ', $this->dropRepeatedParentheticals($transliterated)), $separator), $separator);
    }

    /**
     * A run of letter names with at least one that is not a Bangla word is a run of initials
     * (এ কে এম → a k m, not e ke m).
     */
    private function dotInitialsAfterLetterNames(string $text): string
    {
        $bangla = '[\x{0980}-\x{09FF}]';
        $name = '(?:'.implode('|', array_map(preg_quote(...), array_keys(self::LETTER_NAMES))).')';

        return (string) preg_replace_callback(
            "/(?<!{$bangla})(?:{$name}\\.?\\s+)+{$name}(?!{$bangla})/u",
            function (array $match): string {
                $names = preg_split('/\.?\s+/u', $match[0]) ?: [];

                return array_intersect($names, self::UNAMBIGUOUS_LETTER_NAMES) === []
                    ? $match[0]
                    : implode('. ', $names).(str_ends_with($match[0], '.') ? '' : '.');
            },
            $text,
        );
    }

    /**
     * "ইউসিসি (UCC)" reads as "ucc (UCC)"; a bracketed part that repeats the words before it is dropped.
     */
    private function dropRepeatedParentheticals(string $text): string
    {
        return (string) preg_replace_callback(
            '/\(([^()]*)\)/u',
            function (array $match) use ($text): string {
                $inner = Str::slug($match[1][0]);
                $before = Str::slug(substr($text, 0, $match[0][1]));

                return $inner !== '' && ($before === $inner || str_ends_with($before, '-'.$inner)) ? ' ' : $match[0][0];
            },
            $text,
            flags: PREG_OFFSET_CAPTURE,
        );
    }

    /**
     * Build a slug with a random 1–999999 suffix (just the number when the text gives an empty slug).
     *
     * Pass $exists to retry with a new suffix while the slug is taken. Without it the suffix
     * only makes collisions unlikely, so keep a unique index on the column either way.
     *
     * @param  (Closure(string): bool)|null  $exists
     *
     * @throws RuntimeException when every attempt was taken, which means $exists always returns true
     */
    public function unique(string $text, ?Closure $exists = null): string
    {
        $base = $this->generate($text);
        $prefix = $base === '' ? '' : $base.'-';

        for ($attempt = 0; $attempt < self::MAX_UNIQUE_ATTEMPTS; $attempt++) {
            $slug = $prefix.random_int(1, 999999);

            if (! $exists instanceof Closure || ! $exists($slug)) {
                return $slug;
            }
        }

        throw new RuntimeException("Could not find a free slug for [{$base}] after ".self::MAX_UNIQUE_ATTEMPTS.' attempts.');
    }

    /**
     * First match wins: a dotted initial (আর. → r, ডি.পি → d p), a known word (with or without a suffix),
     * an acronym of two or more letter names (জিএমপি → gmp), then phonetic transliteration.
     * A lone letter name is not an acronym, since most are also real words (আর, কে, ও).
     */
    private function transliterate(string $word, bool $isDotted): string
    {
        $letters = $this->spellLetterNames($word);

        if ($letters !== null && strlen($letters) === 1 && ($isDotted || in_array($word, self::UNAMBIGUOUS_LETTER_NAMES, true))) {
            return $letters;
        }

        return $this->knownSpelling($word)
            ?? ($letters !== null && strlen($letters) > 1 ? $letters : null)
            ?? $this->transliteratePhonetically($word);
    }

    private function knownSpelling(string $word): ?string
    {
        if (isset($this->words[$word])) {
            return $this->words[$word];
        }

        foreach ($this->suffixes as $suffix => $spelling) {
            if (str_ends_with($word, $suffix)) {
                $stem = substr($word, 0, -strlen($suffix));

                if (isset($this->words[$stem])) {
                    return $this->words[$stem].$spelling;
                }
            }
        }

        return null;
    }

    /**
     * Reads the word as a run of English letter names, backtracking where a shorter name fits better (এসি = এ + সি, not এস + ি).
     */
    private function spellLetterNames(string $text): ?string
    {
        if ($text === '') {
            return '';
        }

        foreach (self::LETTER_NAMES as $name => $letter) {
            if (str_starts_with($text, $name)) {
                $rest = $this->spellLetterNames(substr($text, strlen($name)));

                if ($rest !== null) {
                    return $letter.$rest;
                }
            }
        }

        return null;
    }

    /**
     * Keeps the inherent vowel at the start of a word, before conjuncts and after a final phala or
     * doubled consonant; drops it at the end of a word and between two voiced syllables (করবে → karbe).
     */
    private function transliteratePhonetically(string $word): string
    {
        $syllables = $this->syllables(mb_str_split($word));
        $lastIndex = count($syllables) - 1;
        $result = '';

        foreach ($syllables as $index => $syllable) {
            if ($syllable['vowel'] === null) {
                $next = $syllables[$index + 1] ?? null;

                $dropsVowel = $index > 0 && ($index === $lastIndex
                    ? ! $syllable['keepsFinalVowel']
                    : ! $syllable['conjunct']
                        && $syllables[$index - 1]['voiced']
                        && $next !== null && $next['vowel'] !== null && ! $next['coda'] && ! $next['conjunct']);

                $syllable['vowel'] = $dropsVowel ? '' : self::INHERENT_VOWEL;
                $syllables[$index]['voiced'] = ! $dropsVowel;
            }

            $result .= $syllable['sound'].$syllable['vowel'];
        }

        return str_replace('ngk', 'nk', $result);
    }

    /**
     * @param  list<string>  $chars
     * @return list<array{sound: string, vowel: ?string, voiced: bool, conjunct: bool, coda: bool, keepsFinalVowel: bool}>
     */
    private function syllables(array $chars): array
    {
        $syllables = [];
        $count = count($chars);

        for ($position = 0; $position < $count; $position++) {
            $char = $chars[$position];

            if (isset(self::VOWELS[$char])) {
                $syllables[] = $this->syllable('', self::VOWELS[$char]);

                continue;
            }

            if (isset(self::CODAS[$char])) {
                $syllables[] = $this->syllable(self::CODAS[$char], '', coda: true);

                continue;
            }

            if (! isset(self::CONSONANTS[$char])) {
                continue;
            }

            $cluster = [$char];

            while (($chars[$position + 1] ?? null) === self::HASANTA) {
                $next = $chars[$position + 2] ?? null;

                if ($next === null || ! isset(self::CONSONANTS[$next])) {
                    $position++;
                    $syllables[] = $this->syllable($this->clusterSound($cluster), '');

                    continue 2;
                }

                $cluster[] = $next;
                $position += 2;
            }

            $vowel = self::VOWEL_SIGNS[$chars[$position + 1] ?? ''] ?? null;

            if ($vowel !== null) {
                $position++;
            }

            $syllables[] = $this->syllable(
                $this->clusterSound($cluster, $vowel, isWordInitial: $syllables === []),
                $vowel,
                conjunct: count($cluster) > 1,
                keepsFinalVowel: $this->keepsFinalVowel($cluster),
            );
        }

        return $syllables;
    }

    /**
     * @return array{sound: string, vowel: ?string, voiced: bool, conjunct: bool, coda: bool, keepsFinalVowel: bool}
     */
    private function syllable(string $sound, ?string $vowel, bool $conjunct = false, bool $coda = false, bool $keepsFinalVowel = false): array
    {
        return [
            'sound' => $sound,
            'vowel' => $vowel,
            'voiced' => $vowel !== '' && ! $coda,
            'conjunct' => $conjunct,
            'coda' => $coda,
            'keepsFinalVowel' => $keepsFinalVowel,
        ];
    }

    /**
     * Word-final phala, doubled-consonant and native conjunct clusters are still voiced (কেন্দ্র → kendra, অন্ন → anna, স্বপ্ন → swapna).
     *
     * @param  list<string>  $cluster
     */
    private function keepsFinalVowel(array $cluster): bool
    {
        [$previous, $last] = array_slice([null, ...$cluster], -2);

        return $previous !== null && (
            in_array($last, self::PHALAS, true)
            || in_array($last, self::VOICED_FINALS, true)
            || $last === $previous
            || in_array($previous.$last, ['চছ', 'কষ', 'লপ'], true)
        );
    }

    /**
     * A word-initial য-phala is silent before া (ব্যাংক → bank) and a y-glide otherwise (ব্যবসা → byabsa, বিদ্যালয় → bidyalay);
     * a trailing ব-phala is a w-glide (স্বাধীন → swadhin) except after ম, where it is a real b (অম্বর).
     *
     * @param  list<string>  $cluster
     */
    private function clusterSound(array $cluster, ?string $vowel = null, bool $isWordInitial = false): string
    {
        $sound = '';

        for ($index = 0, $count = count($cluster); $index < $count; $index++) {
            $conjunct = self::CONJUNCTS[$cluster[$index].self::HASANTA.($cluster[$index + 1] ?? '')] ?? null;

            if ($conjunct !== null) {
                $sound .= $isWordInitial && $index === 0 && $conjunct === 'kkh' ? 'kh' : $conjunct;
                $index++;

                continue;
            }

            $sound .= match (true) {
                $index === 0 => self::CONSONANTS[$cluster[$index]],
                $cluster[$index] === 'য' => $isWordInitial && $vowel === 'a' ? '' : 'y',
                $cluster[$index] === 'ব' && ! in_array($cluster[$index - 1], ['ম', 'র', 'ব', 'ল'], true) => 'w',
                default => self::CONSONANTS[$cluster[$index]],
            };
        }

        return $sound;
    }

    private function normalize(string $text): string
    {
        return strtr($text, self::NORMALIZE);
    }

    /**
     * Long headlines would otherwise produce unwieldy URLs; cut at a word boundary so no word is left half-spelled.
     */
    private function limitLength(string $slug, string $separator): string
    {
        if ($this->maxLength < 1 || strlen($slug) <= $this->maxLength) {
            return $slug;
        }

        $lastSeparator = $separator === '' ? false : strrpos(substr($slug, 0, $this->maxLength + 1), $separator);

        return substr($slug, 0, $lastSeparator ?: $this->maxLength);
    }
}
