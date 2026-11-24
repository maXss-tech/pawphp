<?php
/* expects: $title (string), $page (string) */
$u = current_user();
$notes = []; $unread = 0;
if ($u) {
    $st = db()->prepare('SELECT * FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 10');
    $st->execute([$u['id']]); $notes = $st->fetchAll();
    $st = db()->prepare('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0');
    $st->execute([$u['id']]); $unread = (int)$st->fetchColumn();
}
$here = basename($_SERVER['SCRIPT_NAME']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="csrf" content="<?= csrf_token() ?>">
  <title>PawBnB – <?= e($title) ?></title>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body data-page="<?= e($page) ?>">
<header>
  <a href="index.php" class="logo" aria-label="PawBnB home"><img src="assets/logo.png" alt="PawBnB"></a>
  <nav>
    <a href="index.php" class="<?= $here === 'index.php' ? 'on' : '' ?>">Home</a>
    <a href="about.php" class="<?= $here === 'about.php' ? 'on' : '' ?>">About Us</a>
    <a href="support.php" class="<?= $here === 'support.php' ? 'on' : '' ?>">Contact</a>
  </nav>
  <div class="tools">
    <a class="ib" href="support.php" aria-label="Customer service support"><svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2"><rect x="3" y="4" width="18" height="13" rx="3"/><path d="M8 21l2-4M8 9h8M8 12h5"/></svg></a>
    <?php if ($u): ?>
    <button class="ib" id="bell" aria-label="Notifications, <?= $unread ?> unread" aria-expanded="false">
      <svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2"><path d="M6 17V11a6 6 0 0112 0v6l2 2H4zM10 21h4"/></svg>
      <span class="badge" id="badge" style="display:<?= $unread ? 'grid' : 'none' ?>"><?= $unread ?></span>
    </button>
    <?php else: ?>
    <a class="ib" href="login.php?next=<?= urlencode($here) ?>" aria-label="Log in to see notifications"><svg viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="2"><path d="M6 17V11a6 6 0 0112 0v6l2 2H4zM10 21h4"/></svg></a>
    <?php endif; ?>
    <button class="user" id="userBtn" aria-label="Account menu" aria-expanded="false">
      <span class="av"><svg width="22" height="22" viewBox="0 0 24 24" fill="#555"><circle cx="12" cy="8" r="5"/><path d="M3 22c0-5 4-8 9-8s9 3 9 8z"/></svg></span>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="3"><path d="M5 9l7 7 7-7"/></svg>
    </button>

    <?php if ($u): ?>
    <div class="dd" id="notePanel">
      <h4>Notifications <button id="markAll" type="button">Mark all as read</button></h4>
      <?php if ($notes): foreach ($notes as $n): ?>
        <button type="button" class="nt<?= $n['is_read'] ? '' : ' un' ?>" data-id="<?= (int)$n['id'] ?>" data-href="<?= e($n['href'] ?? '') ?>">
          <b><?= e($n['title']) ?></b><span><?= e($n['body']) ?></span>
        </button>
      <?php endforeach; else: ?><p class="empty" style="padding:10px">No notifications yet.</p><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="dd" id="userPanel">
      <?php if ($u): ?>
        <div class="who"><b><?= e($u['name']) ?></b><br><?= e($u['email']) ?></div>
        <a href="my-bookings.php">My bookings</a>
        <a href="pet-profile.php">My pet profiles</a>
        <?php if ($u['role'] === 'admin'): ?><a href="admin.php">Admin dashboard</a><?php endif; ?>
        <a href="logout.php">Log out</a>
      <?php else: ?>
        <a href="login.php">Log in</a><a href="login.php?mode=up">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</header>
