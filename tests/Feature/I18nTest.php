<?php

/**
 * Every literal passed to __() / trans_choice() / @lang in the app must have an entry in lang/en.json,
 * and entries nobody uses any more should be removed. English maps each key to itself, so a missing
 * key would silently render the key - fine for English, but a hole for the next language.
 */
function translationKeysUsed(): array
{
    $paths = [];

    foreach ([base_path('app'), resource_path('views')] as $directory) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.php')) {
                $paths[] = $file->getPathname();
            }
        }
    }

    $keys = [];

    foreach ($paths as $path) {
        $code = file_get_contents($path);
        preg_match_all('/(?:__|trans_choice|@lang)\(\s*(\'((?:[^\'\\\\]|\\\\.)*)\'|"((?:[^"\\\\]|\\\\.)*)")/', $code, $matches);

        foreach ($matches[2] as $i => $single) {
            $keys[] = $single !== '' ? stripcslashes($single) : stripcslashes($matches[3][$i]);
        }
    }

    return array_values(array_unique($keys));
}

it('has an English translation for every key used in code and views', function () {
    $defined = array_keys(json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR));

    // Keys that are file based (validation.x) or dynamic are not literals in en.json.
    $used = array_filter(translationKeysUsed(), fn ($key) => ! preg_match('/^[a-z_]+\.[a-z_.]+$/', $key));

    expect(array_values(array_diff($used, $defined)))->toBe([]);
});

it('has no unused entries in lang/en.json', function () {
    $defined = array_keys(json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR));

    expect(array_values(array_diff($defined, translationKeysUsed())))->toBe([]);
});

it('maps every English key to itself', function () {
    $translations = json_decode(file_get_contents(lang_path('en.json')), true, flags: JSON_THROW_ON_ERROR);

    foreach ($translations as $key => $value) {
        expect($value)->toBe($key);
    }
});
