<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$m = ['name' => '', 'position' => '', 'bio' => '', 'photo_path' => '', 'facebook_url' => '', 'instagram_url' => '', 'linkedin_url' => '', 'is_published' => 1];
$isEdit = false;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM team_members WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) { $m = $found; $isEdit = true; }
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('team-form.php' . ($id ? '?id=' . $id : ''));

    $name = trim((string) ($_POST['name'] ?? ''));
    $position = trim((string) ($_POST['position'] ?? ''));
    $bio = trim((string) ($_POST['bio'] ?? ''));
    $fb = trim((string) ($_POST['facebook_url'] ?? ''));
    $ig = trim((string) ($_POST['instagram_url'] ?? ''));
    $li = trim((string) ($_POST['linkedin_url'] ?? ''));
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($name === '') $errors[] = 'Name is required.';

    $photoPath = $m['photo_path'];
    if (!empty($_FILES['photo']['name'])) {
        $up = handle_image_upload($_FILES['photo'], 'team', $uploadError);
        if ($up) { if ($isEdit && $photoPath) delete_upload($photoPath); $photoPath = $up['path']; }
        else { $errors[] = $uploadError; }
    }

    if (!$errors) {
        if ($isEdit) {
            db()->prepare('UPDATE team_members SET name=?, position=?, bio=?, photo_path=?, facebook_url=?, instagram_url=?, linkedin_url=?, is_published=? WHERE id=?')
                ->execute([$name, $position, $bio, $photoPath, $fb, $ig, $li, $isPublished, $id]);
            flash_set('success', 'Team member updated.');
        } else {
            $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order),0) FROM team_members')->fetchColumn();
            db()->prepare('INSERT INTO team_members (name, position, bio, photo_path, facebook_url, instagram_url, linkedin_url, sort_order, is_published) VALUES (?,?,?,?,?,?,?,?,?)')
                ->execute([$name, $position, $bio, $photoPath, $fb, $ig, $li, $maxOrder + 1, $isPublished]);
            flash_set('success', 'Team member added.');
        }
        redirect('team.php');
    }
    $m = array_merge($m, compact('name', 'position', 'bio', 'fb', 'ig', 'li', 'isPublished'));
    $m['photo_path'] = $photoPath;
}

$adminPageTitle = $isEdit ? 'Edit Team Member' : 'Add Team Member';
$adminActive = 'team';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="team.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back</a>

<div class="panel">
  <div class="panel__body">
    <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group">
          <label for="name">Name <span style="color:var(--accent-2)">*</span></label>
          <input class="form-control" type="text" id="name" name="name" value="<?= e($m['name']) ?>" required>
        </div>
        <div class="form-group">
          <label for="position">Position</label>
          <input class="form-control" type="text" id="position" name="position" value="<?= e($m['position']) ?>" placeholder="e.g. Senior Interior Designer">
        </div>
        <div class="form-group">
          <label for="facebook_url">Facebook URL</label>
          <input class="form-control" type="text" id="facebook_url" name="facebook_url" value="<?= e($m['facebook_url']) ?>">
        </div>
        <div class="form-group">
          <label for="instagram_url">Instagram URL</label>
          <input class="form-control" type="text" id="instagram_url" name="instagram_url" value="<?= e($m['instagram_url']) ?>">
        </div>
        <div class="form-group">
          <label for="linkedin_url">LinkedIn URL</label>
          <input class="form-control" type="text" id="linkedin_url" name="linkedin_url" value="<?= e($m['linkedin_url']) ?>">
        </div>
        <div class="form-group">
          <label>&nbsp;</label>
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_published" value="1" <?= $m['is_published'] ? 'checked' : '' ?>> Published (visible on website)
          </label>
        </div>
        <div class="form-group full">
          <label for="bio">Biography</label>
          <textarea class="form-control" id="bio" name="bio" rows="4"><?= e($m['bio']) ?></textarea>
        </div>
        <div class="form-group full">
          <label>Profile Photo</label>
          <div class="image-upload-box" onclick="document.getElementById('photo').click()">
            <img src="<?= e(img($m['photo_path'], 'assets/images/avatar-placeholder.jpg')) ?>" alt="">
            <p>Click to upload / replace photo</p>
          </div>
          <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> <?= $isEdit ? 'Update' : 'Create' ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
