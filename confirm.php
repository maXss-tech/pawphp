<?php
require 'config.php';
$u = require_login();
$st = db()->prepare('SELECT * FROM bookings WHERE id=? AND user_id=?');
$st->execute([(int)($_GET['id'] ?? 0), $u['id']]);
$b = $st->fetch();
if (!$b) redirect('my-bookings.php');
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
        <p>Your stay at <?= e($b['destination']) ?> is booked (ref #<?= (int)$b['id'] ?>).</p>
        <p><?= e(fdate($b['check_in'])) ?> → <?= e(fdate($b['check_out'])) ?> · <?= peso((float)$b['total_price']) ?></p>
        <p>We’ve sent the details to your notifications.</p>
        <button class="cta" style="font-size:18px;border-radius:26px;margin-top:14px" onclick="location.href='index.php'">Back to Home</button>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
