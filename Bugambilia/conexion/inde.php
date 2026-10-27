<?php
declare(strict_types=1);
require_once __DIR__.'/conexion.php';
require_post();
$email=strtolower(trim(field('email')));
$password=field('contraseña');
if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>254 || strlen($password)<12 || strlen($password)>72) {
    fail(422,'Revisa el correo y usa una contraseña de 12 a 72 bytes.');
}
if (isset($_POST['Registrarse']) && !isset($_POST['IniciarSesion'])) {
    $name=trim(field('nombre'));
    if (mb_strlen($name)<2 || mb_strlen($name)>80 || preg_match('/[<>\x00-\x1f]/u',$name)) { fail(422,'El nombre debe tener de 2 a 80 caracteres y no incluir etiquetas.'); }
    $hash=password_hash($password,PASSWORD_BCRYPT);
    $stmt=db()->prepare('INSERT INTO users (name,email,psswd,rol) VALUES (?,?,?,0)');
    $stmt->bind_param('sss',$name,$email,$hash);
    try { $stmt->execute(); } catch(mysqli_sql_exception $e) { if ($e->getCode()!==1062) { throw $e; } }
    // Same response for existing and new accounts avoids email enumeration.
    header('Location: /Bugambilia/paginas/inde.php?estado=registro',true,303); exit;
}
if (!isset($_POST['IniciarSesion']) || isset($_POST['Registrarse'])) { fail(422,'Selecciona una operación válida.'); }
// Persistent IP budget shared by all login attempts, independent of session cookies.
$key=hash('sha256',$_SERVER['REMOTE_ADDR'] ?? 'unknown');
$c=db();
$c->query('DELETE FROM auth_attempts WHERE expires_at < UNIX_TIMESTAMP()');
$c->begin_transaction();
try {
    $stmt=$c->prepare('INSERT IGNORE INTO auth_attempts (key_hash,attempts,expires_at) VALUES (?,0,UNIX_TIMESTAMP()+900)');
    $stmt->bind_param('s',$key); $stmt->execute();
    $stmt=$c->prepare('SELECT attempts FROM auth_attempts WHERE key_hash=? FOR UPDATE'); $stmt->bind_param('s',$key); $stmt->execute();
    $attempts=(int)$stmt->get_result()->fetch_assoc()['attempts'];
    if ($attempts>=5) { $c->commit(); header('Retry-After: 900'); fail(429,'Demasiados intentos. Espera 15 minutos.'); }
    $stmt=$c->prepare('UPDATE auth_attempts SET attempts=attempts+1 WHERE key_hash=?'); $stmt->bind_param('s',$key);$stmt->execute();
    $c->commit();
} catch(Throwable $e) { $c->rollback(); throw $e; }
$stmt=$c->prepare('SELECT id_user,name,psswd FROM users WHERE email=? LIMIT 1');
$stmt->bind_param('s',$email);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
$dummy='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
$valid=password_verify($password,$user['psswd'] ?? $dummy);
if (!$user || !$valid) { fail(401,'Correo o contraseña incorrectos.'); }
session_regenerate_id(true);
$_SESSION=['user_id'=>(int)$user['id_user'],'name'=>$user['name'],'created_at'=>time(),'last_seen'=>time(),'csrf'=>bin2hex(random_bytes(32))];
header('Location: /Bugambilia/conexion/principal.php',true,303);
