<?php
require 'config.php';
$u = current_user();
if (empty($_SESSION['chat_sid'])) $_SESSION['chat_sid'] = bin2hex(random_bytes(16));
$sid = $_SESSION['chat_sid'];

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send') {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('{}'); }
    csrf_check();
    $m = mb_substr(trim($_POST['message'] ?? ''), 0, 1000);
    if ($m === '') { http_response_code(400); exit('{}'); }
    $ins = db()->prepare('INSERT INTO chat_messages (user_id,session_id,sender,message) VALUES (?,?,?,?)');
    $ins->execute([$u['id'] ?? null, $sid, 'user', $m]);
    echo json_encode(['ok' => true]);
    exit;
}

if ($action === 'poll') {
    header('Content-Type: application/json');
    $last = (int)($_GET['last_id'] ?? 0);
    $st = db()->prepare('SELECT id,sender,message FROM chat_messages WHERE session_id=? AND id > ? ORDER BY id');
    $st->execute([$sid, $last]);
    echo json_encode($st->fetchAll());
    exit;
}

$st = db()->prepare('SELECT sender,message FROM chat_messages WHERE session_id=? ORDER BY id');
$st->execute([$sid]);
$history = $st->fetchAll();
$title = 'Customer Service'; $page = 'chat';
include 'includes/header.php';
$av = '<span class="av">🐾</span>';
?>
<main>
  <section id="chat">
    <div class="band" style="min-height:480px;padding-top:0">
      <div class="chat">
        <div class="ch">
          <svg viewBox="0 0 24 24" fill="none" stroke="#3b4f2e" stroke-width="1.6"><path d="M4 14v-3a8 8 0 0116 0v3M4 14h3v5H4zM17 14h3v5h-3zM20 19c0 2-3 2-6 2"/></svg>
          <span>Customer Service</span>
        </div>
        <div class="log" id="log" aria-live="polite">
          <div class="m"><?= $av ?><p>How can I help you today?</p></div>
          <?php foreach ($history as $h): ?>
            <div class="m<?= $h['sender'] === 'user' ? ' me' : '' ?>"><?= $av ?><p><?= e($h['message']) ?></p></div>
          <?php endforeach; ?>
        </div>
        <div class="in">
          <input id="msg" placeholder="Type a message here" aria-label="Message" maxlength="1000">
          <button type="button" onclick="send()" aria-label="Send"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#111" stroke-width="1.6"><path d="M3 12l18-9-6 18-3-8z"/></svg></button>
        </div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
