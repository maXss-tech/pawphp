<?php
require 'config.php';
$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);
if (current_user()) redirect($next);

$mode = ($_GET['mode'] ?? $_POST['mode'] ?? 'in') === 'up' ? 'up' : 'in';
$err = '';
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $pw = (string)($_POST['password'] ?? '');

    if ($mode === 'up') {
        if ($name === '' || mb_strlen($name) > 100) $err = 'Enter your full name.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Enter a valid email address.';
        elseif (strlen($pw) < 8) $err = 'Password must be at least 8 characters.';
        elseif ($pw !== ($_POST['confirm'] ?? '')) $err = 'Passwords do not match.';
        else {
            $st = db()->prepare('SELECT id FROM users WHERE email=?');
            $st->execute([$email]);
            if ($st->fetch()) $err = 'An account with that email already exists.';
            else {
                db()->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)')
                    ->execute([$name, $email, password_hash($pw, PASSWORD_DEFAULT)]);
                $uid = (int)db()->lastInsertId();
                notify($uid, 'Welcome to PawBnB! 🐾', 'Congrats! You just created your account. Create a pet profile to book faster.', 'pet-profile.php');
                session_regenerate_id(true);
                $_SESSION['uid'] = $uid;
                redirect($next);
            }
        }
    } else {
        $st = db()->prepare('SELECT id,password_hash FROM users WHERE email=?');
        $st->execute([$email]);
        $row = $st->fetch();
        if ($row && password_verify($pw, $row['password_hash'])) {
            if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
                db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($pw, PASSWORD_DEFAULT), $row['id']]);
            }
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)$row['id'];
            redirect($next);
        }
        $err = 'Incorrect email or password.';
    }
}
$q = $next !== 'index.php' ? '&next=' . urlencode($next) : '';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>PawBnB – <?= $mode === 'up' ? 'Sign up' : 'Log in' ?></title>
  <link rel="stylesheet" href="css/styles.css">
  <style>
    body[data-page="login"]{background:#FFFAE6}
    body[data-page="login"] main{padding-top:10px}
    body[data-page="login"] .band{max-width:560px;margin:0 auto}
    .login-logo{text-align:center;padding:28px 0 0}
    .login-logo img{height:58px;width:auto}
  </style>
</head>
<body data-page="login">
  <div class="login-logo"><a href="index.php" aria-label="PawBnB home"><img src="assets/logo.png" alt="PawBnB"></a></div>
  <main>
    <section>
      <div class="band" style="min-height:560px">
        <form class="done" style="text-align:left;margin:20px auto" method="post" action="login.php?mode=<?= $mode ?><?= $q ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="mode" value="<?= $mode ?>">
          <input type="hidden" name="next" value="<?= e($next) ?>">
          <?php if ($next !== 'index.php'): ?><p class="notice" role="status">Please log in to continue.</p><?php endif; ?>
          <div class="tabs" role="tablist">
            <a href="login.php?mode=in<?= $q ?>" role="tab" aria-selected="<?= $mode === 'in' ? 'true' : 'false' ?>">Log in</a>
            <a href="login.php?mode=up<?= $q ?>" role="tab" aria-selected="<?= $mode === 'up' ? 'true' : 'false' ?>">Sign up</a>
          </div>
          <?php if ($mode === 'up'): ?>
            <label for="aname">Full Name</label>
            <input class="f" id="aname" name="name" autocomplete="name" value="<?= e($name) ?>" required>
          <?php endif; ?>
          <label for="aemail" style="margin-top:14px">Email</label>
          <input class="f" id="aemail" name="email" type="email" autocomplete="email" value="<?= e($email) ?>" required>
          <label for="apass" style="margin-top:14px">Password</label>
          <input class="f" id="apass" name="password" type="password" minlength="8" autocomplete="<?= $mode === 'up' ? 'new-password' : 'current-password' ?>" placeholder="At least 8 characters" required>
          <?php if ($mode === 'up'): ?>
            <label for="aconf" style="margin-top:14px">Confirm Password</label>
            <input class="f" id="aconf" name="confirm" type="password" autocomplete="new-password" required>
          <?php endif; ?>
          <p class="err" role="alert"><?= e($err) ?></p>
          <button class="cta" type="submit" style="font-size:18px;border-radius:26px;width:100%;margin-top:8px"><?= $mode === 'up' ? 'Create account' : 'Log in' ?></button>
        </form>
      </div>
    </section>
  </main>
</body>
</html>
