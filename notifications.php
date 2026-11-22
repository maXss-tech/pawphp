<?php
require 'config.php';
header('Content-Type: application/json');
$u = current_user();
if (!$u || $_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(403); exit('{"ok":false}'); }
csrf_check();
$a = $_POST['action'] ?? '';
if ($a === 'read_all') {
    db()->prepare('UPDATE notifications SET is_read=1 WHERE user_id=?')->execute([$u['id']]);
} elseif ($a === 'read') {
    db()->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?')->execute([(int)($_POST['id'] ?? 0), $u['id']]);
} else { http_response_code(400); exit('{"ok":false}'); }
echo '{"ok":true}';
