<?php
declare(strict_types=1);
ini_set('display_errors','0');
ini_set('log_errors','1');
$runtime = getenv('APP_RUNTIME') ?: dirname(__DIR__,2).'/runtime';
if (!is_dir($runtime)) { mkdir($runtime,0700,true); }
ini_set('error_log',$runtime.'/server.log');
set_exception_handler(function(Throwable $e): void {
    // Never log SQL, input, passwords, email addresses or session identifiers.
    error_log('Unhandled application exception: '.get_class($e));
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Servicio no disponible</title><h1>Servicio temporalmente no disponible</h1><p>Intenta de nuevo más tarde.</p></html>';
});
$tls = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$local = getenv('ALLOW_LOCAL_HTTP') === '1' && in_array($_SERVER['REMOTE_ADDR'] ?? '',['127.0.0.1','::1'],true);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'; object-src 'none'");
header('Cache-Control: no-store, private');
if ($tls) { header('Strict-Transport-Security: max-age=31536000'); }
if (!$tls && !$local) { fail(426,'Se requiere HTTPS para utilizar la aplicación.'); }
ini_set('session.use_strict_mode','1');
ini_set('session.use_only_cookies','1');
ini_set('session.save_path',$runtime);
session_name('BUGAMBILIASESSID');
session_set_cookie_params(['lifetime'=>0,'path'=>'/Bugambilia/','secure'=>!$local || $tls,'httponly'=>true,'samesite'=>'Lax']);
session_start();
if (isset($_SESSION['user_id']) && (time()-($_SESSION['last_seen'] ?? 0)>900 || time()-($_SESSION['created_at'] ?? 0)>3600)) {
    clear_session();
    fail(401,'La sesión ha caducado. Inicia sesión de nuevo.');
}
if (isset($_SESSION['user_id'])) { $_SESSION['last_seen']=time(); }
if (!isset($_SESSION['csrf'])) { $_SESSION['csrf']=bin2hex(random_bytes(32)); }
function h(string $value): string { return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8'); }
function fail(int $status,string $message): never {
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="es"><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Bugambilia</title><link rel="stylesheet" href="/Bugambilia/styles/seguridad.css"><main class="panel"><h1>Bugambilia</h1><p role="alert">'.h($message).'</p><a href="/Bugambilia/paginas/inde.php">Volver a acceso</a></main></html>';
    exit;
}
function require_post(): void {
    if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: POST'); fail(405,'Esta operación requiere POST.'); }
    $token=$_POST['csrf'] ?? null;
    if (!is_string($token) || !hash_equals($_SESSION['csrf'],$token)) { fail(403,'Solicitud rechazada por protección CSRF. Recarga el formulario.'); }
}
function require_user(): void { if (!isset($_SESSION['user_id'])) { fail(401,'Inicia sesión para acceder a esta página.'); } }
function clear_session(): void {
    $_SESSION=[];
    $p=session_get_cookie_params();
    setcookie(session_name(),'', ['expires'=>time()-3600,'path'=>$p['path'],'secure'=>$p['secure'],'httponly'=>true,'samesite'=>'Lax']);
    session_destroy();
}
function field(string $name): string {
    $v=$_POST[$name] ?? '';
    if (!is_string($v)) { fail(422,'Formato de campos inválido.'); }
    return $v;
}
