<?php
/**
 * DEV-ONLY utility: replaces the highest-visibility placeholder images with
 * real, properly-licensed (CC0 / Public Domain preferred) interior design
 * photography sourced from Openverse (openverse.org — a CC-licensed image
 * search aggregator, no API key required).
 *
 * Images are downloaded, resized/cropped to the exact target dimensions with
 * GD, and saved over the existing placeholder filenames — so no PHP/DB
 * changes are needed. A credits log is written for licensing records.
 *
 * Run once: php tools/fetch-stock-photos.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$demoDir = $root . '/assets/images/demo';
$creditsPath = $root . '/assets/images/demo/CREDITS.txt';

$targets = [
    'hero.jpg'                       => ['modern living room interior design',   1920, 1080],
    'about-studio.jpg'               => ['interior design studio workspace',     1200, 1500],
    'og-default.jpg'                 => ['modern interior design home',          1200, 630],

    'service-residential.jpg'        => ['modern living room interior',          900, 700],
    'service-commercial.jpg'         => ['modern office interior design',        900, 700],
    'service-restaurant.jpg'         => ['restaurant interior design',           900, 700],
    'service-office.jpg'             => ['office interior workspace design',     900, 700],
    'service-architecture.jpg'       => ['modern architecture building',         900, 700],
    'service-turnkey.jpg'            => ['modern home renovation interior',      900, 700],

    'project-islamabad-residence.jpg' => ['contemporary living room interior',   1200, 900],
    'project-bahria-villa.jpg'        => ['luxury villa interior design',        1200, 900],
    'project-corporate-office.jpg'    => ['modern corporate office interior',    1200, 900],
    'project-karachi-apartment.jpg'   => ['minimalist apartment interior',       1200, 900],
    'project-restaurant.jpg'          => ['fine dining restaurant interior',     1200, 900],
    'project-cafe.jpg'                => ['modern cafe interior design',         1200, 900],
    'project-executive-office.jpg'    => ['executive office interior',           1200, 900],
    'project-bedroom.jpg'             => ['modern bedroom interior design',      1200, 900],
    'project-kitchen.jpg'             => ['modern kitchen interior design',      1200, 900],
    'project-living-room.jpg'         => ['elegant living room interior',        1200, 900],
    'project-retail.jpg'              => ['modern retail store interior',        1200, 900],
    'project-hotel-lobby.jpg'         => ['hotel lobby interior design',         1200, 900],
];

function http_get(string $url, int $timeout = 15): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'AuraInteriorsDemoBuilder/1.0 (+https://example.com)',
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($body !== false && $code >= 200 && $code < 300) ? $body : null;
}

function pick_best_result(array $results): ?array
{
    if (!$results) return null;
    usort($results, function ($a, $b) {
        return ($b['width'] ?? 0) <=> ($a['width'] ?? 0);
    });
    foreach ($results as $r) {
        if (($r['width'] ?? 0) >= 800 && !empty($r['url'])) {
            return $r;
        }
    }
    return $results[0];
}

function search_openverse(string $query): ?array
{
    $endpoint = 'https://api.openverse.org/v1/images/?' . http_build_query([
        'q' => $query,
        'license' => 'cc0,pdm',
        'orientation' => 'landscape',
        'page_size' => 6,
        'mature' => 'false',
    ]);
    $json = http_get($endpoint);
    if (!$json) return null;
    $data = json_decode($json, true);
    return pick_best_result($data['results'] ?? []);
}

/** Resize + center-crop ("cover") an image blob to exact target dimensions. */
function cover_crop_save(string $blob, int $targetW, int $targetH, string $destPath): bool
{
    $src = @imagecreatefromstring($blob);
    if (!$src) return false;
    $srcW = imagesx($src);
    $srcH = imagesy($src);

    $srcRatio = $srcW / $srcH;
    $targetRatio = $targetW / $targetH;

    if ($srcRatio > $targetRatio) {
        $cropH = $srcH;
        $cropW = (int) round($srcH * $targetRatio);
    } else {
        $cropW = $srcW;
        $cropH = (int) round($srcW / $targetRatio);
    }
    $cropX = (int) (($srcW - $cropW) / 2);
    $cropY = (int) (($srcH - $cropH) / 2);

    $dst = imagecreatetruecolor($targetW, $targetH);
    imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);
    $ok = imagejpeg($dst, $destPath, 84);
    imagedestroy($src);
    imagedestroy($dst);
    return $ok;
}

$credits = ["Stock photography credits — sourced via Openverse (openverse.org)\n" . str_repeat('=', 70) . "\n"];
$success = 0;
$skipped = [];

foreach ($targets as $filename => [$query, $w, $h]) {
    echo "Searching: $query ... ";
    $result = search_openverse($query);
    if (!$result) {
        echo "no result, keeping placeholder\n";
        $skipped[] = $filename;
        usleep(400000);
        continue;
    }

    $imgBlob = http_get($result['url'], 20);
    if (!$imgBlob) {
        echo "download failed, keeping placeholder\n";
        $skipped[] = $filename;
        usleep(400000);
        continue;
    }

    $destPath = $demoDir . '/' . $filename;
    if (cover_crop_save($imgBlob, $w, $h, $destPath)) {
        echo "OK -> $filename\n";
        $success++;
        $credits[] = sprintf(
            "%-38s | \"%s\" by %s | license: %s | source: %s",
            $filename,
            $result['title'] ?? 'Untitled',
            $result['creator'] ?? 'Unknown',
            strtoupper($result['license'] ?? 'unknown'),
            $result['foreign_landing_url'] ?? $result['url']
        );
    } else {
        echo "image processing failed, keeping placeholder\n";
        $skipped[] = $filename;
    }

    usleep(600000); // be polite to the free public API
}

file_put_contents($creditsPath, implode("\n", $credits) . "\n");

echo "\nDone. $success/" . count($targets) . " images replaced with real photography.\n";
if ($skipped) {
    echo "Kept placeholder for: " . implode(', ', $skipped) . "\n";
}
echo "Credits written to assets/images/demo/CREDITS.txt\n";
