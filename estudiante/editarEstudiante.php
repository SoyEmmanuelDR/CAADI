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
  $carrera = $_POST['carrera'];
  $semestre = $_POST['semestre'];
  $grupo = $_POST['grupo'];
  $email = $_POST['email'];
  $genero = $_POST['genero'];

  $sql = "UPDATE alumno SET 
    nombres=?, ap_paterno=?, ap_materno=?, carrera=?, semestre=?, grupo=?, email=?, genero=? 
    WHERE no_control=?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ssssisssi", $nombres, $ap_paterno, $ap_materno, $carrera, $semestre, $grupo, $email, $genero, $no_control);

  if ($stmt->execute()) {
    echo "
    <html><head>
    <link rel='stylesheet' href='../resources/sweetalert2.css'>
    <script src='../resources/sweetalert2.js'></script>
    </head><body>
    <script>
      Swal.fire({
        title: 'Estudiante actualizado',
        text: 'Los datos se guardaron correctamente',
        icon: 'success',
        confirmButtonText: 'Aceptar'
      }).then(() => {
        window.location.href = 'formConsultarEstudiante.php';
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
  echo "<script>alert('No se proporcionó número de control'); window.location.href='formConsultarEstudiante.php';</script>";
  exit;
}

$sql = "SELECT * FROM alumno WHERE no_control = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $no_control);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
  echo "<script>alert('Estudiante no encontrado'); window.location.href='formConsultarEstudiante.php';</script>";
  exit;
}

$alumno = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Modificar datos de estudiante</title>
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
      <span>Sistema CAADI - Consulta de estudiantes</span>
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
        <a href="../loginconnection/logout.php">Logout</a>
    </nav>
    </header>

    <!-- Datos del Estudiante -->
    <main>
    <form method="POST" action="editarEstudiante.php">
      <h2>Modificar Estudiante</h2>

      <input type="hidden" name="no_control" value="<?= $alumno['no_control'] ?>">

      <label>Nombre(s):</label>
      <input type="text" id="nombres" name="nombres" value="<?= $alumno['nombres'] ?>" required>

      <label>Apellido paterno:</label>
      <input type="text" name="apellidoPaterno" value="<?= $alumno['ap_paterno'] ?>" required>

      <label>Apellido materno:</label>
      <input type="text" name="apellidoMaterno" value="<?= $alumno['ap_materno'] ?>" required>

      <label>Carrera:</label>
      <select name="carrera" required>
        <?php
        $carreras = ["ISIC", "IMCT", "IIND", "ALIM", "GEST"];
        foreach ($carreras as $carrera) {
          $selected = $alumno['carrera'] === $carrera ? "selected" : "";
          echo "<option value='$carrera' $selected>$carrera</option>";
        }
        ?>
      </select>

      <label>Semestre:</label>
      <select name="semestre" required>
        <?php
        for ($i = 0; $i <= 13; $i++) {
          $selected = $alumno['semestre'] == $i ? "selected" : "";
          echo "<option value='$i' $selected>$i</option>";
        }
        ?>
      </select>

      <label>Grupo:</label>
      <select name="grupo" required>
        <?php
        $grupos = ["A", "B", "C", "D", "N"];
        foreach ($grupos as $grupo) {
          $selected = $alumno['grupo'] === $grupo ? "selected" : "";
          echo "<option value='$grupo' $selected>$grupo</option>";
        }
        ?>
      </select>

      <label>Correo:</label>
      <input type="text" name="email" value="<?= $alumno['email'] ?>" required>

      <label>Género:</label>
      <select name="genero" required>
        <option value="M" <?= $alumno['genero'] === 'M' ? 'selected' : '' ?>>Masculino</option>
        <option value="F" <?= $alumno['genero'] === 'F' ? 'selected' : '' ?>>Femenino</option>
        <option value="N" <?= $alumno['genero'] === 'N' ? 'selected' : '' ?>>Prefiere no decirlo</option>
      </select>

      <input type="submit" value="Guardar cambios">
    </form>
    </main>
<script>
  function validarFormularioEstudiante() 
  {
        const regexLetras = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/;
    if (!regexLetras.test(nombres)) {
      alert("El nombre solo debe contener letras.");
      return false;
    }
    if (!regexLetras.test(apellidoPaterno)) {
      alert("El apellido paterno solo debe contener letras.");
      return false;
    }
    if (!regexLetras.test(apellidoMaterno)) {
      alert("El apellido materno solo debe contener letras.");
      return false;
    }

    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!regexCorreo.test(email)) {
      alert("Ingrese un correo válido (debe contener '@' y un dominio).");
      return false;
    }

    return true;
  }
</script>

    <footer>
        Instituto Tecnológico Superior de Guanajuato </br>
        Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
    </footer>

</body>
</html>
