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

// Si viene POST, procesar eliminación
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $no_control = $_POST['no_control'];
    
    $sql = "DELETE FROM alumno WHERE no_control = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $no_control);
   if ($stmt->execute()) 
  {
    echo "
    <html>
    <head>
      <link rel='stylesheet' href='../resources/sweetalert2.css'>
      <script src='../resources/sweetalert2.js'></script>
    </head>
    <body>
      <script>
        Swal.fire({
          title: 'Estudiante eliminado',
          text: 'Datos eliminados correctamente',
          icon: 'success',
          confirmButtonText: 'Aceptar'
        }).then(() => {
          window.location.href = 'formConsultarEstudiante.php';
        });
      </script>
    </body>
    </html>";
  } 
  else 
  {
    echo "Error: " . $stmt->error;
  }
    $stmt->close();
    $conn->close();
    exit;
}

// Si no es POST, muestra el formulario
$no_control = $_GET['no_control'] ?? null;
if (!$no_control) {
  echo "<script>alert('No se proporcionó número de control'); window.location.href='formConsultarEstudiante.php';</script>";
  exit;
}

$sql = "SELECT * FROM alumno WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $no_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Estudiante no encontrado.";
    exit;
}

$alumno = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar estudiante</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
  <link rel="stylesheet" href="../resources/sweetalert2.css">
  <script src="../resources/sweetalert2.js"></script>
  <script>
    function confirmarEliminacion() {
      return confirm("¿Estás seguro de eliminar este estudiante?");
    }
  </script>
</head>
<body>

<header>
  <div class="logo-titulo">
    <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
    <span>Sistema CAADI - Eliminar estudiante</span>
  </div>
  
  <nav>
        <a href="#">Inicio</a>

    <div class="submenu">
      <input type="checkbox" id="toggle-submenu-estudiante" hidden>
      <label for="toggle-submenu-estudiante">Estudiante</label>
      <div class="submenu-items">
        <a href="../estudiante/formRegistrarEstudiante.html">Registrar</a>
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
        <a href="#">Salir</a>
    </nav>
</header>

<main>
  <form method="POST" action="" onsubmit="return confirmarEliminacion();">
    <h2>Eliminar Estudiante</h2>

    <input type="hidden" name="no_control" value="<?= htmlspecialchars($alumno['no_control']) ?>">

    <label>No. Control:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['no_control']) ?>" disabled>

    <label>Nombre(s):</label>
    <input type="text" value="<?= htmlspecialchars($alumno['nombres']) ?>" disabled>

    <label>Apellido paterno:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['ap_paterno']) ?>" disabled>

    <label>Apellido materno:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['ap_materno']) ?>" disabled>

    <label>Carrera:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['carrera']) ?>" disabled>

    <label>Semestre:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['semestre']) ?>" disabled>

    <label>Grupo:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['grupo']) ?>" disabled>

    <label>Correo:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['email']) ?>" disabled>

    <label>Género:</label>
    <input type="text" value="<?= htmlspecialchars($alumno['genero']) ?>" disabled>

    <input type="submit" value="Eliminar Estudiante">
  </form>
</main>

<footer>
  Instituto Tecnológico Superior de Guanajuato <br>
  Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
</footer>

</body>
</html>

