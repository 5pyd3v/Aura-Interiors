<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$item = ['project_name' => '', 'description' => '', 'before_image' => '', 'after_image' => '', 'is_published' => 1];
$isEdit = false;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM before_after WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) { $item = $found; $isEdit = true; }
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('ba-form.php' . ($id ? '?id=' . $id : ''));

    $projectName = trim((string) ($_POST['project_name'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($projectName === '') $errors[] = 'Project name is required.';

    $beforeImage = $item['before_image'];
    $afterImage = $item['after_image'];

    if (!empty($_FILES['before_image']['name'])) {
        $up = handle_image_upload($_FILES['before_image'], 'before-after', $err1);
        if ($up) { if ($isEdit && $beforeImage) delete_upload($beforeImage); $beforeImage = $up['path']; }
        else { $errors[] = $err1; }
    } elseif (!$isEdit) {
        $errors[] = 'Please upload a "before" image.';
    }

    if (!empty($_FILES['after_image']['name'])) {
        $up = handle_image_upload($_FILES['after_image'], 'before-after', $err2);
        if ($up) { if ($isEdit && $afterImage) delete_upload($afterImage); $afterImage = $up['path']; }
        else { $errors[] = $err2; }
    } elseif (!$isEdit) {
        $errors[] = 'Please upload an "after" image.';
    }

    if (!$errors) {
        if ($isEdit) {
            db()->prepare('UPDATE before_after SET project_name=?, description=?, before_image=?, after_image=?, is_published=? WHERE id=?')
                ->execute([$projectName, $description, $beforeImage, $afterImage, $isPublished, $id]);
            flash_set('success', 'Entry updated.');
        } else {
            $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order),0) FROM before_after')->fetchColumn();
            db()->prepare('INSERT INTO before_after (project_name, description, before_image, after_image, sort_order, is_published) VALUES (?,?,?,?,?,?)')
                ->execute([$projectName, $description, $beforeImage, $afterImage, $maxOrder + 1, $isPublished]);
            flash_set('success', 'Entry created.');
        }
        redirect('before-after.php');
    }
    $item = array_merge($item, compact('projectName', 'description', 'isPublished'));
    $item['before_image'] = $beforeImage;
    $item['after_image'] = $afterImage;
}

$adminPageTitle = $isEdit ? 'Edit Before/After' : 'Add Before/After';
$adminActive = 'before-after';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="before-after.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back</a>

<div class="panel">
  <div class="panel__body">
    <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group full">
          <label for="project_name">Project Name <span style="color:var(--accent-2)">*</span></label>
          <input class="form-control" type="text" id="project_name" name="project_name" value="<?= e($item['project_name']) ?>" required>
        </div>
        <div class="form-group full">
          <label for="description">Description</label>
          <textarea class="form-control" id="description" name="description" rows="2"><?= e($item['description']) ?></textarea>
        </div>
        <div class="form-group">
          <label>Before Image <?= $isEdit ? '' : '<span style="color:var(--accent-2)">*</span>' ?></label>
          <div class="image-upload-box" onclick="document.getElementById('before_image').click()">
            <img src="<?= e(img($item['before_image'], 'assets/images/placeholder.jpg')) ?>" alt="">
            <p>Click to upload "before" image</p>
          </div>
          <input type="file" id="before_image" name="before_image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
        <div class="form-group">
          <label>After Image <?= $isEdit ? '' : '<span style="color:var(--accent-2)">*</span>' ?></label>
          <div class="image-upload-box" onclick="document.getElementById('after_image').click()">
            <img src="<?= e(img($item['after_image'], 'assets/images/placeholder.jpg')) ?>" alt="">
            <p>Click to upload "after" image</p>
          </div>
          <input type="file" id="after_image" name="after_image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
        <div class="form-group full">
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_published" value="1" <?= $item['is_published'] ? 'checked' : '' ?>> Published (visible on website)
          </label>
        </div>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> <?= $isEdit ? 'Update Entry' : 'Create Entry' ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
