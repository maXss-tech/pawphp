<?php
require 'config.php';
$u = require_login();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    db()->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND user_id=? AND status<>'cancelled' AND check_in>CURDATE()")
        ->execute([(int)($_POST['id'] ?? 0), $u['id']]);
    flash('Booking cancelled.');
    redirect('my-bookings.php');
}
$st = db()->prepare('SELECT * FROM bookings WHERE user_id=? ORDER BY created_at DESC');
$st->execute([$u['id']]);
$rows = $st->fetchAll();
$title = 'My Bookings'; $page = 'mybookings';
include 'includes/header.php';
?>
<main>
  <section>
    <div class="band" style="min-height:520px">
      <h2 class="rv">My Bookings</h2>
      <p class="sub">Your pet-friendly stays.</p>
      <?= flash_show() ?>
      <?php if (!$rows): ?><p class="empty">No bookings yet. <a href="booking.php" style="color:var(--g);text-decoration:underline">Book now</a></p>
      <?php else: ?>
      <div class="tblwrap"><table class="tbl">
        <tr><th>Ref</th><th>Destination</th><th>Dates</th><th>Pets</th><th>Total</th><th>Status</th><th></th></tr>
        <?php foreach ($rows as $b): ?>
        <tr>
          <td>#<?= (int)$b['id'] ?></td><td><?= e($b['destination']) ?></td>
          <td><?= e(fdate($b['check_in'])) ?> – <?= e(fdate($b['check_out'])) ?></td>
          <td><?= (int)$b['num_pets'] ?> <?= e($b['pet_type']) ?></td>
          <td><?= peso((float)$b['total_price']) ?></td><td><?= e(ucfirst($b['status'])) ?></td>
          <td><?php if ($b['status'] !== 'cancelled' && $b['check_in'] > date('Y-m-d')): ?>
            <form method="post" onsubmit="return confirm('Cancel this booking?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$b['id'] ?>"><button class="lnk">Cancel</button></form>
          <?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
      </table></div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
