<?php
require_once __DIR__.'/../conexion/bootstrap.php';
$messages=['registro'=>'Solicitud de registro procesada. Puedes intentar iniciar sesión.','salida'=>'Sesión cerrada.','eliminada'=>'Cuenta eliminada.'];
$state=$_GET['estado'] ?? '';
$message=is_string($state) ? ($messages[$state] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- ESTILOS -->
    
    
    
    <!-- FIN DE ESTILOS-->
    
    <!-- Etiqueta que no sea escalable -->
    
    <link rel="stylesheet" href="../styles/inde.css">
    <title>Bugambilia</title>
<link rel="stylesheet" href="../styles/seguridad.css"></head>

<body>
<div class="audit-notice"><?= h($message) ?> Usa un correo de prueba. Consulta el <a href="privacidad.html">aviso de privacidad</a>.</div>
<header>
    <input  type="checkbox" name="" id="toggler">
    <label for="toggler" class="fas fa-bars"></label>
    
    <img src="../styles/Imagenes/LBF.png" width="80" height="85" align="left"/> 
    <a href="../index.html" class="logo">Bugambilia<span>.</span></a>

    <nav class="navbar">
        <a href="../index.html" class="nav-link">Inicio</a>
        <a href="../paginas/about_us.html" class="nav-link">Sobre Nosotros</a>
        <a href="../paginas/eventos.html" class="nav-link">Eventos</a>
        <a href="../paginas/FAQS.html" class="nav-link">Ayuda</a>
        <a href="../paginas/contacto.html" class="nav-link">Contacto</a>
        <a href="../paginas/donar.html" class="nav-link">Donar</a>
    </nav>

        

        <div class="iconos">
            <a href="../paginas/carrito.html" class="fas fa-shopping-cart" style='font-size:30px'>Carrito</a>
            <a href="../paginas/inde.php" class="fas fa-user-circle" style='font-size:30px'>Cuenta</a>
        </div>
</header>
<section class="home" id="home">
    <div class="content">
        <h3>Registrate</h3>
        <span>Forma parte de nosotros</span>
        <p>Conoce esta gran comunidad, atrevete a formar parte del cambio</p>
    </div>
</section>
<br><br><br>



<div class="centrar">
<div class="container-form register">
    <div class="information">
        <div class="info-childs">
            <h2>Registrarte</h2>
            <p> Para unirte a nuestra comunidad por favor Iniciar Sesión 
                con tus datos
            </p>
            <input type="button" value="Iniciar Sesión" id="sign-in">
        </div>
    </div>

    <div class="form-information">
        <div class="form-information-childs">
            <h2>Crear una Cuenta</h2>
            <div  class="social-icons">
                <a href="#">Google</a>
                <a href="#">Instagram</a>
                <a href="#">X</a>
            </div>
            <p>o usa tu email para registrarte</p>
            <form class="form"  method="POST" action="../conexion/inde.php">
                <input type="text" placeholder="Nombre" id="nombre" name="nombre" minlength="2" maxlength="80" autocomplete="name" required>
                <input type="email" placeholder="Correo"  name="email" maxlength="254" autocomplete="email" required>
                <input type="password" placeholder="Contraseña"  name="contraseña" minlength="12" maxlength="72" autocomplete="current-password" required> 
                <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="submit" value="Registrarse" name="Registrarse"> 
  
            </form>
        </div>
    </div>   
</div>



<div class="container-form login hide">
    <div class="information">
        <div class="info-childs">
            <h2>¡Bienvenido Nuevamente!</h2>
            <p> Para unirte a nuestra comunidad por favor Iniciar Sesión 
                con tus datos
            </p>
            <input type="button" value="Registrarse" id="sign-up">
        </div>
    </div>

    <div class="form-information">
        <div class="form-information-childs">
            <h2>Iniciar Sesión</h2>
            <div  class="social-icons">
                <a href="#">Google</a>
                <a href="#">Instagram</a>
                <a href="#">X</a>
            </div>
            <p>o iniciar sesion con una cuenta</p>
            <form class="form" method="POST" action="../conexion/inde.php">
                <input type="email" placeholder="Correo"  name="email" maxlength="254" autocomplete="email" required>
                <input type="password" placeholder="Contraseña"  name="contraseña" minlength="12" maxlength="72" autocomplete="current-password" required>
                <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="submit" value="Iniciar Sesión" name="IniciarSesion">   
            </form>
        </div>
    </div>   
</div>
</div>

    <script src="../js/script.js"></script>




<section class="footer">

    <div class="container">
        <div class="row">
            <div class="footer-col">
                <h4>Bugambilia</h4>
                <ul>
                    <li><a href="../index.html">Inicio</a></li>
                    <li><a href="../paginas/about_us.html">Sobre Nosotros</a></li>
                    <li><a href="../paginas/eventos.html">Eventos</a></li>
                </ul>
            </div>
        </div>
    </div>


    <div class="container">
        <div class="row">
            <div class="footer-col">
                <h4>Ayuda</h4>
                <ul>
                    <li><a href="../paginas/FAQS.html">FAQ</a></li>
                    <li><a href="../paginas/inde.php">Registro</a></li>
                    <li><a href="../paginas/donar.html">Donaciones</a></li>
                    <li><a href="../paginas/contacto.html">Contacto</a></li>
                </ul>
            </div>
        </div>
    </div>



    <div class="container">
        <div class="row">
            <div class="footer-col">
                <h4>Compras</h4>
                <ul>
                    <li><a href="../paginas/carrito.html">Plantas del Hogar</a></li>
                </ul>
            </div>
        </div>
    </div>



    <div class="container">
        <div class="row">
            <div class="footer-col">
                <h4>Siguenos</h4>
               <div class="social-link">
                <a href="https://www.facebook.com/profile.php?id=61561767431103">Facebook</a>
                <a href="https://www.instagram.com/bugambilia0/">Instagram</a>
                <a href="https://x.com/Bugambilia0">X</a>

               </div>
            </div>
        </div>
    </div>

</section>


</body>


</html>