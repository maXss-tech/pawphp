<?php
require 'config.php';
$u = require_login();
$d = $_SESSION['draft'] ?? null;
if (!$d) redirect('booking.php');

$nights = max(1, (int)(new DateTime($d['cin']))->diff(new DateTime($d['cout']))->days);
$total = round(6249.5 * $nights * (1 + ($d['np'] - 1) * 0.2), 2);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db()->prepare('INSERT INTO bookings (user_id,pet_id,destination,pax,num_pets,pet_type,pet_size,budget,check_in,check_out,nights,total_price,status)
                   VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)')
        ->execute([$u['id'], $d['pet_id'], $d['dest'], $d['pax'], $d['np'], $d['type'], $d['size'], $d['bud'] ?: null, $d['cin'], $d['cout'], $nights, $total, 'confirmed']);
    $id = (int)db()->lastInsertId();
    notify($u['id'], 'Congrats! 🎉', 'You just booked ' . $d['dest'] . ' for you and your pet. Have a paw-some stay!', 'my-bookings.php');
    unset($_SESSION['draft']);
    redirect('confirm.php?id=' . $id);
}
$title = 'Review'; $page = 'review';
include 'includes/header.php';
?>
<main>
  <section id="review">
    <div class="band" style="min-height:520px">
      <div class="steps"><span class="n">1</span><span class="t">Booking</span><span class="ln"></span><span class="n on">2</span><span class="t">Review</span><span class="ln"></span><span class="n">3</span><span class="t">Confirm</span></div>
      <h2 class="rv">Review Your Booking</h2>
      <p class="sub">Please check your details before confirming.</p>
      <div class="rg">
        <div class="card">
          <div class="room" role="img" aria-label="Hotel suite"></div>
          <div class="cap"><span><?= e($d['dest']) ?></span><small><?= e($d['dest']) ?></small></div>
          <div class="map"><span class="vm">📍 View Map</span><span>📍 <?= e($d['dest']) ?></span></div>
        </div>
        <div class="card">
          <div class="facts">
            <div><b>Check-In</b><span><?= e(fdate($d['cin'])) ?></span><span class="mu">2:00 pm</span></div>
            <div><b>Check-Out</b><span><?= e(fdate($d['cout'])) ?></span><span class="mu">12:00 pm</span></div>
            <div><b>Guests</b><span><?= (int)$d['pax'] ?> Adult<?= $d['pax'] > 1 ? 's' : '' ?></span><span><?= (int)$d['np'] ?> Pet<?= $d['np'] > 1 ? 's' : '' ?></span></div>
            <div><b>Budget</b><span><?= e($d['bud'] ?: '₱10,000 — ₱15,000') ?></span></div>
          </div>
          <form method="post" class="rows">
            <?= csrf_field() ?>
            <div>
              <div class="row"><b>Pet Details</b><span><?= (int)$d['np'] ?> <?= e($d['type']) ?><?= $d['np'] > 1 ? 's' : '' ?>, <?= e($d['size']) ?></span></div>
              <div class="row"><b>Nights</b><span><?= $nights ?></span></div>
            </div>
            <div class="total">TOTAL PRICE<strong><span>=</span><span><?= peso($total) ?></span></strong></div>
            <div class="btns">
              <button class="back" type="button" onclick="location.href='booking.php'">⇦ Back</button>
              <button class="cf" type="submit">Confirm Booking ➔</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
