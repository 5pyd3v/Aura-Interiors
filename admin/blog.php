<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('blog.php');
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT featured_image FROM blog_posts WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['featured_image']);
            db()->prepare('DELETE FROM blog_posts WHERE id = ?')->execute([$id]);
            flash_set('success', 'Article deleted.');
        }
    } elseif ($action === 'toggle_publish' && $id) {
        db()->prepare('UPDATE blog_posts SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
    }
    redirect('blog.php');
}

$posts = db()->query('SELECT bp.*, bc.name AS category_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id ORDER BY bp.published_at DESC, bp.created_at DESC')->fetchAll();

$adminPageTitle = 'Blog';
$adminActive = 'blog';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>All Articles (<?= count($posts) ?>)</h2>
    <a href="blog-form.php" class="btn--admin"><i class="fa-solid fa-plus"></i> Write Article</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>Image</th><th>Title</th><th>Category</th><th>Author</th><th>Views</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php if (!$posts): ?><tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-newspaper"></i><p>No articles yet.</p></div></td></tr><?php endif; ?>
        <?php foreach ($posts as $p): ?>
          <tr>
            <td><img class="table-thumb" src="<?= e(img($p['featured_image'])) ?>" alt=""></td>
            <td><b><?= e($p['title']) ?></b></td>
            <td><?= e($p['category_name'] ?? '—') ?></td>
            <td><?= e($p['author_name']) ?></td>
            <td><?= (int) $p['views'] ?></td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="id" value="<?= $p['id'] ?>">
                <button class="badge <?= $p['is_published'] ? 'badge--published' : 'badge--draft' ?>" style="border:none;cursor:pointer"><?= $p['is_published'] ? 'Published' : 'Draft' ?></button>
              </form>
            </td>
            <td><?= $p['published_at'] ? date('M j, Y', strtotime($p['published_at'])) : '—' ?></td>
            <td>
              <div class="row-actions">
                <a href="blog-form.php?id=<?= $p['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <a href="<?= asset('blog-detail.php?slug=' . urlencode($p['slug'])) ?>" target="_blank" title="View"><i class="fa-solid fa-eye"></i></a>
                <form method="post" data-confirm="Delete this article?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $p['id'] ?>"><button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
