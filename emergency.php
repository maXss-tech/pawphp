<?php
require 'config.php';
$u = require_login();
$err = ''; $sent = false;
$f = ['name' => $u['name'], 'contact' => '', 'location' => '', 'pet' => 'Dog', 'situation' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $f = ['name' => trim($_POST['name'] ?? ''), 'contact' => trim($_POST['contact'] ?? ''), 'location' => trim($_POST['location'] ?? ''),
          'pet' => $_POST['pet'] ?? '', 'situation' => trim($_POST['situation'] ?? '')];
    if (in_array('', [$f['name'], $f['contact'], $f['location'], $f['situation']], true))
        $err = 'Fill in your name, contact number, location and the situation.';
    elseif (!in_array($f['pet'], PET_TYPES, true)) $err = 'Select a pet type.';
    elseif (!preg_match('/^[0-9+\-\s()]{7,40}$/', $f['contact'])) $err = 'Enter a valid contact number.';
    else {
        db()->prepare('INSERT INTO emergency_requests (user_id,name,contact,location,pet_type,situation) VALUES (?,?,?,?,?,?)')
            ->execute([$u['id'], mb_substr($f['name'], 0, 100), $f['contact'], mb_substr($f['location'], 0, 200), $f['pet'], mb_substr($f['situation'], 0, 500)]);
        notify($u['id'], 'Help is on the way! 🚨', 'Congrats, you just sent an emergency request for your ' . strtolower($f['pet']) . '. Keep your phone nearby, help is coming.');
        $sent = true;
    }
}
$title = 'Emergency Assistance'; $page = 'emergency';
include 'includes/header.php';
?>
<main>
  <section>
    <div class="band" style="min-height:520px">
      <h2 class="rv">Emergency Assistance</h2>
      <p class="sub">If your pet is in immediate danger, go to the nearest veterinary clinic first.</p>
      <div class="rg" style="margin-top:14px">
        <div class="card" style="padding:22px">
          <h3 style="margin:0 0 10px;color:var(--g)">While you get help</h3>
          <ul class="tips">
            <li>Stay calm and keep your pet warm and quiet.</li>
            <li>Do not give human medicine or force food or water.</li>
            <li>Note when symptoms started and anything your pet ate.</li>
            <li>Bring vaccination records and your booking details.</li>
          </ul>
        </div>
        <div class="card" style="padding:22px">
        <?php if ($sent): ?>
          <div class="ck" style="width:56px;height:56px;border-radius:50%;background:var(--g);color:#fff;font-size:30px;display:grid;place-items:center;margin:0 auto 12px">✓</div>
          <h3 style="text-align:center;color:var(--g);margin:0 0 8px">Request sent</h3>
          <p style="text-align:center">Keep your phone nearby, <?= e($f['name']) ?>. If your pet is in immediate danger, go to the nearest veterinary clinic now.</p>
        <?php else: ?>
          <h3 style="margin:0 0 12px;color:var(--g)">Request assistance</h3>
          <form method="post">
            <?= csrf_field() ?>
            <div class="grid" style="grid-template-columns:1fr 1fr;gap:12px 18px">
              <div><label for="en">Your Name</label><input class="f" id="en" name="name" autocomplete="name" value="<?= e($f['name']) ?>" required></div>
              <div><label for="ec">Contact Number</label><input class="f" id="ec" name="contact" type="tel" autocomplete="tel" value="<?= e($f['contact']) ?>" required></div>
              <div><label for="el">Current Location</label><input class="f" id="el" name="location" placeholder="Address or landmark" value="<?= e($f['location']) ?>" required></div>
              <div><label for="ep">Pet Type</label><select class="f" id="ep" name="pet"><?php foreach (PET_TYPES as $t): ?><option<?= $f['pet'] === $t ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
              <div style="grid-column:1/-1"><label for="es">What is happening?</label><input class="f" id="es" name="situation" maxlength="500" placeholder="Describe the situation" value="<?= e($f['situation']) ?>" required></div>
            </div>
            <p class="err" role="alert"><?= e($err) ?></p>
            <div class="btns" style="margin-top:12px"><button class="cf" type="submit">Send Request ➔</button></div>
          </form>
        <?php endif; ?>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
