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

// Si es POST, actualiza los datos
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id_historial = $_POST['id_historial'];
    $id_modulo = $_POST['modulo'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'] ?? null;
    $status = $_POST['status'];
    $horas_acumuladas = $_POST['horas_acumuladas'];

    $sql = "UPDATE historial_modulos SET 
            id_modulo=?, fecha_inicio=?, fecha_fin=?, status=?, horas_acumuladas=?
            WHERE id_historial=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssii", $id_modulo, $fecha_inicio, $fecha_fin, $status, $horas_acumuladas, $id_historial);

    if ($stmt->execute()) {
        echo "
        <html><head>
        <link rel='stylesheet' href='../resources/sweetalert2.css'>
        <script src='../resources/sweetalert2.js'></script>
        </head><body>
        <script>
          Swal.fire({
            title: 'Historial actualizado',
            text: 'Los datos del módulo se guardaron correctamente',
            icon: 'success',
            confirmButtonText: 'Aceptar'
          }).then(() => {
            window.location.href = '../modulo/inscribirAModulo.php?buscar=".$_POST['no_control']."';
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
$id_historial = $_GET['id_historial'] ?? null;
if (!$id_historial) {
    echo "<script>alert('No se proporcionó ID de historial'); window.location.href='../modulo/inscribirAModulo.php';</script>";
    exit;
}

// Obtener datos del historial
$sql = "SELECT hm.*, a.no_control 
        FROM historial_modulos hm
        JOIN alumno a ON hm.no_control_Alumno = a.no_control
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

// Obtener lista de módulos disponibles
$sql_modulos = "SELECT * FROM modulo";
$result_modulos = $conn->query($sql_modulos);
$modulos = [];
if ($result_modulos->num_rows > 0) {
    while($row = $result_modulos->fetch_assoc()) {
        $modulos[] = $row;
    }
}

$stmt->close();
$conn->close();
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Modificar historial de módulo</title>
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
      <span>Sistema CAADI - Editar historial de módulo</span>
    </div>
    
    <nav>
        <a href="../index.php">Inicio</a>

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

        <a href="../registros/exportarExcel.php">Reportes</a>
        <a href="../loginconnection/logout.php">Logout</a>
    </nav>
    </header>

    <!-- Formulario para editar historial -->
    <main>
    <form method="POST" action="ModificarHistorialModulo.php">
      <h2>Modificar Historial de Módulo</h2>

      <input type="hidden" name="id_historial" value="<?= $historial['id_historial'] ?>">
      <input type="hidden" name="no_control" value="<?= $historial['no_control'] ?>">

      <label>Módulo:</label>
      <select name="modulo" required>
        <?php foreach ($modulos as $modulo): ?>
          <option value="<?= $modulo['id'] ?>" <?= $modulo['id'] == $historial['id_modulo'] ? 'selected' : '' ?>>
            <?= $modulo['nombre'] ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label>Fecha de Inicio:</label>
      <input type="date" name="fecha_inicio" value="<?= $historial['fecha_inicio'] ?>" required>

      <label>Status:</label>
      <select name="status" id="status" required>
        <option value="En Curso" <?= $historial['status'] == 'En Curso' ? 'selected' : '' ?>>En Curso</option>
        <option value="Aprobado" <?= $historial['status'] == 'Aprobado' ? 'selected' : '' ?>>Aprobado</option>
        <option value="No Aprobado" <?= $historial['status'] == 'No Aprobado' ? 'selected' : '' ?>>No Aprobado</option>
      </select>

      <label>Fecha de Finalización:</label>
      <input type="date" name="fecha_fin" id="fecha_fin" value="<?= $historial['fecha_fin'] ?>" 
             <?= ($historial['status'] == 'En Curso') ? 'disabled' : '' ?>>

      <label>Horas acumuladas:</label>
      <input type="number" name="horas_acumuladas" value="<?= $historial['horas_acumuladas'] ?>" min="0" required>

      <input type="submit" value="Guardar cambios">
    </form>
    </main>

    <footer>
        Instituto Tecnológico Superior de Guanajuato </br>
        Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
    </footer>

    <script>
      // Habilitar/deshabilitar fecha fin según status
      document.addEventListener("DOMContentLoaded", function() {
        const statusSelect = document.getElementById("status");
        const fechaFinInput = document.getElementById("fecha_fin");

        function toggleFechaFin() {
          if (statusSelect.value === "En Curso") {
            fechaFinInput.disabled = true;
            fechaFinInput.value = "";
          } else {
            fechaFinInput.disabled = false;
          }
        }

        // Ejecutar al cargar
        toggleFechaFin();

        // Ejecutar al cambiar status
        statusSelect.addEventListener("change", toggleFechaFin);
      });
    </script>

</body>
</html>