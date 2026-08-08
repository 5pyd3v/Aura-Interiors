<?php
require_once __DIR__ . '/includes/auth.php';

$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM inquiries WHERE id = ?');
$stmt->execute([$id]);
$inquiry = $stmt->fetch();

if (!$inquiry) {
    flash_set('error', 'Inquiry not found.');
    redirect('inquiries.php');
}

$statuses = ['NEW','CONTACTED','IN DISCUSSION','CONVERTED','NOT INTERESTED','CLOSED'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('inquiry-view.php?id=' . $id);
    $status = $_POST['status'] ?? '';
    if (in_array($status, $statuses, true)) {
        db()->prepare('UPDATE inquiries SET status = ? WHERE id = ?')->execute([$status, $id]);
        flash_set('success', 'Status updated.');
        redirect('inquiry-view.php?id=' . $id);
    }
}

$adminPageTitle = 'Inquiry Detail';
$adminActive = 'inquiries';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="inquiries.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back to Inquiries</a>

<div class="panel">
  <div class="panel__head">
    <h2><?= e($inquiry['full_name']) ?></h2>
    <div class="row-actions">
      <a href="tel:<?= e($inquiry['phone']) ?>" class="btn--admin-outline"><i class="fa-solid fa-phone"></i> Call</a>
      <a href="https://wa.me/<?= e(preg_replace('/\D/', '', $inquiry['whatsapp'] ?: $inquiry['phone'])) ?>" target="_blank" class="btn--admin"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
    </div>
  </div>
  <div class="panel__body">
    <div class="admin-form">
      <div class="form-grid">
        <div class="form-group"><label>Phone</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['phone']) ?></div></div>
        <div class="form-group"><label>WhatsApp</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['whatsapp'] ?: '—') ?></div></div>
        <div class="form-group"><label>Email</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['email'] ?: '—') ?></div></div>
        <div class="form-group"><label>City</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['city'] ?: '—') ?></div></div>
        <div class="form-group"><label>Project Type</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['project_type'] ?: '—') ?></div></div>
        <div class="form-group"><label>Property Type</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['property_type'] ?: '—') ?></div></div>
        <div class="form-group"><label>Approximate Area</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['area'] ?: '—') ?></div></div>
        <div class="form-group"><label>Estimated Budget</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['budget'] ?: '—') ?></div></div>
        <div class="form-group"><label>Preferred Start Date</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= e($inquiry['start_date'] ?: '—') ?></div></div>
        <div class="form-group"><label>Submitted</label><div class="form-control" style="background:none;border:none;padding-left:0"><?= date('F j, Y g:i A', strtotime($inquiry['created_at'])) ?></div></div>
      </div>
      <div class="form-group full">
        <label>Message</label>
        <div class="form-control" style="background:var(--surface-2);min-height:80px"><?= nl2br(e($inquiry['message'] ?: 'No message provided.')) ?></div>
      </div>

      <form method="post" class="form-group" style="max-width:320px">
        <?= csrf_field() ?>
        <label for="status">Update Status</label>
        <select class="form-control" id="status" name="status" onchange="this.form.submit()">
          <?php foreach ($statuses as $s): ?>
            <option value="<?= e($s) ?>" <?= $inquiry['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
