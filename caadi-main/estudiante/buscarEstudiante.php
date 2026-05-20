<?php
header('Content-Type: application/json');

require '../loginconnection/conexiondb.php'; 

if (isset($_POST['noControl'])) {
  $noControl = $_POST['noControl'];

  $stmt = $pdo->prepare("SELECT * FROM alumno WHERE no_control = ?");
  $stmt->execute([$noControl]);
  $estudiante = $stmt->fetch();

  if ($estudiante) {
    echo json_encode([
      'existe' => true,
      'nombres' => $estudiante['nombres'],
      'apellidoPaterno' => $estudiante['ap_paterno'],
      'apellidoMaterno' => $estudiante['ap_materno'],
      'carrera' => $estudiante['carrera'],
      'semestre' => $estudiante['semestre'],
      'grupo' => $estudiante['grupo'],
      'email' => $estudiante['email'],
      'genero' => $estudiante['genero']
    ]);
  } else {
    echo json_encode(['existe' => false]);
  }
} else {
  echo json_encode(['existe' => false, 'error' => 'No se envió el número de control']);
}
?>
