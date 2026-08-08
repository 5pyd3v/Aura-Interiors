<?php
/**
 * Shared helper functions used across the public site and admin panel.
 */
declare(strict_types=1);

/** Escape for HTML output. */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/** All settings, loaded once per request and cached in a static var. */
function get_settings(): array
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        $stmt = db()->query('SELECT setting_key, setting_value FROM settings');
        foreach ($stmt->fetchAll() as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $settings;
}

function setting(string $key, string $default = ''): string
{
    $settings = get_settings();
    return $settings[$key] ?? $default;
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function slugify(string $text): string
{
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = trim((string) $text, '-');
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', (string) $text) ?: $text;
    $text = strtolower((string) $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    return $text !== '' ? $text : 'n-a';
}

function unique_slug(string $table, string $baseSlug, ?int $ignoreId = null): string
{
    $pdo = db();
    $slug = $baseSlug;
    $i = 2;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = ?" . ($ignoreId ? ' AND id != ?' : '');
        $stmt = $pdo->prepare($sql);
        $params = [$slug];
        if ($ignoreId) {
            $params[] = $ignoreId;
        }
        $stmt->execute($params);
        if (!$stmt->fetch()) {
            return $slug;
        }
        $slug = $baseSlug . '-' . $i;
        $i++;
    }
}

/** WhatsApp deep link using the configured number + optional custom message. */
function whatsapp_link(?string $message = null): string
{
    $number = preg_replace('/\D+/', '', setting('whatsapp_number', ''));
    $msg = $message ?? setting('whatsapp_default_message', '');
    return 'https://wa.me/' . $number . '?text=' . rawurlencode($msg);
}

function tel_link(): string
{
    $phone = preg_replace('/[^\d+]/', '', setting('phone', ''));
    return 'tel:' . $phone;
}

function asset(string $path): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Resolve an uploaded/demo image path to a full URL, with a graceful fallback. */
function img(?string $path, string $fallback = 'assets/images/placeholder.jpg'): string
{
    $path = empty($path) ? $fallback : $path;

    if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
        return $path;
    }

    $url = asset($path);
    $full = BASE_PATH . '/' . ltrim($path, '/');
    if (is_file($full)) {
        $url .= '?v=' . filemtime($full);
    }
    return $url;
}

function pkr(?string $value): string
{
    return $value ? e($value) : 'Not specified';
}

function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hr ago';
    if ($diff < 2592000) return floor($diff / 86400) . ' days ago';
    return date('M j, Y', strtotime($datetime));
}

function log_event(string $type, ?string $reference = null): void
{
    try {
        $stmt = db()->prepare('INSERT INTO analytics_events (event_type, reference) VALUES (?, ?)');
        $stmt->execute([$type, $reference]);
    } catch (Throwable $e) {
        // Analytics must never break the page.
    }
}

/**
 * Validate + store an uploaded image, generating an optimized thumbnail.
 * Returns ['path' => relative path, 'thumb' => relative thumb path] or null on failure.
 * $subdir example: 'projects', 'gallery', 'team'
 */
function handle_image_upload(array $file, string $subdir, ?string &$error = null): ?array
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Upload failed.';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_BYTES) {
        $error = 'Image is larger than the 5MB limit.';
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_IMAGE_MIME, true)) {
        $error = 'Only JPG, PNG or WEBP images are allowed.';
        return null;
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        $error = 'File is not a valid image.';
        return null;
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };

    $dir = UPLOADS_PATH . '/' . trim($subdir, '/');
    $thumbDir = UPLOADS_PATH . '/thumbs/' . trim($subdir, '/');
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

    $filename = bin2hex(random_bytes(12)) . '.' . $ext;
    $destPath = $dir . '/' . $filename;
    $thumbPath = $thumbDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        $error = 'Could not save the uploaded file.';
        return null;
    }

    generate_thumbnail($destPath, $thumbPath, $mime, THUMB_WIDTH);
    compress_image_in_place($destPath, $mime);

    return [
        'path'  => 'uploads/' . trim($subdir, '/') . '/' . $filename,
        'thumb' => 'uploads/thumbs/' . trim($subdir, '/') . '/' . $filename,
    ];
}

function generate_thumbnail(string $srcPath, string $destPath, string $mime, int $targetWidth): void
{
    if (!extension_loaded('gd')) {
        copy($srcPath, $destPath);
        return;
    }
    [$width, $height] = getimagesize($srcPath);
    $src = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($srcPath),
        'image/png'  => imagecreatefrompng($srcPath),
        'image/webp' => imagecreatefromwebp($srcPath),
        default      => null,
    };
    if (!$src) {
        copy($srcPath, $destPath);
        return;
    }
    $ratio = $height / $width;
    $newWidth = min($targetWidth, $width);
    $newHeight = (int) round($newWidth * $ratio);
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    if ($mime === 'image/png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    imagecopyresampled($thumb, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    match ($mime) {
        'image/jpeg' => imagejpeg($thumb, $destPath, 82),
        'image/png'  => imagepng($thumb, $destPath, 6),
        'image/webp' => imagewebp($thumb, $destPath, 82),
        default      => null,
    };
    imagedestroy($src);
    imagedestroy($thumb);
}

function compress_image_in_place(string $path, string $mime): void
{
    if (!extension_loaded('gd')) {
        return;
    }
    $img = match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($path),
        'image/png'  => imagecreatefrompng($path),
        'image/webp' => imagecreatefromwebp($path),
        default      => null,
    };
    if (!$img) {
        return;
    }
    match ($mime) {
        'image/jpeg' => imagejpeg($img, $path, 84),
        'image/png'  => imagepng($img, $path, 6),
        'image/webp' => imagewebp($img, $path, 84),
        default      => null,
    };
    imagedestroy($img);
}

function delete_upload(?string $relativePath): void
{
    if (!$relativePath) return;
    $full = BASE_PATH . '/' . $relativePath;
    if (is_file($full)) {
        @unlink($full);
    }
    $thumb = str_replace('/uploads/', '/uploads/thumbs/', $full);
    if (str_contains($relativePath, 'uploads/') && is_file($thumb)) {
        @unlink($thumb);
    }
}

/** Simple pagination helper. Returns [offset, totalPages, currentPage]. */
function paginate(int $totalItems, int $perPage, int $currentPage): array
{
    $totalPages = max(1, (int) ceil($totalItems / $perPage));
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    return [$offset, $totalPages, $currentPage];
}
