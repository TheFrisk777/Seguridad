<?php
require_once __DIR__.'/bootstrap.php';
require_user(); require_post(); clear_session();
header('Location: /Bugambilia/paginas/inde.php?estado=salida',true,303);
