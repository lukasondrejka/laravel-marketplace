<?php

use App\Models\Listing;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Tests\Browser\Support\ParseMultipartBody;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
 * E2E browser tests (Pest Browser plugin / Playwright). They live outside the phpunit.xml suites on purpose, so
 * `php artisan test` and the coverage gate never run them; use `composer test:e2e` or `make e2e`.
 */
// A stuck locator fails after 10 seconds instead of hanging the run (Playwright's default is 30).
pest()->browser()->timeout(10_000);

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->group('e2e')
    ->beforeEach(function () {
        // Always serve the built assets, even when a Vite dev server left a hot file behind.
        Vite::useHotFile(storage_path('framework/vite.no-hot'));

        // Uploaded media is written under public/ (served by the test HTTP server) and removed again afterwards.
        config([
            'filesystems.disks.public.root' => public_path('e2e-storage'),
            'filesystems.disks.public.url' => '/e2e-storage',
        ]);
        Storage::forgetDisk('public');

        // Livewire expects this disk when it detects it is running tests; it is only faked for Livewire::test().
        config(['filesystems.disks.tmp-for-tests' => ['driver' => 'local', 'root' => storage_path('framework/testing/e2e-livewire-tmp')]]);

        app(Kernel::class)->prependMiddleware(ParseMultipartBody::class);
    })
    ->afterEach(function () {
        File::deleteDirectory(public_path('e2e-storage'));
        File::deleteDirectory(storage_path('framework/testing/e2e-livewire-tmp'));
    })
    ->in('Browser');

/**
 * Builds a small solid-colour JPEG and returns it base64-encoded (E2E upload fixture).
 */
function e2eImage(int $red = 200, int $green = 60, int $blue = 60): string
{
    $image = imagecreatetruecolor(640, 480);
    imagefill($image, 0, 0, imagecolorallocate($image, $red, $green, $blue));
    ob_start();
    imagejpeg($image);

    return base64_encode((string) ob_get_clean());
}

/**
 * Puts images into the listing form like a browser would. The Playwright server is remote, so local file paths
 * cannot be attached; instead the files are built in the page and delivered via the real input `change` event
 * (`$viaDrop = false`) or a real `drop` event on the drop zone (`$viaDrop = true`).
 *
 * @param  array<string, string>  $files  file name => base64 JPEG
 */
function e2eUpload(object $page, array $files, bool $viaDrop = false): void
{
    $payload = json_encode($files, JSON_THROW_ON_ERROR);
    $mode = $viaDrop ? 'true' : 'false';

    $page->script(<<<JS
        () => {
            const files = {$payload};
            const data = new DataTransfer();
            for (const [name, base64] of Object.entries(files)) {
                const bytes = Uint8Array.from(atob(base64), (c) => c.charCodeAt(0));
                data.items.add(new File([bytes], name, { type: 'image/jpeg' }));
            }
            const input = document.querySelector('#uploads');
            if ({$mode}) {
                input.closest('label').dispatchEvent(new DragEvent('drop', { dataTransfer: data, bubbles: true, cancelable: true }));
            } else {
                input.files = data.files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        JS);
}

/**
 * Waits (inside the browser, no sleeping in PHP) until a JavaScript expression is truthy, then asserts it.
 * Needed because the plugin's assertions are instant and Livewire/Alpine update the DOM asynchronously.
 */
function e2eWait(object $page, string $expression, string $message, int $timeoutMs = 10000): void
{
    $ok = $page->script(<<<JS
        () => new Promise((resolve) => {
            const started = Date.now();
            const check = () => {
                let value = false;
                try { value = Boolean({$expression}); } catch (e) {}
                if (value) { return resolve(true); }
                if (Date.now() - started > {$timeoutMs}) { return resolve(false); }
                setTimeout(check, 50);
            };
            check();
        })
        JS);

    expect($ok)->toBeTrue("Timed out waiting for: {$message}");
}

/**
 * Attaches solid-colour JPEGs to a listing through the media library (fixture for gallery tests).
 */
function e2eAttachImages(Listing $listing, int $count): void
{
    foreach (range(1, $count) as $i) {
        $listing->addMediaFromBase64(e2eImage(40 * $i, 100, 200 - 40 * $i))
            ->usingFileName("photo-$i.jpg")
            ->toMediaCollection(Listing::IMAGES);
    }
}
