<?php 
session_start();
// Si la sesión no ha sido iniciada, redirige a index.php 
if (!isset($_SESSION['user_id'])) 
{ 
    header("Location: ../index.php"); 
    exit; 
}
?> 

<?php
// Realiza la conexión en caso de que la sesión si esté iniciada
require '../loginconnection/conexiondb.php';

// Si viene POST, procesar eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_historial = $_POST['id_historial'];
    $no_control = $_POST['no_control'];
    
    $sql = "DELETE FROM historial_modulos WHERE id_historial = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_historial);
    
    if ($stmt->execute()) {
        echo "
        <html>
        <head>
          <link rel='stylesheet' href='../resources/sweetalert2.css'>
          <script src='../resources/sweetalert2.js'></script>
        </head>
        <body>
          <script>
            Swal.fire({
              title: 'Historial eliminado',
              text: 'El registro del módulo fue eliminado correctamente',
              icon: 'success',
              confirmButtonText: 'Aceptar'
            }).then(() => {
              window.location.href = '../modulo/inscribirAModulo.php?buscar=$no_control';
            });
          </script>
        </body>
        </html>";
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
    exit;
}

// Si no es POST, muestra el formulario
$id_historial = $_GET['id_historial'] ?? null;
if (!$id_historial) {
    echo "<script>alert('No se proporcionó ID de historial'); window.location.href='../modulo/inscribirAModulo.php';</script>";
    exit;
}

// Obtener datos del historial y del alumno
$sql = "SELECT hm.*, a.no_control, a.nombres, a.ap_paterno, a.ap_materno, m.nombre as modulo_nombre 
        FROM historial_modulos hm
        JOIN alumno a ON hm.no_control_Alumno = a.no_control
        JOIN modulo m ON hm.id_modulo = m.id
        WHERE hm.id_historial = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_historial);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Historial no encontrado'); window.location.href='../modulo/inscribirAModulo.php';</script>";
    exit;
}

$historial = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar historial de módulo</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
  <link rel="stylesheet" href="../resources/sweetalert2.css">
  <script src="../resources/sweetalert2.js"></script>
  <script>
    function confirmarEliminacion() {
      return confirm("¿Estás seguro de eliminar este registro de módulo?");
    }
  </script>
</head>
<body>

<header>
  <div class="logo-titulo">
    <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
    <span>Sistema CAADI - Eliminar historial de módulo</span>
  </div>
  
  <nav>
    <a href="#">Inicio</a>

    <div class="submenu">
      <input type="checkbox" id="toggle-submenu-estudiante" hidden>
      <label for="toggle-submenu-estudiante">Estudiante</label>
      <div class="submenu-items">
        <a href="../estudiante/formRegistrarEstudiante.php">Registrar</a>
        <a href="../modulo/inscribirAModulo.php">Inscribir a módulo</a>
        <a href="../estudiante/formConsultarEstudiante.php">Consultar</a>
      </div>
    </div>

    <div class="submenu">
      <input type="checkbox" id="toggle-submenu-coach" hidden>
      <label for="toggle-submenu-coach">Coach</label>
      <div class="submenu-items">
        <a href="../coach/formRegistrarCoach.html">Registrar</a>
        <a href="../coach/formConsultarCoach.php">Consultar</a>
      </div>
    </div>

    <div class="submenu">
      <input type="checkbox" id="toggle-submenu" hidden>
      <label for="toggle-submenu">Entrada/Salida</label>
      <div class="submenu-items">
        <a href="../registros/check-in.php">Check-In</a>
        <a href="../registros/check-out.php">Check-Out</a>
        <a href="#">Modificar</a>
      </div>
    </div>

    <a href="#">Reportes</a>
    <a href="../loginconnection/logout.php">Salir</a>
  </nav>
</header>

<main>
  <form method="POST" action="" onsubmit="return confirmarEliminacion();">
    <h2>Eliminar Historial de Módulo</h2>

    <input type="hidden" name="id_historial" value="<?= htmlspecialchars($historial['id_historial']) ?>">
    <input type="hidden" name="no_control" value="<?= htmlspecialchars($historial['no_control']) ?>">

    <label>No. Control del Alumno:</label>
    <input type="text" value="<?= htmlspecialchars($historial['no_control']) ?>" disabled>

    <label>Nombre del Alumno:</label>
    <input type="text" value="<?= htmlspecialchars($historial['nombres'].' '.$historial['ap_paterno'].' '.$historial['ap_materno']) ?>" disabled>

    <label>Módulo:</label>
    <input type="text" value="<?= htmlspecialchars($historial['modulo_nombre']) ?>" disabled>

    <label>Fecha de Inicio:</label>
    <input type="text" value="<?= htmlspecialchars($historial['fecha_inicio']) ?>" disabled>

    <label>Fecha de Finalización:</label>
    <input type="text" value="<?= htmlspecialchars($historial['fecha_fin']) ?>" disabled>

    <label>Status:</label>
    <input type="text" value="<?= htmlspecialchars($historial['status']) ?>" disabled>

    <label>Horas Acumuladas:</label>
    <input type="text" value="<?= htmlspecialchars($historial['horas_acumuladas']) ?>" disabled>

    <input type="submit" value="Eliminar Registro de Módulo">
  </form>
</main>

<footer>
  Instituto Tecnológico Superior de Guanajuato <br>
  Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
</footer>

</body>
</html>