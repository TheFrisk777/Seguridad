<?php
// Required development front controller. Do not serve the repository with a static server.
require_once __DIR__.'/Bugambilia/conexion/bootstrap.php';
$path=rawurldecode(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH) ?: '/');
if (str_contains($path,"\0") || str_contains($path,'\\') || preg_match('~(^|/)\.|/BD(?:/|$)|/runtime(?:/|$)~i',$path)) { fail(404,'Recurso no disponible.'); }
if ($path==='/') { header('Location: /Bugambilia/index.html',true,302);exit; }
if ($path==='/Bugambilia/paginas/inde.html') { header('Location: /Bugambilia/paginas/inde.php',true,302);exit; }
$base=realpath(__DIR__.'/Bugambilia');$file=realpath(__DIR__.$path);
if (!$file || !str_starts_with($file,$base.DIRECTORY_SEPARATOR) || !is_file($file)) { fail(404,'Recurso no disponible.'); }
if (str_ends_with($path,'.php')) {
    $allowed=['/Bugambilia/paginas/inde.php','/Bugambilia/conexion/inde.php','/Bugambilia/conexion/principal.php','/Bugambilia/conexion/logout.php','/Bugambilia/conexion/eliminar.php'];
    if (!in_array($path,$allowed,true)) { fail(404,'Recurso no disponible.'); }
    require $file;return true;
}
if (!in_array(strtolower(pathinfo($file,PATHINFO_EXTENSION)),['html','css','js','png','jpg','jpeg','svg','ico','woff2'],true)) { fail(404,'Recurso no disponible.'); }
// Stream static files here so no-store, CSP and HTTPS checks apply to all responses.
$mime=['html'=>'text/html; charset=utf-8','css'=>'text/css; charset=utf-8','js'=>'text/javascript; charset=utf-8','png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','svg'=>'image/svg+xml','ico'=>'image/x-icon','woff2'=>'font/woff2'];
header('Content-Type: '.$mime[strtolower(pathinfo($file,PATHINFO_EXTENSION))]);readfile($file);return true;
