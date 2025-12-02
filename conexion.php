<?php
    $servidor="localhost";
    $nombreBd="coffeeyu";
    $usuario="root";
    $pass="";
    $con = new mysqli($servidor,$usuario,$pass,$nombreBd);
    if($con -> connect_error ){
        die("No se pudo conectar");
        
    }
?>