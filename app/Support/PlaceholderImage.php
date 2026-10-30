<?php

namespace App\Support;

/**
 * Generates deterministic gradient JPEGs with GD, so demo data needs no external downloads.
 */
class PlaceholderImage
{
    /**
     * Create a temporary JPEG and return its path. The caller owns the file.
     */
    public static function make(string $seed, int $width = 900, int $height = 675): string
    {
        $hash = crc32($seed);
        // Muted blue-grey tones only, so demo data looks calm and consistent.
        $hue = 200 + $hash % 40;

        $image = imagecreatetruecolor($width, $height);

        [$r1, $g1, $b1] = self::hsl($hue, 0.14, 0.78);
        [$r2, $g2, $b2] = self::hsl($hue, 0.16, 0.62);

        for ($y = 0; $y < $height; $y++) {
            $t = $y / $height;
            $color = imagecolorallocate(
                $image,
                (int) ($r1 + ($r2 - $r1) * $t),
                (int) ($g1 + ($g2 - $g1) * $t),
                (int) ($b1 + ($b2 - $b1) * $t),
            );
            imageline($image, 0, $y, $width, $y, $color);
        }

        // A simple, faint "frame" motif so images are not just flat fills.
        imagealphablending($image, true);
        $light = imagecolorallocatealpha($image, 255, 255, 255, 100);
        $fw = (int) ($width * 0.42);
        $fh = (int) ($height * 0.42);
        $x = (int) (($width - $fw) / 2);
        $y = (int) (($height - $fh) / 2);
        imagefilledrectangle($image, $x, $y, $x + $fw, $y + $fh, $light);
        imagefilledellipse($image, $x + (int) ($fw * 0.3), $y + (int) ($fh * 0.3), (int) ($fh * 0.18), (int) ($fh * 0.18), imagecolorallocatealpha($image, 255, 255, 255, 70));

        $path = tempnam(sys_get_temp_dir(), 'ph').'.jpg';
        imagejpeg($image, $path, 82);
        imagedestroy($image);

        return $path;
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function hsl(int $h, float $s, float $l): array
    {
        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;
        [$r, $g, $b] = match (intdiv($h, 60)) {
            0 => [$c, $x, 0],
            1 => [$x, $c, 0],
            2 => [0, $c, $x],
            3 => [0, $x, $c],
            4 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return [(int) (($r + $m) * 255), (int) (($g + $m) * 255), (int) (($b + $m) * 255)];
    }
}
