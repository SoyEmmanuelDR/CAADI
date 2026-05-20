<?php
// Conexión a la base de datos
require '../loginconnection/conexiondb.php'; 
include 'funcionesCoach.php';

$no_control = $_POST['no_control'];
$nombres = $_POST['nombres'];
$ap_paterno = $_POST['apellido_paterno'];
$ap_materno = $_POST['apellido_materno'];
$password = $_POST['password'];

if (insertarCoach($conn, $no_control, $nombres, $ap_paterno, $ap_materno, $password)) {
    echo "Coach registrado con éxito";
} 
else 
{
    echo "Error al registrar el coach";
}
?>
