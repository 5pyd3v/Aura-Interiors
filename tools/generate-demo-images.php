<?php
/**
 * DEV-ONLY utility: generates placeholder JPG/PNG images for every demo path
 * referenced in database/database.sql, so the site renders correctly out of
 * the box. Replace these with real photography before going live.
 *
 * Run once: php tools/generate-demo-images.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$demoDir = $root . '/assets/images/demo';
$imgDir = $root . '/assets/images';
if (!is_dir($demoDir)) mkdir($demoDir, 0755, true);

// Mature, earthy palette matching the site's rust / brass / deep-pine brand colors.
$palettes = [
    [[168, 67, 42], [185, 133, 45]],   // rust -> brass
    [[47, 74, 62], [23, 20, 15]],      // deep pine -> ink
    [[185, 133, 45], [210, 162, 76]],  // brass -> gold
    [[23, 20, 15], [140, 56, 34]],     // ink -> rust
    [[47, 74, 62], [185, 133, 45]],    // pine -> brass
    [[140, 56, 34], [23, 20, 15]],     // rust -> ink
];

function draw_bold_string($img, int $font, int $x, int $y, string $text, int $color): void
{
    // GD has no bold built-in font, so fake it with a tight double-stamp.
    imagestring($img, $font, $x, $y, $text, $color);
    imagestring($img, $font, $x + 1, $y, $text, $color);
}

function draw_placeholder(string $path, int $w, int $h, string $label, array $palette): void
{
    $img = imagecreatetruecolor($w, $h);
    [$c1, $c2] = $palette;
    for ($y = 0; $y < $h; $y++) {
        $ratio = $y / max(1, $h - 1);
        $r = (int) ($c1[0] + ($c2[0] - $c1[0]) * $ratio);
        $g = (int) ($c1[1] + ($c2[1] - $c1[1]) * $ratio);
        $b = (int) ($c1[2] + ($c2[2] - $c1[2]) * $ratio);
        $color = imagecolorallocate($img, $r, $g, $b);
        imageline($img, 0, $y, $w, $y, $color);
    }
    // soft vignette instead of a busy diagonal stripe pattern
    $vignette = imagecolorallocatealpha($img, 0, 0, 0, 70);
    imagefilledrectangle($img, 0, $h - (int) ($h * 0.32), $w, $h, $vignette);

    $white = imagecolorallocate($img, 255, 255, 255);
    $font = 5;
    $lines = explode('|', wordwrap($label, 26, '|', true));
    $lineHeight = imagefontheight($font) + 10;
    $startY = (int) ($h / 2 - (count($lines) * $lineHeight) / 2);
    foreach ($lines as $i => $line) {
        $line = strtoupper($line);
        $textWidth = imagefontwidth($font) * strlen($line);
        $x = (int) (($w - $textWidth) / 2);
        draw_bold_string($img, $font, $x, $startY + $i * $lineHeight, $line, $white);
    }

    $brandY = $h - 30;
    $brand = 'AURA INTERIORS  //  PLACEHOLDER — REPLACE IN ADMIN PANEL';
    $bw = imagefontwidth(2) * strlen($brand);
    imagestring($img, 2, (int) (($w - $bw) / 2), $brandY, $brand, $white);

    imagejpeg($img, $path, 82);
    imagedestroy($img);
}

$targets = [
    'hero.jpg' => ['Spaces Designed to Inspire', 1920, 1080, 4],
    'about-studio.jpg' => ['Aura Interiors Studio', 1200, 1500, 0],
    'og-default.jpg' => ['Aura Interiors', 1200, 630, 1],

    'service-residential.jpg' => ['Residential Interior Design', 900, 700, 0],
    'service-commercial.jpg' => ['Commercial Interior Design', 900, 700, 1],
    'service-restaurant.jpg' => ['Restaurant and Cafe Design', 900, 700, 2],
    'service-office.jpg' => ['Office Interior Design', 900, 700, 3],
    'service-architecture.jpg' => ['Architecture and Space Planning', 900, 700, 4],
    'service-turnkey.jpg' => ['Turnkey Interior Solutions', 900, 700, 5],

    'project-islamabad-residence.jpg' => ['Modern Islamabad Residence', 1200, 900, 0],
    'project-bahria-villa.jpg' => ['Luxury Bahria Town Villa', 1200, 900, 1],
    'project-corporate-office.jpg' => ['Contemporary Corporate Office', 1200, 900, 2],
    'project-karachi-apartment.jpg' => ['Minimalist Karachi Apartment', 1200, 900, 3],
    'project-restaurant.jpg' => ['Modern Fine Dining Restaurant', 1200, 900, 4],
    'project-cafe.jpg' => ['Boutique Cafe Interior', 1200, 900, 5],
    'project-executive-office.jpg' => ['Executive Office Suite', 1200, 900, 0],
    'project-bedroom.jpg' => ['Serene Master Bedroom Suite', 1200, 900, 1],
    'project-kitchen.jpg' => ['Chef Style Modern Kitchen', 1200, 900, 2],
    'project-living-room.jpg' => ['Elegant Formal Living Room', 1200, 900, 3],
    'project-retail.jpg' => ['Commercial Retail Showroom', 1200, 900, 4],
    'project-hotel-lobby.jpg' => ['Boutique Hotel Lobby', 1200, 900, 5],

    'before-livingroom.jpg' => ['BEFORE — Living Room', 1000, 700, 4],
    'after-livingroom.jpg' => ['AFTER — Living Room', 1000, 700, 0],
    'before-kitchen.jpg' => ['BEFORE — Kitchen', 1000, 700, 4],
    'after-kitchen.jpg' => ['AFTER — Kitchen', 1000, 700, 2],
    'before-bedroom.jpg' => ['BEFORE — Bedroom', 1000, 700, 4],
    'after-bedroom.jpg' => ['AFTER — Bedroom', 1000, 700, 1],

    'gallery-1.jpg' => ['Living Room Detail', 800, 1000, 0],
    'gallery-2.jpg' => ['Kitchen Island', 800, 900, 1],
    'gallery-3.jpg' => ['Office Lounge', 800, 1100, 2],
    'gallery-4.jpg' => ['Restaurant Ambience', 800, 950, 3],
    'gallery-5.jpg' => ['Bedroom Styling', 800, 1050, 4],
    'gallery-6.jpg' => ['Facade Detail', 800, 900, 5],

    'team-1.jpg' => ['Hina Sheikh', 700, 900, 0],
    'team-2.jpg' => ['Ahmed Khan', 700, 900, 1],
    'team-3.jpg' => ['Zoya Tariq', 700, 900, 2],
    'team-4.jpg' => ['Usman Siddiqui', 700, 900, 3],

    'blog-1.jpg' => ['Modern Living Room Ideas', 1000, 650, 0],
    'blog-2.jpg' => ['Interior Design Cost Guide', 1000, 650, 1],
    'blog-3.jpg' => ['Modern Kitchen Design', 1000, 650, 2],
    'blog-4.jpg' => ['2026 Design Trends', 1000, 650, 3],
    'blog-5.jpg' => ['Choosing a Designer', 1000, 650, 4],
];

foreach ($targets as $filename => [$label, $w, $h, $paletteIndex]) {
    draw_placeholder($demoDir . '/' . $filename, $w, $h, $label, $palettes[$paletteIndex]);
    echo "Generated demo/$filename\n";
}

// Generic fallback placeholder + avatar placeholder + favicon (used by img()/setting fallbacks)
draw_placeholder($imgDir . '/placeholder.jpg', 900, 700, 'Image Coming Soon', $palettes[0]);
draw_placeholder($imgDir . '/avatar-placeholder.jpg', 400, 400, 'Photo', $palettes[2]);
echo "Generated placeholder.jpg\n";
echo "Generated avatar-placeholder.jpg\n";

// Simple favicon PNG
$fav = imagecreatetruecolor(64, 64);
imagesavealpha($fav, true);
$transparent = imagecolorallocatealpha($fav, 0, 0, 0, 127);
imagefill($fav, 0, 0, $transparent);
$bg = imagecolorallocate($fav, 168, 67, 42);
imagefilledellipse($fav, 32, 32, 60, 60, $bg);
$white = imagecolorallocate($fav, 255, 255, 255);
imagestring($fav, 5, 26, 22, 'A', $white);
imagepng($fav, $imgDir . '/favicon.png');
imagedestroy($fav);
echo "Generated favicon.png\n";

echo "\nDone. " . count($targets) . " demo images generated in assets/images/demo/\n";
