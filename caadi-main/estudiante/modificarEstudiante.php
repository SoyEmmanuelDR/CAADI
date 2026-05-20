<?php
// Conexión a la base de datos
require 'conexion.php';

// Verifica si llegaron los datos del formulario
if (
  isset($_POST['noControl']) &&
  isset($_POST['nombres']) &&
  isset($_POST['apellidoPaterno']) &&
  isset($_POST['apellidoMaterno']) &&
  isset($_POST['semestre']) &&
  isset($_POST['grupo']) &&
  isset($_POST['email'])
) {
  $noControl = $_POST['noControl'];
  $nombres = $_POST['nombres'];
  $apPaterno = $_POST['apellidoPaterno'];
  $apMaterno = $_POST['apellidoMaterno'];
  $semestre = $_POST['semestre'];
  $grupo = $_POST['grupo'];
  $email = $_POST['email'];

  // Actualiza solo los campos permitidos
  $sql = "UPDATE alumno SET 
            nombres = ?, 
            ap_paterno = ?, 
            ap_materno = ?, 
            semestre = ?, 
            grupo = ?, 
            email = ?
          WHERE no_control = ?";

  $stmt = $pdo->prepare($sql);
  $resultado = $stmt->execute([
    $nombres,
    $apPaterno,
    $apMaterno,
    $semestre,
    $grupo,
    $email,
    $noControl
  ]);

  if ($resultado) {
    echo "<script>alert('Estudiante modificado correctamente'); window.location.href='formRegistrarEstudiante.html';</script>";
  } else {
    echo "<script>alert('No se pudo modificar el estudiante'); history.back();</script>";
  }

} else {
  echo "<script>alert('Faltan datos obligatorios'); history.back();</script>";
}
?>
