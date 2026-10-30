<?php

it('toggles between light and dark and remembers the choice after a reload', function () {
    $page = visit('/')->inLightMode();
    $theme = 'document.documentElement.dataset.theme';

    e2eWait($page, "{$theme} === 'light'", 'starts in light theme');

    $page->click('@theme-toggle');
    e2eWait($page, "{$theme} === 'dark'", 'switches to dark');
    expect($page->script("() => localStorage.getItem('theme')"))->toBe('dark');

    // The stored choice wins over the (light) system preference, with no flash: it is applied before first paint.
    $page->refresh();
    e2eWait($page, "{$theme} === 'dark'", 'dark theme persists after reload');

    $page->navigate('/listings');
    e2eWait($page, "{$theme} === 'dark'", 'dark theme persists on another page');

    $page->click('@theme-toggle');
    e2eWait($page, "{$theme} === 'light'", 'switches back to light');
    $page->refresh();
    e2eWait($page, "{$theme} === 'light'", 'light theme persists after reload');
});

it('follows the system colour scheme until a choice is stored', function () {
    $page = visit('/')->inDarkMode();

    e2eWait($page, "document.documentElement.dataset.theme === 'dark'", 'dark system preference is applied');
    expect($page->script("() => localStorage.getItem('theme')"))->toBeNull();
});
