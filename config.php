<?php
declare(strict_types=1);

/* ====== EDIT THESE ====== */
const DB_HOST = 'localhost';
const DB_NAME = 'pawphp2';
const DB_USER = 'root';
const DB_PASS = '';
/* ======================== */

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Database connection failed. Check config.php.');
        }
    }
    return $pdo;
}

function e(?string $s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function peso(float $n): string { return '₱' . number_format($n, 0); }
function fdate(string $d): string { return date('M j, Y', strtotime($d)); }
function redirect(string $url): never { header('Location: ' . $url); exit; }

/* Only allow redirects to local .php pages */
function safe_next(?string $n, string $default = 'index.php'): string {
    return ($n && preg_match('/^[a-z0-9\-]+\.php$/i', $n)) ? $n : $default;
}

/* ---- CSRF ---- */
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_check(): void {
    $t = $_POST['csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        exit('Invalid request token. Go back and try again.');
    }
}

/* ---- Auth ---- */
function current_user(): ?array {
    static $u = false;
    if ($u === false) {
        $u = null;
        if (!empty($_SESSION['uid'])) {
            $st = db()->prepare('SELECT id,name,email,role FROM users WHERE id=?');
            $st->execute([$_SESSION['uid']]);
            $u = $st->fetch() ?: null;
        }
    }
    return $u;
}
function require_login(): array {
    $u = current_user();
    if (!$u) redirect('login.php?next=' . urlencode(basename($_SERVER['SCRIPT_NAME'])));
    return $u;
}
function require_admin(): array {
    $u = require_login();
    if ($u['role'] !== 'admin') { http_response_code(403); exit('Admins only.'); }
    return $u;
}

/* ---- Notifications / flash ---- */
function notify(int $uid, string $title, string $body, ?string $href = null): void {
    db()->prepare('INSERT INTO notifications (user_id,title,body,href) VALUES (?,?,?,?)')->execute([$uid, $title, $body, $href]);
}
function flash(string $msg, bool $bad = false): void { $_SESSION['flash'] = [$msg, $bad]; }
function flash_show(): string {
    if (empty($_SESSION['flash'])) return '';
    [$m, $bad] = $_SESSION['flash']; unset($_SESSION['flash']);
    return '<div class="flash' . ($bad ? ' bad' : '') . '" role="status">' . e($m) . '</div>';
}

const PET_TYPES = ['Dog', 'Cat', 'Other'];
