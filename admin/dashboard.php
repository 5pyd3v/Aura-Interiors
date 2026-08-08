<?php
require_once __DIR__ . '/includes/auth.php';

$totalInquiries   = (int) db()->query('SELECT COUNT(*) FROM inquiries')->fetchColumn();
$newInquiries     = (int) db()->query("SELECT COUNT(*) FROM inquiries WHERE status = 'NEW'")->fetchColumn();
$totalProjects    = (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$totalServices    = (int) db()->query('SELECT COUNT(*) FROM services')->fetchColumn();
$totalTestimonials = (int) db()->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();
$totalBlogPosts   = (int) db()->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn();
$whatsappClicks   = (int) db()->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'whatsapp_click' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();
$callClicks       = (int) db()->query("SELECT COUNT(*) FROM analytics_events WHERE event_type = 'call_click' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetchColumn();

$recentInquiries = db()->query('SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 8')->fetchAll();

$statusBadge = [
    'NEW' => 'badge--new', 'CONTACTED' => 'badge--contacted', 'IN DISCUSSION' => 'badge--discussion',
    'CONVERTED' => 'badge--converted', 'NOT INTERESTED' => 'badge--not-interested', 'CLOSED' => 'badge--closed',
];

$adminPageTitle = 'Dashboard';
$adminActive = 'dashboard';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="kpi-grid">
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-envelope-open-text"></i></div>
    <b><?= $totalInquiries ?></b>
    <span>Total Inquiries</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-bell"></i></div>
    <b><?= $newInquiries ?></b>
    <span>New Inquiries</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-brands fa-whatsapp"></i></div>
    <b><?= $whatsappClicks ?></b>
    <span>WhatsApp Clicks (30d)</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-phone"></i></div>
    <b><?= $callClicks ?></b>
    <span>Call Clicks (30d)</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-layer-group"></i></div>
    <b><?= $totalProjects ?></b>
    <span>Projects</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-briefcase"></i></div>
    <b><?= $totalServices ?></b>
    <span>Services</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-star"></i></div>
    <b><?= $totalTestimonials ?></b>
    <span>Testimonials</span>
  </div>
  <div class="kpi-card">
    <div class="kpi-card__top"><i class="fa-solid fa-newspaper"></i></div>
    <b><?= $totalBlogPosts ?></b>
    <span>Blog Posts</span>
  </div>
</div>

<div class="panel">
  <div class="panel__head">
    <h2>Recent Inquiries</h2>
    <a href="inquiries.php" class="btn--admin-outline">View All <i class="fa-solid fa-arrow-right"></i></a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead><tr><th>Name</th><th>Phone</th><th>City</th><th>Project Type</th><th>Status</th><th>Date</th><th></th></tr></thead>
      <tbody>
        <?php if (!$recentInquiries): ?>
          <tr><td colspan="7"><div class="empty-state"><i class="fa-solid fa-inbox"></i><p>No inquiries yet.</p></div></td></tr>
        <?php endif; ?>
        <?php foreach ($recentInquiries as $inq): ?>
          <tr>
            <td><b><?= e($inq['full_name']) ?></b></td>
            <td><?= e($inq['phone']) ?></td>
            <td><?= e($inq['city'] ?? '—') ?></td>
            <td><?= e($inq['project_type'] ?? '—') ?></td>
            <td><span class="badge <?= $statusBadge[$inq['status']] ?? 'badge--closed' ?>"><?= e($inq['status']) ?></span></td>
            <td><?= time_ago($inq['created_at']) ?></td>
            <td>
              <div class="row-actions">
                <a href="inquiry-view.php?id=<?= (int) $inq['id'] ?>" title="View"><i class="fa-solid fa-eye"></i></a>
                <a href="tel:<?= e($inq['phone']) ?>" title="Call"><i class="fa-solid fa-phone"></i></a>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
