<?php
require_once __DIR__ . '/includes/auth.php';

$statuses = ['NEW','CONTACTED','IN DISCUSSION','CONVERTED','NOT INTERESTED','CLOSED'];
$statusBadge = [
    'NEW' => 'badge--new', 'CONTACTED' => 'badge--contacted', 'IN DISCUSSION' => 'badge--discussion',
    'CONVERTED' => 'badge--converted', 'NOT INTERESTED' => 'badge--not-interested', 'CLOSED' => 'badge--closed',
];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    admin_verify_csrf('inquiries.php');
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id && in_array($status, $statuses, true)) {
        db()->prepare('UPDATE inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
        flash_set('success', 'Inquiry status updated.');
    }
    redirect('inquiries.php' . (!empty($_POST['redirect_qs']) ? '?' . $_POST['redirect_qs'] : ''));
}

$filterStatus = $_GET['status'] ?? '';
$q = trim((string) ($_GET['q'] ?? ''));
$perPage = 20;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));

$where = [];
$params = [];
if ($filterStatus !== '' && in_array($filterStatus, $statuses, true)) {
    $where[] = 'status = ?';
    $params[] = $filterStatus;
}
if ($q !== '') {
    $where[] = '(full_name LIKE ? OR phone LIKE ? OR city LIKE ? OR email LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like);
}
$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$countStmt = db()->prepare("SELECT COUNT(*) FROM inquiries $whereSql");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $currentPage] = paginate($total, $perPage, $currentPage);

$stmt = db()->prepare("SELECT * FROM inquiries $whereSql ORDER BY created_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$inquiries = $stmt->fetchAll();

$qs = http_build_query(array_filter(['status' => $filterStatus, 'q' => $q]));

$adminPageTitle = 'Inquiries';
$adminActive = 'inquiries';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="panel">
  <div class="panel__head">
    <h2>All Inquiries (<?= $total ?>)</h2>
    <form method="get" class="filter-toolbar">
      <input type="text" name="q" placeholder="Search name, phone, city..." value="<?= e($q) ?>">
      <select name="status" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <?php foreach ($statuses as $s): ?>
          <option value="<?= e($s) ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn--admin-outline"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Contact</th><th>City</th><th>Project</th><th>Budget</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php if (!$inquiries): ?>
          <tr><td colspan="8"><div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No inquiries match your filters.</p></div></td></tr>
        <?php endif; ?>
        <?php foreach ($inquiries as $inq): ?>
          <tr>
            <td><b><?= e($inq['full_name']) ?></b><br><span style="color:var(--ink-faint);font-size:.78rem"><?= e($inq['email'] ?? '') ?></span></td>
            <td><?= e($inq['phone']) ?><?= $inq['whatsapp'] ? '<br><span style="color:var(--ink-faint);font-size:.78rem">WA: ' . e($inq['whatsapp']) . '</span>' : '' ?></td>
            <td><?= e($inq['city'] ?? '—') ?></td>
            <td><?= e($inq['project_type'] ?? '—') ?></td>
            <td><?= e($inq['budget'] ?? '—') ?></td>
            <td>
              <form method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="id" value="<?= (int) $inq['id'] ?>">
                <input type="hidden" name="redirect_qs" value="<?= e($qs) ?>">
                <select name="status" onchange="this.form.submit()" class="badge <?= $statusBadge[$inq['status']] ?? '' ?>" style="border:none;cursor:pointer">
                  <?php foreach ($statuses as $s): ?>
                    <option value="<?= e($s) ?>" <?= $inq['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </td>
            <td><?= time_ago($inq['created_at']) ?></td>
            <td>
              <div class="row-actions">
                <a href="inquiry-view.php?id=<?= (int) $inq['id'] ?>" title="View"><i class="fa-solid fa-eye"></i></a>
                <a href="tel:<?= e($inq['phone']) ?>" title="Call"><i class="fa-solid fa-phone"></i></a>
                <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $inq['whatsapp'] ?: $inq['phone'])) ?>" target="_blank" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPages > 1): ?>
  <div class="panel__body">
    <div class="pagination">
      <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
        <a href="inquiries.php?<?= e($qs ? $qs . '&' : '') ?>p=<?= $pg ?>" class="<?= $pg === $currentPage ? 'is-active' : '' ?>"><?= $pg ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
