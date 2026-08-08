<?php
require_once __DIR__ . '/includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('team.php');
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($action === 'delete' && $id) {
        $stmt = db()->prepare('SELECT photo_path FROM team_members WHERE id = ?');
        $stmt->execute([$id]);
        if ($row = $stmt->fetch()) {
            delete_upload($row['photo_path']);
            db()->prepare('DELETE FROM team_members WHERE id = ?')->execute([$id]);
            flash_set('success', 'Team member deleted.');
        }
    } elseif ($action === 'toggle_publish' && $id) {
        db()->prepare('UPDATE team_members SET is_published = 1 - is_published WHERE id = ?')->execute([$id]);
    }
    redirect('team.php');
}

$team = db()->query('SELECT * FROM team_members ORDER BY sort_order')->fetchAll();

$adminPageTitle = 'Team';
$adminActive = 'team';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>Team Members (<?= count($team) ?>)</h2>
    <a href="team-form.php" class="btn--admin"><i class="fa-solid fa-plus"></i> Add Team Member</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th></th><th>Name</th><th>Position</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php if (!$team): ?><tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-people-group"></i><p>No team members yet.</p></div></td></tr><?php endif; ?>
        <?php foreach ($team as $m): ?>
          <tr>
            <td><img class="table-thumb" style="border-radius:50%" src="<?= e(img($m['photo_path'], 'assets/images/avatar-placeholder.jpg')) ?>" alt=""></td>
            <td><b><?= e($m['name']) ?></b></td>
            <td><?= e($m['position']) ?></td>
            <td>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="toggle_publish"><input type="hidden" name="id" value="<?= $m['id'] ?>">
                <button class="badge <?= $m['is_published'] ? 'badge--published' : 'badge--draft' ?>" style="border:none;cursor:pointer"><?= $m['is_published'] ? 'Published' : 'Draft' ?></button>
              </form>
            </td>
            <td>
              <div class="row-actions">
                <a href="team-form.php?id=<?= $m['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                <form method="post" data-confirm="Delete this team member?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $m['id'] ?>"><button class="danger" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
