<?php
include('conexion.php');

$nombre = $_POST["nombre"];
$apellido = $_POST["apellido"];
$email = $_POST["email"];
$contraseña = $_POST["contraseña"];

//Para Registrarse
if(isset($_POST["Registrarse"]))
{
    $queryusuario = mysqli_query($conn,"SELECT * FROM users WHERE email ='$email'");
    $nr =mysqli_num_rows($queryusuario);
    
    if($nr == 0)
    {
        $pass_fuerte = password_hash($contraseña, PASSWORD_BCRYPT);
        $queryregistrar = "INSERT INTO users (name,email,psswd) values ('$nombre','$email','$pass_fuerte')";

        if(mysqli_query($conn,$queryregistrar))
        {
            echo "<script> alert ('Usuario registrado: $nombre');' </scrip>";
            header ('location: inde.html');

        }
        else{
            header ('location: about_us.html');
        }
    }else{
        header ('location: about_us.html');

    }
}


//Para iniciar sesión
if(isset($_POST["IniciarSesion"])){
    $queryusuario = mysqli_query($conn,"SELECT * FROM login WHERE email = '$email'");
    $nr =mysqli_num_rows($queryusuario);
    $mostrar = mysqli_fetch_array($queryusuario);

    if(($nr == 1) && (password_verify($contraseña,$mostrar['pass']))){
        session_start();
        $_SESSION['nombredelusuario']=$nombre;
        header("Location: principal.php");
    }
    else
    {
        echo "<script> alert ('Usuario o contraseña incorrecto.');window.location = 'inde.html' </scrip>";
    }
}






