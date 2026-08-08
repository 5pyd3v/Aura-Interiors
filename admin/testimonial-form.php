<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$t = ['client_name' => '', 'review' => '', 'project_type' => '', 'location' => '', 'rating' => 5, 'photo_path' => '', 'is_published' => 1];
$isEdit = false;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM testimonials WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) { $t = $found; $isEdit = true; }
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('testimonial-form.php' . ($id ? '?id=' . $id : ''));

    $clientName = trim((string) ($_POST['client_name'] ?? ''));
    $review = trim((string) ($_POST['review'] ?? ''));
    $projectType = trim((string) ($_POST['project_type'] ?? ''));
    $location = trim((string) ($_POST['location'] ?? ''));
    $rating = max(1, min(5, (int) ($_POST['rating'] ?? 5)));
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($clientName === '') $errors[] = 'Client name is required.';
    if ($review === '') $errors[] = 'Review text is required.';

    $photoPath = $t['photo_path'];
    if (!empty($_FILES['photo']['name'])) {
        $up = handle_image_upload($_FILES['photo'], 'testimonials', $uploadError);
        if ($up) { if ($isEdit && $photoPath) delete_upload($photoPath); $photoPath = $up['path']; }
        else { $errors[] = $uploadError; }
    }

    if (!$errors) {
        if ($isEdit) {
            db()->prepare('UPDATE testimonials SET client_name=?, review=?, project_type=?, location=?, rating=?, photo_path=?, is_published=? WHERE id=?')
                ->execute([$clientName, $review, $projectType, $location, $rating, $photoPath, $isPublished, $id]);
            flash_set('success', 'Testimonial updated.');
        } else {
            $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order),0) FROM testimonials')->fetchColumn();
            db()->prepare('INSERT INTO testimonials (client_name, review, project_type, location, rating, photo_path, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$clientName, $review, $projectType, $location, $rating, $photoPath, $maxOrder + 1, $isPublished]);
            flash_set('success', 'Testimonial created.');
        }
        redirect('testimonials.php');
    }
    $t = array_merge($t, compact('clientName', 'review', 'projectType', 'location', 'rating', 'isPublished'));
    $t['photo_path'] = $photoPath;
}

$adminPageTitle = $isEdit ? 'Edit Testimonial' : 'Add Testimonial';
$adminActive = 'testimonials';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="testimonials.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back</a>

<div class="panel">
  <div class="panel__body">
    <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label for="client_name">Client Name <span style="color:var(--accent-2)">*</span></label>
          <input class="form-control" type="text" id="client_name" name="client_name" value="<?= e($t['client_name']) ?>" required>
        </div>
        <div class="form-group">
          <label for="rating">Rating</label>
          <select class="form-control" id="rating" name="rating">
            <?php for ($r = 5; $r >= 1; $r--): ?><option value="<?= $r ?>" <?= (int) $t['rating'] === $r ? 'selected' : '' ?>><?= $r ?> Star<?= $r > 1 ? 's' : '' ?></option><?php endfor; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="project_type">Project Type</label>
          <input class="form-control" type="text" id="project_type" name="project_type" value="<?= e($t['project_type']) ?>" placeholder="e.g. Residential — House">
        </div>
        <div class="form-group">
          <label for="location">Location</label>
          <input class="form-control" type="text" id="location" name="location" value="<?= e($t['location']) ?>" placeholder="e.g. Islamabad">
        </div>
        <div class="form-group full">
          <label for="review">Review <span style="color:var(--accent-2)">*</span></label>
          <textarea class="form-control" id="review" name="review" rows="4" required><?= e($t['review']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Client Photo (optional)</label>
          <div class="image-upload-box" onclick="document.getElementById('photo').click()">
            <img src="<?= e(img($t['photo_path'], 'assets/images/avatar-placeholder.jpg')) ?>" alt="">
            <p>Click to upload</p>
          </div>
          <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
        <div class="form-group">
          <label>&nbsp;</label>
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_published" value="1" <?= $t['is_published'] ? 'checked' : '' ?>> Published (visible on website)
          </label>
        </div>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
