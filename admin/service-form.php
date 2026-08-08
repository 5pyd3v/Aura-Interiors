<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$service = ['title' => '', 'slug' => '', 'short_description' => '', 'content' => '', 'image_path' => '', 'icon_class' => 'fa-solid fa-house', 'is_published' => 1];
$isEdit = false;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) { $service = $found; $isEdit = true; }
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('service-form.php' . ($id ? '?id=' . $id : ''));

    $title = trim((string) ($_POST['title'] ?? ''));
    $slugInput = trim((string) ($_POST['slug'] ?? ''));
    $shortDesc = trim((string) ($_POST['short_description'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $icon = trim((string) ($_POST['icon_class'] ?? 'fa-solid fa-house'));
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') $errors[] = 'Title is required.';

    $imagePath = $service['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $upload = handle_image_upload($_FILES['image'], 'services', $uploadError);
        if ($upload) {
            if ($isEdit && $imagePath) delete_upload($imagePath);
            $imagePath = $upload['path'];
        } else {
            $errors[] = $uploadError;
        }
    }

    if (!$errors) {
        $slug = unique_slug('services', slugify($slugInput !== '' ? $slugInput : $title), $isEdit ? $id : null);
        if ($isEdit) {
            $stmt = db()->prepare('UPDATE services SET title=?, slug=?, short_description=?, content=?, image_path=?, icon_class=?, is_published=? WHERE id=?');
            $stmt->execute([$title, $slug, $shortDesc, $content, $imagePath, $icon, $isPublished, $id]);
            flash_set('success', 'Service updated.');
        } else {
            $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order),0) FROM services')->fetchColumn();
            $stmt = db()->prepare('INSERT INTO services (title, slug, short_description, content, image_path, icon_class, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$title, $slug, $shortDesc, $content, $imagePath, $icon, $maxOrder + 1, $isPublished]);
            flash_set('success', 'Service created.');
        }
        redirect('services.php');
    }
    $service = array_merge($service, compact('title', 'shortDesc', 'content', 'icon', 'isPublished'));
    $service['image_path'] = $imagePath;
    $service['slug'] = $slugInput;
}

$adminPageTitle = $isEdit ? 'Edit Service' : 'Add Service';
$adminActive = 'services';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="services.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back to Services</a>

<div class="panel">
  <div class="panel__body">
    <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label for="title">Title <span style="color:var(--accent-2)">*</span></label>
          <input class="form-control" type="text" id="title" name="title" data-slug-source="#slug" value="<?= e($service['title']) ?>" required>
        </div>
        <div class="form-group">
          <label for="slug">URL Slug</label>
          <input class="form-control" type="text" id="slug" name="slug" value="<?= e($service['slug']) ?>" placeholder="auto-generated from title">
        </div>
        <div class="form-group">
          <label for="icon_class">Icon Class (Font Awesome)</label>
          <input class="form-control" type="text" id="icon_class" name="icon_class" value="<?= e($service['icon_class']) ?>" placeholder="fa-solid fa-house">
          <p class="form-hint">See <a href="https://fontawesome.com/search" target="_blank" rel="noopener">fontawesome.com/search</a> for icon names.</p>
        </div>
        <div class="form-group">
          <label>&nbsp;</label>
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_published" value="1" <?= $service['is_published'] ? 'checked' : '' ?>> Published (visible on website)
          </label>
        </div>
        <div class="form-group full">
          <label for="short_description">Short Description</label>
          <textarea class="form-control" id="short_description" name="short_description" rows="2"><?= e($service['short_description']) ?></textarea>
        </div>
        <div class="form-group full">
          <label for="content">Full Description</label>
          <textarea class="form-control" id="content" name="content" rows="6"><?= e($service['content']) ?></textarea>
        </div>
        <div class="form-group full">
          <label>Service Image</label>
          <div class="image-upload-box" onclick="document.getElementById('image').click()">
            <img src="<?= e(img($service['image_path'], 'assets/images/placeholder.jpg')) ?>" alt="">
            <p>Click to upload / replace image (JPG, PNG, WEBP — max 5MB)</p>
          </div>
          <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> <?= $isEdit ? 'Update Service' : 'Create Service' ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
