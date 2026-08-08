<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('projects.php');
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT cover_image FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['cover_image']);
            $imgs = db()->prepare('SELECT image_path FROM project_images WHERE project_id = ?');
            $imgs->execute([$id]);
            foreach ($imgs->fetchAll() as $img) { delete_upload($img['image_path']); }
            db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
            flash_set('success', 'Project deleted.');
        }
    } elseif ($action === 'toggle_publish' && $id) {
        db()->prepare('UPDATE projects SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
    } elseif ($action === 'toggle_featured' && $id) {
        db()->prepare('UPDATE projects SET is_featured = 1 - is_featured WHERE id = ?')->execute([$id]);
    }
    redirect('projects.php');
}

$projects = db()->query('SELECT * FROM projects ORDER BY sort_order, created_at DESC')->fetchAll();

$adminPageTitle = 'Projects';
$adminActive = 'projects';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>All Projects (<?= count($projects) ?>)</h2>
    <a href="project-form.php" class="btn--admin"><i class="fa-solid fa-plus"></i> Add Project</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Location</th><th>Year</th><th>Status</th><th>Featured</th><th></th></tr></thead>
      <tbody>
        <?php if (!$projects): ?><tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-layer-group"></i><p>No projects yet.</p></div></td></tr><?php endif; ?>
        <?php foreach ($projects as $p): ?>
          <tr>
            <td><img class="table-thumb" src="<?= e(img($p['cover_image'])) ?>" alt=""></td>
            <td><b><?= e($p['title']) ?></b></td>
            <td><?= e($p['category']) ?></td>
            <td><?= e($p['location'] ?? '—') ?></td>
            <td><?= e((string) $p['completion_year']) ?></td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="badge <?= $p['is_published'] ? 'badge--published' : 'badge--draft' ?>" style="border:none;cursor:pointer"><?= $p['is_published'] ? 'Published' : 'Draft' ?></button>
              </form>
            </td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_featured"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button style="border:none;background:none;color:<?= $p['is_featured'] ? 'var(--accent-4)' : 'var(--ink-faint)' ?>;font-size:1.1rem"><i class="fa-solid fa-star"></i></button>
              </form>
            </td>
            <td>
              <div class="row-actions">
                <a href="project-form.php?id=<?= $p['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <a href="<?= asset('project-detail.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" title="View"><i class="fa-solid fa-eye"></i></a>
                <form method="post" data-confirm="Delete this project and all its images?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
