<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('services.php');
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT image_path FROM services WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['image_path']);
            db()->prepare('DELETE FROM services WHERE id = ?')->execute([$id]);
            flash_set('success', 'Service deleted.');
        }
    } elseif ($action === 'toggle_publish' && $id) {
        db()->prepare('UPDATE services SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
        flash_set('success', 'Service updated.');
    } elseif ($action === 'move' && $id) {
        $dir = $_POST['dir'] ?? '';
        $stmt = db()->prepare('SELECT id, sort_order FROM services ORDER BY sort_order');
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $ids = array_column($rows, 'id');
        $pos = array_search($id, $ids, true);
        if ($pos !== false) {
            $swapWith = $dir === 'up' ? $pos - 1 : $pos + 1;
            if (isset($ids[$swapWith])) {
                $a = $rows[$pos]; $b = $rows[$swapWith];
                db()->prepare('UPDATE services SET sort_order = ? WHERE id = ?')->execute([$b['sort_order'], $a['id']]);
                db()->prepare('UPDATE services SET sort_order = ? WHERE id = ?')->execute([$a['sort_order'], $b['id']]);
            }
        }
    }
    redirect('services.php');
}

$services = db()->query('SELECT * FROM services ORDER BY sort_order')->fetchAll();

$adminPageTitle = 'Services';
$adminActive = 'services';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>All Services (<?= count($services) ?>)</h2>
    <a href="service-form.php" class="btn--admin"><i class="fa-solid fa-plus"></i> Add Service</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th></th><th>Image</th><th>Title</th><th>Status</th><th>Order</th><th></th></tr></thead>
      <tbody>
        <?php if (!$services): ?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-briefcase"></i><p>No services yet.</p></div></td></tr><?php endif; ?>
        <?php foreach ($services as $i => $s): ?>
          <tr>
            <td><i class="<?= e($s['icon_class']) ?>"></i></td>
            <td><img class="table-thumb" src="<?= e(img($s['image_path'])) ?>" alt=""></td>
            <td><b><?= e($s['title']) ?></b><br><span style="color:var(--ink-faint);font-size:.78rem"><?= e($s['short_description']) ?></span></td>
            <td><span class="badge <?= $s['is_published'] ? 'badge--published' : 'badge--draft' ?>"><?= $s['is_published'] ? 'Published' : 'Draft' ?></span></td>
            <td>
              <div class="row-actions" style="display:inline-flex">
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="dir" value="up"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button <?= $i === 0 ? 'disabled' : '' ?>><i class="fa-solid fa-arrow-up"></i></button></form>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="dir" value="down"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button <?= $i === count($services) - 1 ? 'disabled' : '' ?>><i class="fa-solid fa-arrow-down"></i></button></form>
              </div>
            </td>
            <td>
              <div class="row-actions">
                <a href="service-form.php?id=<?= $s['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="post" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button title="Toggle Publish"><i class="fa-solid fa-eye<?= $s['is_published'] ? '' : '-slash' ?>"></i></button></form>
                <form method="post" style="display:inline" data-confirm="Delete this service?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
