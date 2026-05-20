<?php 
    session_start();
    //Si la sesión no ha sido iniciada, redirige a index.php 
    if (!isset($_SESSION['user_id'])) 
    { 
        header("Location: ../index.php"); 
        exit; 
    }
?> 

<?php
//realiza la conexión en caso de que la sesión si esté iniciada
require '../loginconnection/conexiondb.php'; 
// Si es POST, actualiza los datos
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $no_control = $_POST['no_control'];
  $nombres = $_POST['nombres'];
  $ap_paterno = $_POST['apellidoPaterno'];
  $ap_materno = $_POST['apellidoMaterno'];

  $sql = "UPDATE coach SET 
    nombres=?, ap_paterno=?, ap_materno=? 
    WHERE no_control=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("sssi", $nombres, $ap_paterno, $ap_materno, $no_control);

  if ($stmt->execute()) {
    echo "
    <html><head>
    <link rel='stylesheet' href='../resources/sweetalert2.css'>
    <script src='../resources/sweetalert2.js'></script>
    </head><body>
    <script>
      Swal.fire({
        title: 'Coach actualizado',
        text: 'Los datos se guardaron correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      }).then(() => {
        window.location.href = 'formConsultarCoach.php';
      });
    </script>
    </body></html>";
  } else {
    echo "Error: " . $stmt->error;
  }
  $stmt->close();
  $conn->close();
  exit;
}

// Si no es POST, muestra el formulario para modificar
$no_control = $_GET['no_control'] ?? null;
if (!$no_control) {
  echo "<script>alert('No se proporcionó número de control'); window.location.href='formConsultarCoach.php';</script>";
  exit;
}

$sql = "SELECT * FROM coach WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $no_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<script>alert('Coach no encontrado'); window.location.href='formConsultarCoach.php';</script>";
  exit;
}

$coach = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Modificar datos de Coach</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
  <link rel="stylesheet" href="../resources/sweetalert2.css">
  <script src="../resources/sweetalert2.js"></script>
</head>
<body>

    <!-- Cabecera y menú-->
    <header>
    <div class="logo-titulo">
      <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Modificación de datos de Coaches</span>
    </div>
    
    <nav>
        <a href="../loginconnection/dashboard.php">Inicio</a>

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
        <a href="../coach/formRegistrarCoach.php">Registrar</a>
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
        <a href="../loginconnection/logout.php">Logout</a>
    </nav>
    </header>

    <!-- Datos del Coach -->
    <main>
    <form method="POST" action="editarCoach.php">
      <h2>Modificar Coach</h2>

      <input type="hidden" name="no_control" value="<?= $coach['no_control'] ?>">

      <label>Nombre(s):</label>
      <input type="text" id="nombres" name="nombres" value="<?= $coach['nombres'] ?>" required>

      <label>Apellido paterno:</label>
      <input type="text" name="apellidoPaterno" value="<?= $coach['ap_paterno'] ?>" required>

      <label>Apellido materno:</label>
      <input type="text" name="apellidoMaterno" value="<?= $coach['ap_materno'] ?>" required>

      <input type="submit" value="Guardar cambios">
    </form>
    </main>

    <footer>
        Instituto Tecnológico Superior de Guanajuato </br>
        Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
    </footer>

</body>
</html>