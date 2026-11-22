<?php
require 'config.php';
$u = require_login();
$d = $_SESSION['draft'] ?? [];
$err = '';

$st = db()->prepare('SELECT id,name,type,size_weight FROM pets WHERE user_id=? ORDER BY name');
$st->execute([$u['id']]);
$pets = $st->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $d = [
        'dest'  => trim($_POST['dest'] ?? ''),
        'pax'   => max(1, (int)($_POST['pax'] ?? 1)),
        'np'    => max(1, (int)($_POST['npet'] ?? 1)),
        'type'  => $_POST['ptype'] ?? '',
        'size'  => trim($_POST['psize'] ?? ''),
        'bud'   => trim($_POST['bud'] ?? ''),
        'cin'   => $_POST['cin'] ?? '',
        'cout'  => $_POST['cout'] ?? '',
        'pet_id'=> (int)($_POST['pet_id'] ?? 0) ?: null,
    ];
    $ci = DateTime::createFromFormat('Y-m-d', $d['cin']);
    $co = DateTime::createFromFormat('Y-m-d', $d['cout']);
    if ($d['dest'] === '' || mb_strlen($d['dest']) > 150) $err = 'Enter a destination.';
    elseif (!in_array($d['type'], PET_TYPES, true)) $err = 'Select a pet type.';
    elseif (!$ci || !$co) $err = 'Choose check-in and check-out dates.';
    elseif ($ci < new DateTime('today')) $err = 'Check-in cannot be in the past.';
    elseif ($co <= $ci) $err = 'Check-out must be after check-in.';
    elseif ($d['pet_id']) {
        $ok = false;
        foreach ($pets as $p) if ((int)$p['id'] === $d['pet_id']) $ok = true;
        if (!$ok) $err = 'Selected pet not found.';
    }
    if (!$err) {
        if ($d['size'] === '') $d['size'] = 'Small (10kg)';
        $_SESSION['draft'] = $d;
        redirect('review.php');
    }
}
$title = 'Booking'; $page = 'booking';
include 'includes/header.php';
$v = fn($k, $def = '') => e((string)($d[$k] ?? $def));
?>
<main>
  <section id="booking">
    <form method="post" action="booking.php">
      <?= csrf_field() ?>
      <div class="band">
        <div class="steps"><span class="n on">1</span><span class="t">Booking</span><span class="ln"></span><span class="n">2</span><span class="t">Review</span><span class="ln"></span><span class="n">3</span><span class="t">Confirm</span></div>
        <?php if ($err): ?><div class="flash bad" role="alert"><?= e($err) ?></div><?php endif; ?>
        <div class="grid">
          <div><label for="dest">Destination</label><input class="f" id="dest" name="dest" placeholder="Input Destination" value="<?= $v('dest') ?>" required></div>
          <div><label for="pax">Number of Pax</label><input class="f" id="pax" name="pax" type="number" min="1" placeholder="Quantity Input" value="<?= $v('pax') ?>"></div>
          <div><label for="bud">Budget</label><input class="f" id="bud" name="bud" placeholder="Input Preferred Budget" value="<?= $v('bud') ?>"></div>
          <div><label for="ptype">Pet Type</label>
            <select class="f" id="ptype" name="ptype" required>
              <option value="">Select Type</option>
              <?php foreach (PET_TYPES as $t): ?><option<?= ($d['type'] ?? '') === $t ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
            </select></div>
          <div><label for="cin">Check-In</label><input class="f" id="cin" name="cin" type="date" min="<?= date('Y-m-d') ?>" value="<?= $v('cin') ?>" required></div>
          <div><label for="cout">Check-out</label><input class="f" id="cout" name="cout" type="date" min="<?= date('Y-m-d') ?>" value="<?= $v('cout') ?>" required></div>
          <div><label for="npet">Number of Pets</label><input class="f" id="npet" name="npet" type="number" min="1" placeholder="Quantity Input" value="<?= $v('np') ?>"></div>
          <div><label for="psize">Pet Size and Weight</label><input class="f" id="psize" name="psize" placeholder="e.g. Small, 10kg" value="<?= $v('size') ?>"></div>
          <div><label for="pet_id">Saved Pet (optional)</label>
            <select class="f" id="pet_id" name="pet_id">
              <option value="">None</option>
              <?php foreach ($pets as $p): ?><option value="<?= (int)$p['id'] ?>"<?= (int)($d['pet_id'] ?? 0) === (int)$p['id'] ? ' selected' : '' ?>><?= e($p['name']) ?> (<?= e($p['type']) ?>)</option><?php endforeach; ?>
            </select></div>
        </div>
      </div>
      <button class="go" type="submit">Find Accommodations <span>➨</span></button>
    </form>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
