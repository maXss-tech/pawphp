<?php
require 'config.php';
$admin = require_admin();
$tabs = ['bookings' => 'Bookings', 'emergencies' => 'Emergencies', 'pets' => 'Pet Profiles', 'users' => 'Users', 'announce' => 'Announcements'];
$tab = array_key_exists($_GET['tab'] ?? '', $tabs) ? $_GET['tab'] : 'bookings';
$search = trim($_GET['search'] ?? '');
$searchLike = '%' . $search . '%';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $a = $_POST['action'] ?? ''; $id = (int)($_POST['id'] ?? 0);
    switch ($a) {
        case 'booking_status':
            $s = $_POST['status'] ?? '';
            if (in_array($s, ['pending', 'confirmed', 'cancelled'], true)) {
                db()->prepare('UPDATE bookings SET status=? WHERE id=?')->execute([$s, $id]);
                flash('Booking updated.');                
            }
            
            break;
            case 'booking_payment':
        $p = $_POST['status'] ?? '';
        if (in_array($p, ['unpaid', 'paid', 'refunded'], true)) {
            db()->prepare('UPDATE bookings SET payment_status=? WHERE id=?')->execute([$p, $id]);
            flash('Payment updated.');
        }
        break;

    case 'archive_booking':
        db()->prepare('UPDATE bookings SET archived=1 WHERE id=?')->execute([$id]);
        flash('Booking archived.');
        break;

        case 'assign_room':
    $room = trim($_POST['room_number'] ?? '');
    db()->prepare('UPDATE bookings SET room_number=? WHERE id=?')->execute([$room ?: null, $id]);
    flash('Room assigned.');
    break;


        case 'emergency_status':
            $s = $_POST['status'] ?? '';
            if (in_array($s, ['open', 'in_progress', 'resolved'], true)) {
                db()->prepare('UPDATE emergency_requests SET status=? WHERE id=?')->execute([$s, $id]);
                flash('Emergency request updated.');
            }
            break;
        case 'delete_pet':
            db()->prepare('DELETE FROM pets WHERE id=?')->execute([$id]);
            flash('Pet profile deleted.');
            break;
        case 'set_role':
            $r = $_POST['role'] ?? '';
            if ($id !== (int)$admin['id'] && in_array($r, ['user', 'admin'], true)) {
                db()->prepare('UPDATE users SET role=? WHERE id=?')->execute([$r, $id]);
                flash('Role updated.');
            } else flash('You cannot change your own role.', true);
            break;
        case 'delete_user':
            if ($id !== (int)$admin['id']) { db()->prepare('DELETE FROM users WHERE id=?')->execute([$id]); flash('User deleted.'); }
            else flash('You cannot delete yourself.', true);
            break;
        case 'announce':
    $t = trim($_POST['title'] ?? ''); $b = trim($_POST['body'] ?? '');
    if ($t === '' || $b === '') { flash('Title and message are required.', true); break; }
    db()->prepare('INSERT INTO notifications (user_id,title,body,href) SELECT id,?,?,? FROM users')
        ->execute([mb_substr($t, 0, 120), mb_substr($b, 0, 255), 'index.php']);
    flash('Announcement sent to all users.');
    break;
    }
    redirect('admin.php?tab=' . $tab);
}

function status_form(string $action, int $id, string $cur, array $opts): string {
    $h = '<form method="post">' . csrf_field() . '<input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="id" value="' . $id . '"><select name="status" onchange="this.form.submit()">';
    foreach ($opts as $o) $h .= '<option' . ($o === $cur ? ' selected' : '') . '>' . e($o) . '</option>';
    return $h . '</select></form>';
}
function del_form(string $action, int $id, string $label = 'Delete'): string {
    return '<form method="post" onsubmit="return confirm(\'Are you sure?\')">' . csrf_field() . '<input type="hidden" name="action" value="' . $action . '"><input type="hidden" name="id" value="' . $id . '"><button class="lnk">' . $label . '</button></form>';
}

$title = 'Admin Dashboard'; $page = 'admin';
include 'includes/header.php';
?>
<main>
  <section>
    <div class="band" style="min-height:560px">
      <h2 class="rv">Admin Dashboard</h2>
      <p class="sub">Manage bookings, emergency requests, pet profiles, users and announcements.</p>
      <div class="tabs" role="tablist" style="flex-wrap:wrap">
        <?php foreach ($tabs as $k => $l): ?><a href="admin.php?tab=<?= $k ?>" role="tab" aria-selected="<?= $k === $tab ? 'true' : 'false' ?>"><?= $l ?></a><?php endforeach; ?>
      </div>
      <?= flash_show() ?>
      <div class="tblwrap">
<?php if ($tab === 'bookings'):
  $st = db()->prepare("SELECT b.*,u.name AS uname,u.email FROM bookings b JOIN users u ON u.id=b.user_id WHERE b.archived=0 AND (u.name LIKE ? OR u.email LIKE ? OR b.destination LIKE ? OR b.pet_type LIKE ?) ORDER BY b.created_at DESC LIMIT 200");
  $st->execute([$searchLike, $searchLike, $searchLike, $searchLike]);
  $rows = $st->fetchAll(); ?>
        <form method="get" style="margin:0 0 12px;display:flex;gap:8px">
          <input type="hidden" name="tab" value="bookings">
          <input name="search" value="<?= e($search) ?>" placeholder="Search guest, email, Manila, Dog..." style="padding:8px 12px;border-radius:20px;border:1px solid #ddd;flex:1">
          <button>Search</button>
          <?php if ($search): ?><a href="admin.php?tab=bookings">Clear</a><?php endif; ?>
        </form>
        <table class="tbl"><tr><th>#</th><th>Guest</th><th>Destination</th><th>Dates</th><th>Pets</th><th>Total</th><th>Status</th><th>Payment</th><th>Room</th><th>Action</th></tr>
        <?php foreach ($rows as $r): ?><tr><td><?= (int)$r['id'] ?></td><td><?= e($r['uname']) ?><br><small><?= e($r['email']) ?></small></td><td><?= e($r['destination']) ?></td>
          <td><?= e(fdate($r['check_in'])) ?> – <?= e(fdate($r['check_out'])) ?></td><td><?= (int)$r['num_pets'] ?> <?= e($r['pet_type']) ?>, <?= e($r['pet_size']) ?></td>
          <td><?= peso((float)$r['total_price']) ?></td><td><?= status_form('booking_status', (int)$r['id'], $r['status'], ['pending', 'confirmed', 'cancelled']) ?></td>
          <td><?= status_form('booking_payment', (int)$r['id'], $r['payment_status'] ?? 'unpaid', ['unpaid', 'paid', 'refunded']) ?></td>
<td><form method="post" style="display:flex;gap:4px"><?= csrf_field() ?><input type="hidden" name="action" value="assign_room"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input name="room_number" value="<?= e($r['room_number'] ?? '') ?>" placeholder="-" style="width:80px"><button>Save</button></form></td>          <td><form method="post" onsubmit="return confirm('Archive this booking? It will be hidden from the list.')" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="archive_booking"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button>Archive</button></form></td></tr><?php endforeach; ?>        </table>
<?php elseif ($tab === 'emergencies'):
  $rows = db()->query("SELECT * FROM emergency_requests ORDER BY FIELD(status,'open','in_progress','resolved'), created_at DESC LIMIT 200")->fetchAll(); ?>
        <table class="tbl"><tr><th>#</th><th>Name</th><th>Contact</th><th>Location</th><th>Pet</th><th>Situation</th><th>When</th><th>Status</th></tr>
        <?php foreach ($rows as $r): ?><tr><td><?= (int)$r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['contact']) ?></td><td><?= e($r['location']) ?></td><td><?= e($r['pet_type']) ?></td><td><?= e($r['situation']) ?></td>
          <td><?= e(date('M j, g:i a', strtotime($r['created_at']))) ?></td><td><?= status_form('emergency_status', (int)$r['id'], $r['status'], ['open', 'in_progress', 'resolved']) ?></td></tr><?php endforeach; ?>
        </table>
<?php elseif ($tab === 'pets'):
  $st = db()->prepare("SELECT p.*,u.name AS uname FROM pets p JOIN users u ON u.id=p.user_id WHERE (p.name LIKE ? OR u.name LIKE ? OR p.breed LIKE ? OR p.type LIKE ?) ORDER BY p.created_at DESC LIMIT 200");
  $st->execute([$searchLike, $searchLike, $searchLike, $searchLike]);
  $rows = $st->fetchAll(); ?>
        <form method="get" style="margin:0 0 12px;display:flex;gap:8px">
          <input type="hidden" name="tab" value="pets">
          <input name="search" value="<?= e($search) ?>" placeholder="Search pet name, owner, breed..." style="padding:8px 12px;border-radius:20px;border:1px solid #ddd;flex:1">
          <button>Search</button>
          <?php if ($search): ?><a href="admin.php?tab=pets">Clear</a><?php endif; ?>
        </form>
        <table class="tbl"><tr><th>#</th><th>Owner</th><th>Pet</th><th>Type</th><th>Breed</th><th>Size</th><th>Vaccinated</th><th></th></tr>
        <?php foreach ($rows as $r): ?><tr><td><?= (int)$r['id'] ?></td><td><?= e($r['uname']) ?></td><td><?= e($r['name']) ?></td><td><?= e($r['type']) ?></td><td><?= e($r['breed'] ?? '') ?></td><td><?= e($r['size_weight'] ?? '') ?></td><td><?= e($r['vaccinated'] ?? '') ?></td><td><?= del_form('delete_pet', (int)$r['id']) ?></td></tr><?php endforeach; ?>
        </table>
<?php elseif ($tab === 'users'):
  $rows = db()->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC LIMIT 200')->fetchAll(); ?>
        <table class="tbl"><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th></th></tr>
        <?php foreach ($rows as $r): ?><tr><td><?= (int)$r['id'] ?></td><td><?= e($r['name']) ?></td><td><?= e($r['email']) ?></td>
          <td><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="set_role"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><select name="role" onchange="this.form.submit()"><?php foreach (['user', 'admin'] as $o): ?><option<?= $o === $r['role'] ? ' selected' : '' ?>><?= $o ?></option><?php endforeach; ?></select></form></td>
          <td><?= e(fdate($r['created_at'])) ?></td><td><?= (int)$r['id'] !== (int)$admin['id'] ? del_form('delete_user', (int)$r['id']) : '' ?></td></tr><?php endforeach; ?>
        </table>
<?php else: ?>
        <form method="post" class="card" style="padding:22px;max-width:620px">
          <?= csrf_field() ?><input type="hidden" name="action" value="announce">
          <label for="at">Title</label><input class="f" id="at" name="title" maxlength="120" required>
          <label for="ab" style="margin-top:14px">Message</label><input class="f" id="ab" name="body" maxlength="255" required>
          <button class="go" type="submit" style="margin:20px 0 0;width:100%">Send to all users</button>
        </form>
<?php endif; ?>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
