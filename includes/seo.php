<?php
/**
 * SEO helpers: per-page meta tags + JSON-LD structured data.
 * Call seo_render($page) inside <head> in header.php.
 */
declare(strict_types=1);

/**
 * $page keys: title, description, canonical, image, type (website|article), schema (array of JSON-LD blocks)
 */
function seo_render(array $page): void
{
    $title = $page['title'] ?? setting('meta_title');
    $description = $page['description'] ?? setting('meta_description');
    $canonical = $page['canonical'] ?? (BASE_URL . ($_SERVER['REQUEST_URI'] ?? ''));
    $image = $page['image'] ?? asset('assets/images/demo/og-default.jpg');
    $type = $page['type'] ?? 'website';

    echo '<title>' . e($title) . "</title>\n";
    echo '<meta name="description" content="' . e($description) . "\">\n";
    echo '<link rel="canonical" href="' . e($canonical) . "\">\n";

    echo '<meta property="og:title" content="' . e($title) . "\">\n";
    echo '<meta property="og:description" content="' . e($description) . "\">\n";
    echo '<meta property="og:type" content="' . e($type) . "\">\n";
    echo '<meta property="og:url" content="' . e($canonical) . "\">\n";
    echo '<meta property="og:image" content="' . e($image) . "\">\n";
    echo '<meta property="og:site_name" content="' . e(setting('company_name')) . "\">\n";

    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . e($title) . "\">\n";
    echo '<meta name="twitter:description" content="' . e($description) . "\">\n";
    echo '<meta name="twitter:image" content="' . e($image) . "\">\n";

    if (!empty($page['schema']) && is_array($page['schema'])) {
        foreach ($page['schema'] as $block) {
            echo '<script type="application/ld+json">' . json_encode($block, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
        }
    }
}

function schema_local_business(): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'HomeAndConstructionBusiness',
        'name' => setting('company_name'),
        'image' => asset('assets/images/demo/og-default.jpg'),
        'url' => BASE_URL,
        'telephone' => setting('phone'),
        'priceRange' => '$$',
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => setting('address'),
            'addressCountry' => 'PK',
        ],
        'sameAs' => array_values(array_filter([
            setting('facebook_url'), setting('instagram_url'), setting('youtube_url'), setting('tiktok_url'),
        ])),
    ];
}

function schema_breadcrumbs(array $items): array
{
    $list = [];
    foreach ($items as $i => $item) {
        $list[] = [
            '@type' => 'ListItem',
            'position' => $i + 1,
            'name' => $item['name'],
            'item' => $item['url'],
        ];
    }
    return [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $list,
    ];
}

function schema_article(array $post): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $post['title'],
        'image' => [img($post['featured_image'])],
        'datePublished' => $post['published_at'],
        'author' => ['@type' => 'Person', 'name' => $post['author_name'] ?? setting('company_name')],
        'publisher' => ['@type' => 'Organization', 'name' => setting('company_name')],
    ];
}

function schema_service(array $service): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'serviceType' => $service['title'],
        'description' => $service['short_description'],
        'provider' => ['@type' => 'Organization', 'name' => setting('company_name')],
        'areaServed' => 'PK',
    ];
}
