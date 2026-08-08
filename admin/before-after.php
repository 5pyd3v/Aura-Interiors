<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('before-after.php');
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT before_image, after_image FROM before_after WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['before_image']);
            delete_upload($row['after_image']);
            db()->prepare('DELETE FROM before_after WHERE id = ?')->execute([$id]);
            flash_set('success', 'Entry deleted.');
        }
    } elseif ($action === 'toggle_publish' && $id) {
        db()->prepare('UPDATE before_after SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
    }
    redirect('before-after.php');
}

$items = db()->query('SELECT * FROM before_after ORDER BY sort_order')->fetchAll();

$adminPageTitle = 'Before / After';
$adminActive = 'before-after';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>Before / After Transformations (<?= count($items) ?>)</h2>
    <a href="ba-form.php" class="btn--admin"><i class="fa-solid fa-plus"></i> Add New</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>Before</th><th>After</th><th>Project</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$items): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-left-right"></i><p>No entries yet.</p></div></td></tr><?php endif; ?>
        <?php foreach ($items as $item): ?>
          <tr>
            <td><img class="table-thumb" src="<?= e(img($item['before_image'])) ?>" alt=""></td>
            <td><img class="table-thumb" src="<?= e(img($item['after_image'])) ?>" alt=""></td>
            <td><b><?= e($item['project_name']) ?></b><br><span style="color:var(--ink-faint);font-size:.78rem"><?= e($item['description']) ?></span></td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button class="badge <?= $item['is_published'] ? 'badge--published' : 'badge--draft' ?>" style="border:none;cursor:pointer"><?= $item['is_published'] ? 'Published' : 'Draft' ?></button>
              </form>
            </td>
            <td>
              <div class="row-actions">
                <a href="ba-form.php?id=<?= $item['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="post" data-confirm="Delete this entry?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $item['id'] ?>"><button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
