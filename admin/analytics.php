<?php
require_once __DIR__ . '/includes/auth.php';

$days = 14;
$since = date('Y-m-d', strtotime("-$days days"));

$dailyStmt = db()->prepare(
    "SELECT DATE(created_at) AS d, event_type, COUNT(*) AS c FROM analytics_events
     WHERE created_at >= ? GROUP BY DATE(created_at), event_type"
);
$dailyStmt->execute([$since]);
$rawDaily = $dailyStmt->fetchAll();

$dateRange = [];
for ($i = $days - 1; $i >= 0; $i--) {
    $dateRange[date('Y-m-d', strtotime("-$i days"))] = ['page_view' => 0, 'whatsapp_click' => 0, 'call_click' => 0, 'inquiry_submit' => 0, 'project_view' => 0];
}
foreach ($rawDaily as $row) {
    if (isset($dateRange[$row['d']])) {
        $dateRange[$row['d']][$row['event_type']] = (int) $row['c'];
    }
}
$maxDaily = 1;
foreach ($dateRange as $d) { $maxDaily = max($maxDaily, array_sum($d)); }

$totals = [];
foreach (['page_view', 'project_view', 'whatsapp_click', 'call_click', 'inquiry_submit'] as $type) {
    $stmt = db()->prepare("SELECT COUNT(*) FROM analytics_events WHERE event_type = ? AND created_at >= ?");
    $stmt->execute([$type, $since]);
    $totals[$type] = (int) $stmt->fetchColumn();
}

$topProjects = db()->query('SELECT title, slug, views FROM projects ORDER BY views DESC LIMIT 5')->fetchAll();
$topPosts = db()->query('SELECT title, slug, views FROM blog_posts ORDER BY views DESC LIMIT 5')->fetchAll();

$adminPageTitle = 'Analytics';
$adminActive = 'analytics';
require __DIR__ . '/includes/admin-header.php';
?>

<div class="kpi-grid">
  <div class="kpi-card"><div class="kpi-card__top"><i class="fa-solid fa-eye"></i></div><b><?= $totals['page_view'] ?></b><span>Page Views (14d)</span></div>
  <div class="kpi-card"><div class="kpi-card__top"><i class="fa-solid fa-layer-group"></i></div><b><?= $totals['project_view'] ?></b><span>Project Views (14d)</span></div>
  <div class="kpi-card"><div class="kpi-card__top"><i class="fa-brands fa-whatsapp"></i></div><b><?= $totals['whatsapp_click'] ?></b><span>WhatsApp Clicks (14d)</span></div>
  <div class="kpi-card"><div class="kpi-card__top"><i class="fa-solid fa-envelope-open-text"></i></div><b><?= $totals['inquiry_submit'] ?></b><span>Inquiries (14d)</span></div>
</div>

<div class="panel">
  <div class="panel__head"><h2>Activity — Last <?= $days ?> Days</h2></div>
  <div class="panel__body">
    <div style="display:flex;align-items:flex-end;gap:6px;height:180px">
      <?php foreach ($dateRange as $date => $counts): $total = array_sum($counts); $h = max(4, (int) round(($total / $maxDaily) * 160)); ?>
        <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px" title="<?= e($date) ?>: <?= $total ?> events">
          <div style="width:100%;height:<?= $h ?>px;background:var(--gradient-brand);border-radius:6px 6px 0 0"></div>
          <span style="font-size:.62rem;color:var(--ink-faint);writing-mode:vertical-rl;text-orientation:mixed"><?= date('M j', strtotime($date)) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
  <div class="panel">
    <div class="panel__head"><h2>Top Viewed Projects</h2></div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Project</th><th>Views</th></tr></thead>
        <tbody>
          <?php if (!$topProjects): ?><tr><td colspan="2"><div class="empty-state"><p>No data yet.</p></div></td></tr><?php endif; ?>
          <?php foreach ($topProjects as $p): ?>
            <tr><td><?= e($p['title']) ?></td><td><b><?= (int) $p['views'] ?></b></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <div class="panel__head"><h2>Top Viewed Articles</h2></div>
    <div class="table-wrap">
      <table class="admin-table">
        <thead><tr><th>Article</th><th>Views</th></tr></thead>
        <tbody>
          <?php if (!$topPosts): ?><tr><td colspan="2"><div class="empty-state"><p>No data yet.</p></div></td></tr><?php endif; ?>
          <?php foreach ($topPosts as $p): ?>
            <tr><td><?= e($p['title']) ?></td><td><b><?= (int) $p['views'] ?></b></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!setting('google_analytics_id')): ?>
<div class="panel">
  <div class="panel__body">
    <p style="color:var(--ink-faint);font-size:.88rem"><i class="fa-solid fa-circle-info"></i> Add a Google Analytics ID in <a href="settings.php">Settings</a> for deeper, industry-standard analytics alongside these built-in numbers.</p>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
