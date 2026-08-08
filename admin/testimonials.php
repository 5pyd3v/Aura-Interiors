<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('testimonials.php');
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT photo_path FROM testimonials WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['photo_path']);
            db()->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
            flash_set('success', 'Testimonial deleted.');
        }
    } elseif ($action === 'toggle_publish' && $id) {
        db()->prepare('UPDATE testimonials SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
    }
    redirect('testimonials.php');
}

$testimonials = db()->query('SELECT * FROM testimonials ORDER BY sort_order DESC, created_at DESC')->fetchAll();

$adminPageTitle = 'Testimonials';
$adminActive = 'testimonials';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>All Testimonials (<?= count($testimonials) ?>)</h2>
    <a href="testimonial-form.php" class="btn--admin"><i class="fa-solid fa-plus"></i> Add Testimonial</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th></th><th>Client</th><th>Review</th><th>Rating</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$testimonials): ?><tr><td colspan="6"><div class="empty-state"><i class="fa-solid fa-star"></i><p>No testimonials yet.</p></div></td></tr><?php endif; ?>
        <?php foreach ($testimonials as $t): ?>
          <tr>
            <td><img class="table-thumb" style="border-radius:50%" src="<?= e(img($t['photo_path'], 'assets/images/avatar-placeholder.jpg')) ?>" alt=""></td>
            <td><b><?= e($t['client_name']) ?></b><br><span style="color:var(--ink-faint);font-size:.78rem"><?= e($t['project_type']) ?> · <?= e($t['location']) ?></span></td>
            <td style="max-width:320px"><?= e(mb_strimwidth($t['review'], 0, 90, '...')) ?></td>
            <td><?php for ($s = 0; $s < (int) $t['rating']; $s++) echo '<i class="fa-solid fa-star" style="color:var(--accent-4)"></i>'; ?></td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="id" value="<?= $t['id'] ?>">
                <button class="badge <?= $t['is_published'] ? 'badge--published' : 'badge--draft' ?>" style="border:none;cursor:pointer"><?= $t['is_published'] ? 'Published' : 'Draft' ?></button>
              </form>
            </td>
            <td>
              <div class="row-actions">
                <a href="testimonial-form.php?id=<?= $t['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="post" data-confirm="Delete this testimonial?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>"><button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
