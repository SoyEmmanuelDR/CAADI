<?php 
session_start();
// Si la sesión no ha sido iniciada, redirige a index.php 
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../index.php"); 
    exit; 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require '../loginconnection/conexiondb.php';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    $no_control = $_POST['noControl'] ?? null;
    $nombres = $_POST['nombres'] ?? null;
    $ap_paterno = $_POST['apellidoPaterno'] ?? null;
    $ap_materno = $_POST['apellidoMaterno'] ?? null;
    $carrera = $_POST['carrera'] ?? null;
    $semestre = $_POST['semestre'] ?? null;
    $grupo = $_POST['grupo'] ?? null;
    $email = $_POST['email'] ?? null;
    $genero = $_POST['genero'] ?? null;

    $sql_check = "SELECT no_control FROM alumno WHERE no_control = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $no_control);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "
        <html><head>
        <link rel='stylesheet' href='../resources/sweetalert2.css'>
        <script src='../resources/sweetalert2.js'></script>
        </head><body>
        <script>
        Swal.fire({
            title: 'Registro duplicado',
            text: 'Ya existe un estudiante con ese número de control.',
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = 'formRegistrarEstudiante.php';
        });
        </script>
        </body></html>";
        exit;
    }
    $stmt_check->close();

    $sql_insert = "INSERT INTO alumno (no_control, nombres, ap_paterno, ap_materno, carrera, semestre, grupo, email, genero) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issssisss", $no_control, $nombres, $ap_paterno, $ap_materno, $carrera, $semestre, $grupo, $email, $genero);

    if ($stmt_insert->execute()) {
        echo "
        <html><head>
        <link rel='stylesheet' href='../resources/sweetalert2.css'>
        <script src='../resources/sweetalert2.js'></script>
        </head><body>
        <script>
        Swal.fire({
            title: 'Estudiante registrado',
            text: 'El registro fue exitoso.',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = 'formRegistrarEstudiante.php';
        });
        </script>
        </body></html>";
        exit;
    } else {
        echo "
        <html><head>
        <link rel='stylesheet' href='../resources/sweetalert2.css'>
        <script src='../resources/sweetalert2.js'></script>
        </head><body>
        <script>
        Swal.fire({
            title: 'Error',
            text: 'Error al registrar: " . addslashes($stmt_insert->error) . "',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        }).then(() => {
            window.location.href = 'formRegistrarEstudiante.php';
        });
        </script>
        </body></html>";
        exit;
    }

    $stmt_insert->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CAADI: Registro de estudiante</title>
  <link rel="stylesheet" href="../estilos/style.css" />
</head>
<body>

<header>
  <div class="logo-titulo">
    <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
    <span>Sistema CAADI</span>
  </div>
  
  <nav>
    <a href="../index.php">Inicio</a>

    <div class="submenu">
      <input type="checkbox" id="toggle-submenu-estudiante" hidden>
      <label for="toggle-submenu-estudiante">Estudiante</label>
      <div class="submenu-items">
        <a href="formRegistrarEstudiante.php">Registrar</a>
        <a href="../modulo/inscribirAModulo.php">Inscribir a módulo</a>
        <a href="formConsultarEstudiante.php">Consultar</a>
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
    <a href="#">Salir</a>
  </nav>
</header>

<main>
  <form name="registro" action="" method="POST" onsubmit="return validarFormularioEstudiante();">
    <h2>Registro de estudiante</h2>

    <label for="noControl">No. Control:</label>
    <input type="text" id="noControl" name="noControl" required>

    <label for="nombres">Nombre(s):</label>
    <input type="text" id="nombres" name="nombres" required>

    <label for="apellidoPaterno">Apellido Paterno:</label>
    <input type="text" id="apellidoPaterno" name="apellidoPaterno" required>

    <label for="apellidoMaterno">Apellido Materno:</label>
    <input type="text" id="apellidoMaterno" name="apellidoMaterno" required>

    <label for="carrera">Carrera:</label>
    <select id="carrera" name="carrera" required>
      <option value="ISIC">ISIC</option>
      <option value="IMCT">IMCT</option>
      <option value="IIND">IIND</option>
      <option value="ALIM">ALIM</option>
      <option value="IGEM">GEST</option>
    </select>

    <label for="semestre">Semestre:</label>
    <select id="semestre" name="semestre" required>
      <option value="0">No Aplica</option>
      <option value="1">1</option>
      <option value="2">2</option>
      <option value="3">3</option>
      <option value="4">4</option>
      <option value="5">5</option>
      <option value="6">6</option>
      <option value="7">7</option>
      <option value="8">8</option>
      <option value="9">9</option>
      <option value="10">10</option>
      <option value="11">11</option>
      <option value="12">12</option>
      <option value="13">13</option>
    </select>

    <label for="grupo">Grupo:</label>
    <select id="grupo" name="grupo" required>
      <option value="A">A</option>
      <option value="B">B</option>
      <option value="C">C</option>
      <option value="D">D</option>
      <option value="N">No Aplica</option>
    </select>

    <label for="email">Correo:</label>
    <input type="text" id="email" name="email" required>

    <label for="genero">Género:</label>
    <select id="genero" name="genero" required>
      <option value="M">Masculino</option>
      <option value="F">Femenino</option>
      <option value="N">Prefiere no decirlo</option>
    </select>

    <input type="submit" value="Registrar">
  </form>
</main>

<footer>
  Instituto Tecnológico Superior de Guanajuato <br>
  Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
</footer>

<script>
  function validarFormularioEstudiante() {
    const noControl = document.getElementById("noControl").value.trim();
    const nombres = document.getElementById("nombres").value.trim();
    const apellidoPaterno = document.getElementById("apellidoPaterno").value.trim();
    const apellidoMaterno = document.getElementById("apellidoMaterno").value.trim();
    const email = document.getElementById("email").value.trim();

    const regexNumeros = /^[0-9]+$/;
    if (!regexNumeros.test(noControl)) {
      alert("El número de control solo debe contener números.");
      return false;
    }

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
      alert("Ingrese un correo válido.");
      return false;
    }

    return true;
  }
</script>

</body>
</html>
