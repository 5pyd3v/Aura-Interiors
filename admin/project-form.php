<?php
require_once __DIR__ . '/includes/auth.php';

$categories = ['Residential','Commercial','Office','Restaurant','Bedroom','Kitchen','Living Room','Luxury Interiors'];

$id = (int) ($_GET['id'] ?? 0);
$project = [
    'title' => '', 'slug' => '', 'category' => 'Residential', 'location' => '', 'property_type' => '',
    'area_sqft' => '', 'completion_year' => date('Y'), 'short_description' => '', 'description' => '',
    'cover_image' => '', 'video_url' => '', 'is_featured' => 0, 'is_published' => 1,
];
$isEdit = false;
$galleryImages = [];

if ($id) {
    $stmt = db()->prepare('SELECT * FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $project = $found;
        $isEdit = true;
        $gi = db()->prepare('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order');
        $gi->execute([$id]);
        $galleryImages = $gi->fetchAll();
    }
}

// Delete a single gallery image (separate mini-action on this same page)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'delete_image') {
    admin_verify_csrf('project-form.php?id=' . $id);
    $imageId = (int) ($_POST['image_id'] ?? 0);
    $stmt = db()->prepare('SELECT * FROM project_images WHERE id = ? AND project_id = ?');
    $stmt->execute([$imageId, $id]);
    if ($img = $stmt->fetch()) {
        delete_upload($img['image_path']);
        db()->prepare('DELETE FROM project_images WHERE id = ?')->execute([$imageId]);
    }
    redirect('project-form.php?id=' . $id);
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'save') {
    admin_verify_csrf('project-form.php' . ($id ? '?id=' . $id : ''));

    $title = trim((string) ($_POST['title'] ?? ''));
    $slugInput = trim((string) ($_POST['slug'] ?? ''));
    $category = $_POST['category'] ?? 'Residential';
    $location = trim((string) ($_POST['location'] ?? ''));
    $propertyType = trim((string) ($_POST['property_type'] ?? ''));
    $areaSqft = (int) ($_POST['area_sqft'] ?? 0) ?: null;
    $completionYear = (int) ($_POST['completion_year'] ?? date('Y'));
    $shortDesc = trim((string) ($_POST['short_description'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $videoUrl = trim((string) ($_POST['video_url'] ?? ''));
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') $errors[] = 'Title is required.';
    if (!in_array($category, $categories, true)) $errors[] = 'Please select a valid category.';

    $coverImage = $project['cover_image'];
    if (!empty($_FILES['cover_image']['name'])) {
        $upload = handle_image_upload($_FILES['cover_image'], 'projects', $uploadError);
        if ($upload) {
            if ($isEdit && $coverImage) delete_upload($coverImage);
            $coverImage = $upload['path'];
        } else {
            $errors[] = $uploadError;
        }
    } elseif (!$isEdit) {
        $errors[] = 'Please upload a cover image.';
    }

    if (!$errors) {
        $slug = unique_slug('projects', slugify($slugInput !== '' ? $slugInput : $title), $isEdit ? $id : null);

        if ($isEdit) {
            $stmt = db()->prepare('UPDATE projects SET title=?, slug=?, category=?, location=?, property_type=?, area_sqft=?, completion_year=?, short_description=?, description=?, cover_image=?, video_url=?, is_featured=?, is_published=? WHERE id=?');
            $stmt->execute([$title, $slug, $category, $location, $propertyType, $areaSqft, $completionYear, $shortDesc, $description, $coverImage, $videoUrl, $isFeatured, $isPublished, $id]);
        } else {
            $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order),0) FROM projects')->fetchColumn();
            $stmt = db()->prepare('INSERT INTO projects (title, slug, category, location, property_type, area_sqft, completion_year, short_description, description, cover_image, video_url, is_featured, is_published, sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$title, $slug, $category, $location, $propertyType, $areaSqft, $completionYear, $shortDesc, $description, $coverImage, $videoUrl, $isFeatured, $isPublished, $maxOrder + 1]);
            $id = (int) db()->lastInsertId();
        }

        // Additional gallery images (multi-upload)
        if (!empty($_FILES['gallery_images']['name'][0])) {
            $count = count($_FILES['gallery_images']['name']);
            $orderStmt = db()->prepare('SELECT COALESCE(MAX(sort_order),0) FROM project_images WHERE project_id = ?');
            $orderStmt->execute([$id]);
            $nextOrder = (int) $orderStmt->fetchColumn() + 1;
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['gallery_images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'name' => $_FILES['gallery_images']['name'][$i],
                    'type' => $_FILES['gallery_images']['type'][$i],
                    'tmp_name' => $_FILES['gallery_images']['tmp_name'][$i],
                    'error' => $_FILES['gallery_images']['error'][$i],
                    'size' => $_FILES['gallery_images']['size'][$i],
                ];
                $up = handle_image_upload($file, 'projects', $imgErr);
                if ($up) {
                    db()->prepare('INSERT INTO project_images (project_id, image_path, sort_order) VALUES (?,?,?)')
                        ->execute([$id, $up['path'], $nextOrder++]);
                }
            }
        }

        flash_set('success', $isEdit ? 'Project updated.' : 'Project created.');
        redirect('project-form.php?id=' . $id);
    }
    $project = array_merge($project, compact('title', 'category', 'location', 'propertyType', 'areaSqft', 'completionYear', 'shortDesc', 'description', 'videoUrl', 'isFeatured', 'isPublished'));
    $project['cover_image'] = $coverImage;
    $project['slug'] = $slugInput;
}

$adminPageTitle = $isEdit ? 'Edit Project' : 'Add Project';
$adminActive = 'projects';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="projects.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back to Projects</a>

<div class="panel">
  <div class="panel__body">
    <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <div class="form-grid">
        <div class="form-group">
          <label for="title">Project Title <span style="color:var(--accent-2)">*</span></label>
          <input class="form-control" type="text" id="title" name="title" data-slug-source="#slug" value="<?= e($project['title']) ?>" required>
        </div>
        <div class="form-group">
          <label for="slug">URL Slug</label>
          <input class="form-control" type="text" id="slug" name="slug" value="<?= e($project['slug']) ?>" placeholder="auto-generated from title">
        </div>
        <div class="form-group">
          <label for="category">Category <span style="color:var(--accent-2)">*</span></label>
          <select class="form-control" id="category" name="category">
            <?php foreach ($categories as $c): ?>
              <option value="<?= e($c) ?>" <?= $project['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="location">Location</label>
          <input class="form-control" type="text" id="location" name="location" value="<?= e($project['location']) ?>" placeholder="e.g. Islamabad">
        </div>
        <div class="form-group">
          <label for="property_type">Property Type</label>
          <input class="form-control" type="text" id="property_type" name="property_type" value="<?= e($project['property_type']) ?>" placeholder="e.g. House, Apartment, Office">
        </div>
        <div class="form-group">
          <label for="area_sqft">Area (sq. ft)</label>
          <input class="form-control" type="number" id="area_sqft" name="area_sqft" value="<?= e((string) $project['area_sqft']) ?>">
        </div>
        <div class="form-group">
          <label for="completion_year">Completion Year</label>
          <input class="form-control" type="number" id="completion_year" name="completion_year" value="<?= e((string) $project['completion_year']) ?>">
        </div>
        <div class="form-group">
          <label for="video_url">Video Embed URL (optional)</label>
          <input class="form-control" type="text" id="video_url" name="video_url" value="<?= e($project['video_url']) ?>" placeholder="https://www.youtube.com/embed/...">
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_featured" value="1" <?= $project['is_featured'] ? 'checked' : '' ?>> Featured on homepage
          </label>
        </div>
        <div class="form-group">
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_published" value="1" <?= $project['is_published'] ? 'checked' : '' ?>> Published (visible on website)
          </label>
        </div>
        <div class="form-group full">
          <label for="short_description">Short Description</label>
          <textarea class="form-control" id="short_description" name="short_description" rows="2"><?= e($project['short_description']) ?></textarea>
        </div>
        <div class="form-group full">
          <label for="description">Full Description</label>
          <textarea class="form-control" id="description" name="description" rows="6"><?= e($project['description']) ?></textarea>
        </div>
        <div class="form-group full">
          <label>Cover Image <?= $isEdit ? '' : '<span style="color:var(--accent-2)">*</span>' ?></label>
          <div class="image-upload-box" onclick="document.getElementById('cover_image').click()">
            <img src="<?= e(img($project['cover_image'], 'assets/images/placeholder.jpg')) ?>" alt="">
            <p>Click to upload / replace cover image (JPG, PNG, WEBP — max 5MB)</p>
          </div>
          <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>

        <?php if ($isEdit): ?>
        <div class="form-group full">
          <label>Project Gallery Images</label>
          <?php if ($galleryImages): ?>
          <div class="chip-list mt-40" style="margin-top:14px;margin-bottom:18px">
            <?php foreach ($galleryImages as $gi): ?>
              <div class="chip-thumb">
                <img src="<?= e(img($gi['image_path'])) ?>" alt="">
                <form method="post" data-confirm="Remove this image?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete_image">
                  <input type="hidden" name="image_id" value="<?= $gi['id'] ?>">
                  <button type="submit"><i class="fa-solid fa-xmark"></i></button>
                </form>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <input type="file" name="gallery_images[]" accept="image/jpeg,image/png,image/webp" multiple class="form-control">
          <p class="form-hint">Select multiple images to add to this project's gallery. New images are appended after saving.</p>
        </div>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> <?= $isEdit ? 'Update Project' : 'Create Project' ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
