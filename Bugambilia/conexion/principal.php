<?php
include('conexion.php');
session_start();

if(isset($_SESSION['nombredelusuario']))
{
    session_destroy();
    header('location: bug2.html');
    exit;
}
?>
