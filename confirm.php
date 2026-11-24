<?php
require 'config.php';
$u = require_login();
$st = db()->prepare('SELECT * FROM bookings WHERE id=? AND user_id=?');
$st->execute([(int)($_GET['id'] ?? 0), $u['id']]);
$b = $st->fetch();
if (!$b) redirect('my-bookings.php');
// Auto confirmation code - same code every time for this booking
$code = 'PAW-' . strtoupper(substr(md5($b['id'] . '-' . $b['destination'] . '-' . $b['check_in']), 0, 6)) . '-' . str_pad($b['id'], 4, '0', STR_PAD_LEFT);  
$title = 'Confirm'; $page = 'confirm';
include 'includes/header.php';
?>
<main>
  <section id="confirm">
    <div class="band" style="min-height:520px">
      <div class="steps"><span class="n">1</span><span class="t">Booking</span><span class="ln"></span><span class="n">2</span><span class="t">Review</span><span class="ln"></span><span class="n on">3</span><span class="t">Confirm</span></div>
      <div class="done">
        <div class="ck">✓</div>
        <h2 style="margin:0 0 8px">Booking confirmed</h2>
<p>Your stay at <?= e($b['destination']) ?> is booked.</p>
<div style="background:#fff;border:2px dashed #2e4a2f;border-radius:14px;padding:14px 18px;margin:16px 0;display:inline-block">
  <div style="font-size:12px;color:#888;letter-spacing:1px">CONFIRMATION CODE</div>
  <div style="font-size:28px;font-weight:800;letter-spacing:2px;color:#2e4a2f"><?= e($code) ?></div>
  <div style="font-size:12px;color:#888">Show this code at check-in</div>
</div>
<br>
<button onclick="navigator.clipboard.writeText('<?= e($code) ?>');this.textContent='Copied!'" style="background:#f7f2df;border:1px solid #eee;padding:8px 16px;border-radius:20px;cursor:pointer;font-size:13px">📋 Copy Code</button>
        <p><?= e(fdate($b['check_in'])) ?> → <?= e(fdate($b['check_out'])) ?> · <?= peso((float)$b['total_price']) ?></p>
        <p>We’ve sent the details to your notifications.</p>
        <button class="cta" style="font-size:18px;border-radius:26px;margin-top:14px" onclick="location.href='index.php'">Back to Home</button>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
