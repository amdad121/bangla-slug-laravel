<?php

declare(strict_types=1);

use AmdadulHaq\BanglaSlug\BanglaSlug;
use AmdadulHaq\BanglaSlug\Facades\BanglaSlug as BanglaSlugFacade;
use Illuminate\Support\Str;

test('bangla text is transliterated phonetically', function (string $text, string $expected): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe($expected);
})->with([
    'inherent vowel kept and dropped' => ['সদর কলম করবে', 'sadar-kalam-karbe'],
    'medial schwa dropped between voiced syllables' => ['কলকাতা', 'kalkata'],
    'bangla digits' => ['রাস্তা ২০২৫', 'rasta-2025'],
    'decomposed nukta letters' => ["দুনি\u{09AF}\u{09BC}া", 'duniya'],
    'special conjunct kkh' => ['শিক্ষা প্রতিষ্ঠান', 'shikkha-pratishthan'],
    'unknown words still transliterated' => ['রক্তদান কর্মসূচি', 'raktadan-karmasuchi'],
    'zero-width joiners ignored' => ["র\u{200D}্যাব খবর", 'rab-khobor'],
]);

test('conjuncts and phala follow spoken bangla', function (string $text, string $expected): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe($expected);
})->with([
    'word-initial ya-phala before aa is silent' => ['ব্যাগ', 'bag'],
    'ya-phala otherwise is a y-glide' => ['ব্যবসায়ী', 'byabsayi'],
    'medial ya-phala is voiced' => ['বিদ্যা', 'bidya'],
    'ba-phala as w' => ['স্বাধীন বিশ্ব', 'swadhin-bishwa'],
    'ra-phala ending keeps its vowel' => ['কেন্দ্র', 'kendra'],
    'doubled consonant ending keeps its vowel' => ['অন্ন', 'anna'],
    'vowel kept before a conjunct' => ['কর্মকর্তা', 'karmakarta'],
    'a-phala spelling of english a' => ['অ্যাসিড', 'asid'],
    'standalone a uses the same vowel as the inherent one' => ['অমর', 'amar'],
    'anusvara before ka' => ['ব্যাংক', 'bank'],
]);

test('known words use their fixed spelling, including with suffixes', function (string $text, string $expected): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe($expected);
})->with([
    'place and loanwords' => ['গাজীপুর সদর হাসপাতাল', 'gazipur-sadar-hospital'],
    'known word with suffix' => ['গাজীপুরের সেরা ক্লিনিক', 'gazipurer-sera-clinic'],
    'short known word does not split a longer word' => ['আজমপুর', 'ajampur'],
    'loanword spelled with a-phala' => ['অ্যাম্বুলেন্স', 'ambulance'],
]);

test('english letter names are read as acronyms and initials', function (string $text, string $expected): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe($expected);
})->with([
    'acronyms' => ['জরুরি এসি অ্যান্ড ফ্রিজিং অ্যাম্বুলেন্স ২৪/৭', 'jaruri-ac-and-freezing-ambulance-24-7'],
    'multi-letter acronym' => ['আইসিইউ এমসিডব্লিউসি', 'icu-mcwc'],
    'known word wins over acronym' => ['জিএমপি সিটি', 'gmp-city'],
    'dotted initials' => ['আর. এম. বিদ্যাপীঠ', 'r-m-bidyapith'],
    'lone letter name stays a word' => ['আর', 'ar'],
]);

test('a bracketed name that repeats the words before it is dropped', function (string $text, string $expected): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe($expected);
})->with([
    'english acronym after its bangla spelling' => ['ইউসিসি (UCC) বিশ্ববিদ্যালয় ভর্তি কোচিং', 'ucc-university-bharti-coaching'],
    'english brand after its bangla spelling' => ['মানিগ্রাম (MoneyGram) সার্ভিস', 'moneygram-service'],
    'different bracketed text is kept' => ['রাজা রাজেন্দ্র নারায়ণ (আর.আর.এন.) স্কুল', 'raja-rajendra-narayan-r-r-n-school'],
    'repeated honorific is kept' => ['শ্রী শ্রী কালী মন্দির', 'sree-sree-kali-mandir'],
]);

test('english text is slugged exactly like str slug', function (string $text): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe(Str::slug($text));
})->with(['Gazipur City Hospital & Clinic', 'Hello World 2025', 'already-a-slug']);

test('mixed english and bangla keeps the english part', function (): void {
    expect(resolve(BanglaSlug::class)->generate('Hello দুনিয়া!'))->toBe('hello-duniya');
});

test('malformed utf-8 bytes are dropped without losing the bangla text', function (): void {
    expect(resolve(BanglaSlug::class)->generate("ঢাকা \xB1 ok"))->toBe('dhaka-ok');
});

test('split o-kar and ou-kar read the same as the single characters', function (): void {
    expect(resolve(BanglaSlug::class)->generate("ক\u{09C7}\u{09BE}ম্পানি ম\u{09C7}\u{09D7}লভী"))
        ->toBe(resolve(BanglaSlug::class)->generate('কোম্পানি মৌলভী'))
        ->toBe('company-moulbhi');
});

test('text without anything sluggable gives an empty slug', function (string $text): void {
    expect(resolve(BanglaSlug::class)->generate($text))->toBe('');
})->with(['', "   \t\n", '।!?,', '😀🎉']);

test('unique slug of empty text is just the number, without a leading separator', function (): void {
    expect(resolve(BanglaSlug::class)->unique('!!!'))->toMatch('/^\d{1,6}$/');
});

test('a max length of zero disables the limit', function (): void {
    $title = str_repeat('আমার সোনার বাংলা ', 10);

    expect((new BanglaSlug(maxLength: 0))->generate($title))->toBe(Str::slug(str_repeat('amar sonar bangla ', 10)));
});

test('the length limit works without a separator', function (): void {
    expect((new BanglaSlug(maxLength: 10))->generate('আমার সোনার বাংলা', ''))->toBe('amarsonarb');
});

test('custom separator is used', function (): void {
    expect(resolve(BanglaSlug::class)->generate('গাজীপুর সদর', '_'))->toBe('gazipur_sadar');
});

test('long slugs are cut at a whole word', function (): void {
    config(['bangla-slug.max_length' => 20]);

    expect(resolve(BanglaSlug::class)->generate('গাজীপুর সিটি কর্পোরেশন নতুন রাস্তা'))->toBe('gazipur-city');
});

test('unique slug appends a random numeric suffix', function (): void {
    expect(resolve(BanglaSlug::class)->unique('মসজিদ'))->toMatch('/^mosque-\d{1,6}$/');
});

test('unique retries with a new suffix while the slug is taken', function (): void {
    $taken = [];
    $slug = resolve(BanglaSlug::class)->unique('মসজিদ', function (string $candidate) use (&$taken): bool {
        $taken[] = $candidate;

        return count($taken) < 3;
    });

    expect($taken)->toHaveCount(3)
        ->and($slug)->toBe($taken[2])
        ->and($slug)->toMatch('/^mosque-\d{1,6}$/');
});

test('unique throws instead of looping forever when every slug is taken', function (): void {
    resolve(BanglaSlug::class)->unique('মসজিদ', fn (): bool => true);
})->throws(RuntimeException::class, 'Could not find a free slug for [mosque]');

test('words added through config are used', function (): void {
    config(['bangla-slug.words' => ['জরুরি' => 'emergency']]);

    expect(resolve(BanglaSlug::class)->generate('জরুরি সেবা'))->toBe('emergency-seba');
});

test('facade and str macro generate the same slug', function (): void {
    expect(BanglaSlugFacade::generate('গাজীপুর সদর'))->toBe('gazipur-sadar')
        ->and(Str::banglaSlug('গাজীপুর সদর'))->toBe('gazipur-sadar');
});
