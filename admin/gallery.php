<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('gallery.php');
    $action = $_POST['action'] ?? '';

    if ($action === 'upload') {
        $category = trim((string) ($_POST['category'] ?? ''));
        $caption = trim((string) ($_POST['caption'] ?? ''));
        if (!empty($_FILES['images']['name'][0])) {
            $count = count($_FILES['images']['name']);
            $maxOrder = (int) db()->query('SELECT COALESCE(MAX(sort_order),0) FROM gallery')->fetchColumn();
            $added = 0;
            for ($i = 0; $i < $count; $i++) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                $file = [
                    'name' => $_FILES['images']['name'][$i], 'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i], 'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i],
                ];
                $up = handle_image_upload($file, 'gallery', $err);
                if ($up) {
                    db()->prepare('INSERT INTO gallery (title, media_type, file_path, category, caption, sort_order) VALUES (?, "image", ?, ?, ?, ?)')
                        ->execute([pathinfo($_FILES['images']['name'][$i], PATHINFO_FILENAME), $up['path'], $category ?: null, $caption ?: null, ++$maxOrder]);
                    $added++;
                }
            }
            flash_set('success', $added . ' image(s) uploaded to gallery.');
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT file_path FROM gallery WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['file_path']);
            db()->prepare('DELETE FROM gallery WHERE id = ?')->execute([$id]);
            flash_set('success', 'Image deleted.');
        }
    }
    redirect('gallery.php');
}

$items = db()->query('SELECT * FROM gallery ORDER BY sort_order DESC, created_at DESC')->fetchAll();

$adminPageTitle = 'Gallery';
$adminActive = 'gallery';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head"><h2>Upload Images</h2></div>
  <div class="panel__body">
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="upload">
      <div class="form-grid">
        <div class="form-group">
          <label for="category">Category</label>
          <input class="form-control" type="text" id="category" name="category" placeholder="e.g. Living Room, Kitchen">
        </div>
        <div class="form-group">
          <label for="caption">Caption</label>
          <input class="form-control" type="text" id="caption" name="caption" placeholder="Optional caption applied to all uploaded images">
        </div>
        <div class="form-group full">
          <label>Images</label>
          <input type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple class="form-control" required>
        </div>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-upload"></i> Upload</button>
    </form>
  </div>
</div>

<div class="panel">
  <div class="panel__head"><h2>Gallery (<?= count($items) ?>)</h2></div>
  <div class="panel__body">
    <?php if (!$items): ?>
      <div class="empty-state"><i class="fa-solid fa-images"></i><p>No images uploaded yet.</p></div>
    <?php else: ?>
    <div class="chip-list">
      <?php foreach ($items as $item): ?>
        <div class="chip-thumb" style="width:150px;height:150px">
          <img src="<?= e(img($item['file_path'])) ?>" alt="">
          <form method="post" data-confirm="Delete this image?">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            <button type="submit"><i class="fa-solid fa-xmark"></i></button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
