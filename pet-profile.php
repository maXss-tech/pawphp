<?php
require 'config.php';
$u = require_login();
$err = '';
$f = ['name' => '', 'type' => '', 'breed' => '', 'age' => '', 'size' => '', 'vac' => '', 'notes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'delete') {
        db()->prepare('DELETE FROM pets WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $u['id']]);
        flash('Pet profile deleted.');
        redirect('pet-profile.php');
    }
    foreach ($f as $k => $_) $f[$k] = trim((string)($_POST[$k] ?? ''));
    $vacOpts = ['Yes, up to date', 'Partially', 'No'];
    if ($f['name'] === '' || !in_array($f['type'], PET_TYPES, true)) $err = 'Pet name and type are required.';
    elseif (mb_strlen($f['name']) > 80) $err = 'Pet name is too long.';
    elseif ($f['age'] !== '' && (!is_numeric($f['age']) || $f['age'] < 0 || $f['age'] > 99)) $err = 'Enter a valid age.';
    elseif ($f['vac'] !== '' && !in_array($f['vac'], $vacOpts, true)) $err = 'Invalid vaccination option.';
    else {
        db()->prepare('INSERT INTO pets (user_id,name,type,breed,age_years,size_weight,vaccinated,notes) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$u['id'], $f['name'], $f['type'], $f['breed'] ?: null, $f['age'] !== '' ? $f['age'] : null,
                       $f['size'] ?: null, $f['vac'] ?: null, mb_substr($f['notes'], 0, 255) ?: null]);
        notify($u['id'], 'Congrats! 🎉', 'You just made a pet profile for ' . $f['name'] . '. ' . $f['name'] . ' is ready to use for your next booking.');
        flash('Pet profile saved.');
        redirect('pet-profile.php');
    }
}
$st = db()->prepare('SELECT * FROM pets WHERE user_id=? ORDER BY created_at DESC');
$st->execute([$u['id']]);
$pets = $st->fetchAll();
$title = 'Create Pet Profile'; $page = 'pets';
include 'includes/header.php';
?>
<main>
  <section>
    <div class="band" style="min-height:520px">
      <h2 class="rv">Create Pet Profile</h2>
      <p class="sub">Save your pet’s details once and reuse them on every booking.</p>
      <form method="post">
        <?= csrf_field() ?>

        <div style="text-align:center;margin:0 0 20px">
  <div style="display:inline-block">
    <img id="camPreview" src="" alt="Pet photo" style="width:140px;height:140px;object-fit:cover;border-radius:50%;background:#fff;border:2px dashed #ccc;display:none">
    <div id="camPlaceholder" style="width:140px;height:140px;border-radius:50%;background:#fff;border:2px dashed #ccc;display:flex;align-items:center;justify-content:center;font-size:40px">🐾</div>
  </div>
  <div style="margin-top:10px;display:flex;gap:8px;justify-content:center">
    <button type="button" id="openCam" style="background:#2e4a2f;color:#fff;border:0;padding:10px 18px;border-radius:24px;cursor:pointer">📷 Take Photo</button>
    <button type="button" id="takePhoto" style="background:#ff5a5f;color:#fff;border:0;padding:10px 18px;border-radius:24px;cursor:pointer;display:none">📸 Snap</button>
    <button type="button" id="retake" style="background:#fff;border:1px solid #ddd;padding:10px 18px;border-radius:24px;cursor:pointer;display:none">↩ Retake</button>
  </div>
  <video id="camVideo" autoplay playsinline style="width:100%;max-width:340px;border-radius:12px;background:#000;display:none;margin:12px auto 0"></video>
  <canvas id="camCanvas" style="display:none"></canvas>
  <input type="hidden" name="photo_data" id="photoData">
</div>

        <div class="grid" style="margin-top:18px">
          <div><label for="pname">Pet Name</label><input class="f" id="pname" name="name" placeholder="e.g. Biscuit" value="<?= e($f['name']) ?>" required></div>
          <div><label for="ptype2">Pet Type</label><select class="f" id="ptype2" name="type" required><option value="">Select Type</option><?php foreach (PET_TYPES as $t): ?><option<?= $f['type'] === $t ? ' selected' : '' ?>><?= $t ?></option><?php endforeach; ?></select></div>
          <div><label for="pbreed">Breed</label><input class="f" id="pbreed" name="breed" placeholder="e.g. Golden Retriever" value="<?= e($f['breed']) ?>"></div>
          <div><label for="page2">Age (years)</label><input class="f" id="page2" name="age" type="number" min="0" step="0.5" placeholder="Quantity Input" value="<?= e($f['age']) ?>"></div>
          <div><label for="pw">Size and Weight</label><input class="f" id="pw" name="size" placeholder="e.g. Small, 10kg" value="<?= e($f['size']) ?>"></div>
          <div><label for="pvac">Vaccinated</label><select class="f" id="pvac" name="vac"><option value="">Select</option><?php foreach (['Yes, up to date', 'Partially', 'No'] as $o): ?><option<?= $f['vac'] === $o ? ' selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></div>
          <div style="grid-column:1/-1"><label for="pnotes">Notes (diet, allergies, behavior)</label><input class="f" id="pnotes" name="notes" maxlength="255" placeholder="Optional" value="<?= e($f['notes']) ?>"></div>
        </div>
        <p class="err" role="alert"><?= e($err) ?></p>
        <?= flash_show() ?>
        <button class="go" type="submit" style="margin:20px auto">Save Pet Profile <span>➨</span></button>
      </form>
      <h3 style="color:var(--g);margin:10px 0">Saved profiles</h3>
      <div class="plist">
        <?php if (!$pets): ?><p class="empty">No pet profiles yet.</p><?php endif; ?>
        <?php foreach ($pets as $p): ?>
          <div class="pc">
            <form method="post" onsubmit="return confirm('Delete this profile?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><button aria-label="Delete <?= e($p['name']) ?>">Delete</button></form>
            <b><?= e($p['name']) ?></b><?= e($p['type']) ?><?= $p['breed'] ? ' · ' . e($p['breed']) : '' ?><br>
            <?= $p['age_years'] !== null ? e(rtrim(rtrim($p['age_years'], '0'), '.')) . ' yrs · ' : '' ?><?= e($p['size_weight'] ?? 'Size not set') ?><br>
            Vaccinated: <?= e($p['vaccinated'] ?? 'Not set') ?><?= $p['notes'] ? '<br>' . e($p['notes']) : '' ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
<script>
let camStream = null;
const v = document.getElementById('camVideo');
const c = document.getElementById('camCanvas');
const p = document.getElementById('camPreview');
const photoData = document.getElementById('photoData');
document.getElementById('openCam').onclick = async () => {
  try {
    camStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    v.srcObject = camStream;
    v.style.display = 'block';
    p.style.display = 'none';
    document.getElementById('takePhoto').style.display = 'inline-block';
    document.getElementById('openCam').style.display = 'none';
    document.getElementById('camPlaceholder').style.display = 'none';
  } catch (e) {
    alert('Please Allow camera permission.');
  }
};
document.getElementById('takePhoto').onclick = () => {
  c.width = v.videoWidth; c.height = v.videoHeight;
  c.getContext('2d').drawImage(v, 0, 0);
  const data = c.toDataURL('image/jpeg', 0.9);
  photoData.value = data;
  p.src = data; p.style.display = 'block';
  document.getElementById('camPlaceholder').style.display = 'none';
  v.style.display = 'none';
  if (camStream) camStream.getTracks().forEach(t => t.stop());
  document.getElementById('takePhoto').style.display = 'none';
  document.getElementById('retake').style.display = 'inline-block';
};
document.getElementById('retake').onclick = () => {
  photoData.value = ''; p.style.display = 'none';
  document.getElementById('retake').style.display = 'none';
  document.getElementById('openCam').style.display = 'inline-block';
  document.getElementById('openCam').click();
};
</script>
<?php include 'includes/footer.php'; ?>


