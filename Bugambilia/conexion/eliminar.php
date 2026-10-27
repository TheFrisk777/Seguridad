<?php
require_once __DIR__.'/conexion.php';
require_user(); require_post();
$id=$_SESSION['user_id'];
$stmt=db()->prepare('SELECT psswd FROM users WHERE id_user=?');$stmt->bind_param('i',$id);$stmt->execute();$user=$stmt->get_result()->fetch_assoc();
if (!$user || !password_verify(field('contraseña'),$user['psswd'])) { fail(401,'No se pudo confirmar la operación.'); }
$stmt=db()->prepare('DELETE FROM users WHERE id_user=?');$stmt->bind_param('i',$id);$stmt->execute();
clear_session(); header('Location: /Bugambilia/paginas/inde.php?estado=eliminada',true,303);
