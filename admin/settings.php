<?php
require_once __DIR__ . '/includes/auth.php';

$editableKeys = [
    'company_name', 'tagline', 'phone', 'whatsapp_number', 'whatsapp_default_message', 'email', 'address',
    'google_maps_url', 'facebook_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'business_hours',
    'hero_heading', 'hero_subheading', 'cta_text', 'stat_years', 'stat_projects', 'stat_clients', 'stat_cities',
    'cities', 'google_analytics_id', 'meta_title', 'meta_description',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('settings.php');

    $stmt = db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
    foreach ($editableKeys as $key) {
        $value = trim((string) ($_POST[$key] ?? ''));
        $stmt->execute([$key, $value]);
    }

    // Keep footer social icon links in sync with the settings form.
    $syncMap = ['Facebook' => 'facebook_url', 'Instagram' => 'instagram_url', 'TikTok' => 'tiktok_url', 'YouTube' => 'youtube_url'];
    $syncStmt = db()->prepare('UPDATE social_links SET url = ? WHERE platform = ?');
    foreach ($syncMap as $platform => $key) {
        $syncStmt->execute([trim((string) ($_POST[$key] ?? '')), $platform]);
    }

    foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path', 'hero_image' => 'hero_image', 'about_image' => 'about_image'] as $field => $settingKey) {
        if (!empty($_FILES[$field]['name'])) {
            $up = handle_image_upload($_FILES[$field], 'settings', $uploadError);
            if ($up) {
                $old = setting($settingKey);
                if ($old) delete_upload($old);
                db()->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)')
                    ->execute([$settingKey, $up['path']]);
            }
        }
    }

    flash_set('success', 'Settings updated successfully.');
    redirect('settings.php');
}

$adminPageTitle = 'Settings';
$adminActive = 'settings';
require __DIR__ . '/includes/admin-header.php';
?>

<form method="post" enctype="multipart/form-data" class="admin-form">
  <?= csrf_field() ?>

  <div class="panel">
    <div class="panel__head"><h2>Company Information</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group"><label for="company_name">Company Name</label><input class="form-control" type="text" id="company_name" name="company_name" value="<?= e(setting('company_name')) ?>"></div>
        <div class="form-group"><label for="tagline">Tagline</label><input class="form-control" type="text" id="tagline" name="tagline" value="<?= e(setting('tagline')) ?>"></div>
        <div class="form-group">
          <label>Logo</label>
          <div class="image-upload-box" onclick="document.getElementById('logo').click()">
            <?php if (setting('logo_path')): ?><img src="<?= e(img(setting('logo_path'))) ?>" alt=""><?php else: ?><i class="fa-solid fa-image"></i><?php endif; ?>
            <p>Click to upload logo (PNG recommended)</p>
          </div>
          <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp" class="js-image-input" style="display:none">
        </div>
        <div class="form-group">
          <label>Favicon</label>
          <div class="image-upload-box" onclick="document.getElementById('favicon').click()">
            <?php if (setting('favicon_path')): ?><img src="<?= e(img(setting('favicon_path'))) ?>" alt="" style="max-height:60px"><?php else: ?><i class="fa-solid fa-image"></i><?php endif; ?>
            <p>Click to upload favicon (square PNG)</p>
          </div>
          <input type="file" id="favicon" name="favicon" accept="image/png" class="js-image-input" style="display:none">
        </div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>Contact &amp; Location</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group"><label for="phone">Phone Number</label><input class="form-control" type="text" id="phone" name="phone" value="<?= e(setting('phone')) ?>"></div>
        <div class="form-group"><label for="whatsapp_number">WhatsApp Number (with country code, digits only)</label><input class="form-control" type="text" id="whatsapp_number" name="whatsapp_number" value="<?= e(setting('whatsapp_number')) ?>" placeholder="923001234567"></div>
        <div class="form-group"><label for="email">Email</label><input class="form-control" type="email" id="email" name="email" value="<?= e(setting('email')) ?>"></div>
        <div class="form-group"><label for="business_hours">Business Hours</label><input class="form-control" type="text" id="business_hours" name="business_hours" value="<?= e(setting('business_hours')) ?>"></div>
        <div class="form-group full"><label for="address">Office Address</label><input class="form-control" type="text" id="address" name="address" value="<?= e(setting('address')) ?>"></div>
        <div class="form-group full"><label for="google_maps_url">Google Maps Embed URL</label><input class="form-control" type="text" id="google_maps_url" name="google_maps_url" value="<?= e(setting('google_maps_url')) ?>" placeholder="https://www.google.com/maps/embed?pb=..."></div>
        <div class="form-group full"><label for="whatsapp_default_message">Default WhatsApp Message</label><textarea class="form-control" id="whatsapp_default_message" name="whatsapp_default_message" rows="2"><?= e(setting('whatsapp_default_message')) ?></textarea></div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>Social Media</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group"><label for="facebook_url">Facebook URL</label><input class="form-control" type="text" id="facebook_url" name="facebook_url" value="<?= e(setting('facebook_url')) ?>"></div>
        <div class="form-group"><label for="instagram_url">Instagram URL</label><input class="form-control" type="text" id="instagram_url" name="instagram_url" value="<?= e(setting('instagram_url')) ?>"></div>
        <div class="form-group"><label for="tiktok_url">TikTok URL</label><input class="form-control" type="text" id="tiktok_url" name="tiktok_url" value="<?= e(setting('tiktok_url')) ?>"></div>
        <div class="form-group"><label for="youtube_url">YouTube URL</label><input class="form-control" type="text" id="youtube_url" name="youtube_url" value="<?= e(setting('youtube_url')) ?>"></div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>Homepage Hero</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group full"><label for="hero_heading">Hero Heading</label><input class="form-control" type="text" id="hero_heading" name="hero_heading" value="<?= e(setting('hero_heading')) ?>"></div>
        <div class="form-group full"><label for="hero_subheading">Hero Subheading</label><textarea class="form-control" id="hero_subheading" name="hero_subheading" rows="2"><?= e(setting('hero_subheading')) ?></textarea></div>
        <div class="form-group"><label for="cta_text">Primary CTA Text</label><input class="form-control" type="text" id="cta_text" name="cta_text" value="<?= e(setting('cta_text')) ?>"></div>
        <div class="form-group full">
          <label>Hero Background Image</label>
          <div class="image-upload-box" onclick="document.getElementById('hero_image').click()">
            <img src="<?= e(img(setting('hero_image'), 'assets/images/demo/hero.jpg')) ?>" alt="">
            <p>Click to upload / replace the full-screen homepage hero image (1920×1080 or larger recommended)</p>
          </div>
          <input type="file" id="hero_image" name="hero_image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>About Page</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group full">
          <label>About Page Image</label>
          <div class="image-upload-box" onclick="document.getElementById('about_image').click()">
            <img src="<?= e(img(setting('about_image'), 'assets/images/demo/about-studio.jpg')) ?>" alt="">
            <p>Click to upload / replace the studio photo shown on the About page</p>
          </div>
          <input type="file" id="about_image" name="about_image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>Trust Statistics</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group"><label for="stat_years">Years Experience</label><input class="form-control" type="text" id="stat_years" name="stat_years" value="<?= e(setting('stat_years')) ?>"></div>
        <div class="form-group"><label for="stat_projects">Projects Completed</label><input class="form-control" type="text" id="stat_projects" name="stat_projects" value="<?= e(setting('stat_projects')) ?>"></div>
        <div class="form-group"><label for="stat_clients">Happy Clients</label><input class="form-control" type="text" id="stat_clients" name="stat_clients" value="<?= e(setting('stat_clients')) ?>"></div>
        <div class="form-group"><label for="stat_cities">Cities Served</label><input class="form-control" type="text" id="stat_cities" name="stat_cities" value="<?= e(setting('stat_cities')) ?>"></div>
      </div>
    </div>
  </div>

  <div class="panel">
    <div class="panel__head"><h2>Locations &amp; SEO</h2></div>
    <div class="panel__body">
      <div class="form-grid">
        <div class="form-group full"><label for="cities">Cities Served (comma separated)</label><input class="form-control" type="text" id="cities" name="cities" value="<?= e(setting('cities')) ?>"></div>
        <div class="form-group full"><label for="meta_title">Default Meta Title</label><input class="form-control" type="text" id="meta_title" name="meta_title" value="<?= e(setting('meta_title')) ?>"></div>
        <div class="form-group full"><label for="meta_description">Default Meta Description</label><textarea class="form-control" id="meta_description" name="meta_description" rows="2"><?= e(setting('meta_description')) ?></textarea></div>
        <div class="form-group full"><label for="google_analytics_id">Google Analytics ID (optional)</label><input class="form-control" type="text" id="google_analytics_id" name="google_analytics_id" value="<?= e(setting('google_analytics_id')) ?>" placeholder="G-XXXXXXXXXX"></div>
      </div>
    </div>
  </div>

  <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> Save All Settings</button>
</form>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
