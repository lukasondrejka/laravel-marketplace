<?php

namespace Tests\Browser\Support;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Test-only middleware for the E2E suite. The Pest browser plugin's in-process HTTP server forwards the raw body
 * but does not populate `$_FILES`/`$_POST` for multipart requests, so Livewire uploads would arrive without files.
 * This rebuilds files and fields from the raw body before the request reaches the app.
 */
class ParseMultipartBody
{
    public function handle(Request $request, Closure $next): Response
    {
        $type = (string) $request->headers->get('content-type');

        if (str_starts_with(strtolower($type), 'multipart/form-data') && preg_match('/boundary="?([^";]+)"?/i', $type, $m)) {
            $this->populate($request, $m[1]);
        }

        return $next($request);
    }

    private function populate(Request $request, string $boundary): void
    {
        $parts = explode('--'.$boundary, $request->getContent());
        // First chunk is the preamble, the last one the closing "--".
        array_shift($parts);

        foreach ($parts as $part) {
            if (str_starts_with($part, '--')) {
                break;
            }

            [$rawHeaders, $body] = explode("\r\n\r\n", ltrim($part, "\r\n"), 2) + [1 => ''];
            $body = substr($body, 0, -2); // trailing CRLF before the next boundary

            if (! preg_match('/name="([^"]*)"/i', $rawHeaders, $name)) {
                continue;
            }

            $key = $name[1];

            if (preg_match('/filename="([^"]*)"/i', $rawHeaders, $file)) {
                $path = tempnam(sys_get_temp_dir(), 'e2e-upload-');
                file_put_contents($path, $body);
                $mime = preg_match('/content-type:\s*([^\r\n]+)/i', $rawHeaders, $ct) ? trim($ct[1]) : null;
                $upload = new UploadedFile($path, $file[1], $mime, null, true);

                $this->push($request->files, $key, $upload);
            } else {
                $this->push($request->request, $key, $body);
            }
        }
    }

    private function push(object $bag, string $key, mixed $value): void
    {
        if (str_ends_with($key, '[]')) {
            $key = substr($key, 0, -2);
            $bag->set($key, [...(array) $bag->all($key), $value]);

            return;
        }

        $bag->set($key, $value);
    }
}
