<?php
session_start();
// Si la sesión no ha sido iniciada, redirige a index.php
if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit;
}
?> 

<?php
// realiza la conexión en caso de que la sesión si esté iniciada
require '../loginconnection/conexiondb.php';
$buscar = $_GET['buscar'] ?? '';
$noEncontrado = false;

// Inicializar las variables
$no_control = '';
$nombres = '';
$ap_paterno = '';
$ap_materno = '';
$carrera = '';
$semestre = '';
$grupo = '';
$email = '';
$genero = '';
$moduloActual = '';
$cantidadModulosRegistrados = 0;

// Ejecutar la consulta si el input Buscar tiene contenido
if (trim($buscar) !== '') 
{
  $sql = 'SELECT * FROM alumno
  WHERE alumno.no_control = ?';
  $stmt = $conn->prepare($sql);
  $like = $buscar;
  $stmt->bind_param('s', $like);
  $stmt->execute();
  $resultado = $stmt->get_result();

  // Obtener una sola fila
  $fila = $resultado->fetch_assoc();

  if ($fila)  // si encuentra a alguien con ese número de control
  {
    $noEncontrado = false;
    // Inicializar valores con los que encontró en la consulta
    $no_control = $fila['no_control'];
    $nombres = $fila['nombres'];
    $ap_paterno = $fila['ap_paterno'];
    $ap_materno = $fila['ap_materno'];
    $carrera = $fila['carrera'];
    $semestre = $fila['semestre'];
    $grupo = $fila['grupo'];
    $email = $fila['email'];
    $genero = $fila['genero'];

    //para obtener los módulos de un estudiante ya encontrado o que si existe
    $sqlConsultaModulos = 'SELECT * FROM alumno 
    INNER JOIN historial_modulos ON alumno.no_control = historial_modulos.no_control_Alumno
    INNER JOIN modulo ON historial_modulos.id_modulo = modulo.id
    WHERE alumno.no_control = ?';
    $stmtConsultaModulos = $conn->prepare($sqlConsultaModulos);
    $stmtConsultaModulos->bind_param('s', $like);
    $stmtConsultaModulos->execute();
    $resultadoConsultaModulos = $stmtConsultaModulos->get_result();
    
    $cantidadModulosRegistrados = $resultadoConsultaModulos->num_rows; 

  }
  else  
  // si no hay nadie con ese número de control
  {
    $noEncontrado = true;
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Check-in</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
</head>
<body>
    <!-- Cabecera y menú-->
    <header>
    <div class="logo-titulo">
      <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Estudiante: Inscribir a módulo</span>
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

    
    <main style="padding: 30px;">
        <!-- Buscar estudiante por número de control -->
        <div class="contenedor">
            <form class = "buscar" method="GET" action="">
                <input type="text" name="buscar" placeholder="Ingresa No. Control de Estudiante..." value="<?= htmlspecialchars($buscar) ?>">
                <button type="submit">Buscar</button>
            </form>

        </div>

        <!-- Etiqueta que sale cuando el estudiante no existe con ese no_control -->
        <?php if ($noEncontrado): ?>
            <div style="background-color: #ffe0e0; padding: 12px; margin-top: 10px; border: 1px solid #ff9999; border-radius: 6px;">
                <strong>⚠ Estudiante no encontrado.</strong> Verifica el número de control.
            </div>
        <?php endif; ?>

        <!-- Datos del Estudiante -->
        <div class="panel">
            <div class="panel-titulo">DATOS DEL ESTUDIANTE</div>
            <div class="panel-datos">
                <div><strong>No. Control:</strong> <?= $no_control ?> </div>
                <div><strong>Nombre(s):</strong> <?= $nombres ?> </div>
                <div><strong>Apellido Paterno:</strong> <?= $ap_paterno ?> </div>
                <div><strong>Apellido Materno:</strong> <?= $ap_materno ?> </div>
                <div><strong>Carrera:</strong> <?= $carrera ?> </div>
                <div><strong>Semestre:</strong> <?= $semestre ?> </div>
                <div><strong>Grupo:</strong> <?= $grupo ?> </div>
                <div><strong>E-mail:</strong> <?= $email ?> </div>
                <div><strong>Género:</strong> <?= $genero ?> </div>
            </div>
        </div>


        <!-- Historial de módulos 
        Al hacer click en cada módulo nos lleva a ver el detalle de horas por módulo-->
        <div class="panel">
            <div class="panel-titulo">INSCRIBIR A MÓDULO NUEVO</div>
            <form name="registro" action="registrarInscripcionModulo.php" method="POST">
              <label for="modulo">Módulo:</label>
              <select id="modulo" name="modulo" required>
                <option value="1">Básico I</option>
                <option value="2">Básico II</option> 
                <option value="3">Básico III</option>
                <option value="4">Básico IV</option>
                <option value="5">Básico V</option>
                <option value="6">Intermedio I</option>
                <option value="7">Intermedio II</option>
                <option value="8">Intermedio III</option>
                <option value="9">Intermedio IV</option>
                <option value="10">Intermedio V</option>
              </select>

              <label for="fecha_inicio">Fecha de Inicio:</label>
              <input type="date" id="fecha_inicio" name="fecha_inicio" required>

              <label for="status">Status:</label>
              <select id="status" name="status" required>
                <option value="En Curso">En Curso</option>
                <option value="Aprobado">Aprobado</option>
                <option value="No Aprobado">No Aprobado</option>
              </select>

              <!-- Activar cuando status esté como Aprobado o No aprobado-->
              <label for="fecha_fin">Fecha de finalización:</label>
              <input type="date" id="fecha_fin" name="fecha_fin" disabled required>

              <!-- Pasar datos ocultos para el formulario al enviar -->
              <input type="hidden" name="no_control" value="<?php echo $no_control; ?>">

              <input type="submit" value="Registrar" <?= $noEncontrado ? 'disabled' : '' ?>>
            </form>

            <h2 style="text-align: center; color: #1b396a;;">HISTORIAL DE MÓDULOS</h2>
            <?php if ($cantidadModulosRegistrados > 0): ?>
              <!-- Tabla de historial de módulos por Estudiante -->
            <table>
              <thead>
                <tr>
                  <th>Módulo</th>
                  <th>Fecha Inicio</th>
                  <th>Fecha Fin</th>
                  <th>Status</th>
                  <th>Horas acumuladas</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $resultadoConsultaModulos->fetch_assoc()): ?>
    <tr>
        <td><?= $row['nombre'] ?></td>
        <td><?= $row['fecha_inicio'] ?></td>
        <td><?= $row['fecha_fin'] ?></td>
        <td><?= $row['status'] ?></td>
        <td><?= $row['horas_acumuladas'] ?></td>
        <td class="acciones">
            <a class="historialchecks" href='../registros/registrosByModulo.php?buscar=<?= $no_control ?>&id_historial=<?= $row['id_historial'] ?>'>
                <img src="../imagenes/icon-timer.png" alt="Historial Checks" width="20" height="20" title="Historial Checks">
            </a>
            <a class="editar" href="modificarHistorialModulo.php?id_historial=<?= $row['id_historial'] ?>">
                <img src="../imagenes/icon-edit.png" alt="Editar" width="20" height="20" title="Editar">
            </a>
            <a class="eliminar" href="eliminarHistorialModulo.php?id_historial=<?= $row['id_historial'] ?>" onclick="return confirm('¿Estás seguro de eliminar este historial de módulo?')">
                <img src="../imagenes/icon-delete.png" alt="Eliminar" width="20" height="20" title="Eliminar">
            </a>
        </td>
    </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
            <?php else: ?>
              <p style="text-align: center;">Este estudiante no tiene módulos registrados.</p>
            </br>
            <?php endif; ?>
        </div>

      
    </main>

    <footer>
        Instituto Tecnológico Superior de Guanajuato </br>
        Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
    </footer>

    <!-- Script para recargar Datos Estudiante vacíos para cuando Buscar quedó vacío -->
    <script>
        const inputBuscar = document.querySelector('input[name="buscar"]');
        inputBuscar.addEventListener('input', function () 
        {
            if (this.value.trim() === '') 
            {
                window.location.href = window.location.pathname;
            }
        });
    </script>

    <!-- Script para habilitar o deshabilitar fechaFin -->
    <script>
      document.addEventListener("DOMContentLoaded", function () 
      {
        const statusSelect = document.getElementById("status");
        const fechaFinInput = document.getElementById("fecha_fin");

        function toggleFechaFin() 
        {
          const selectedStatus = statusSelect.value;
          if (selectedStatus === "Aprobado" || selectedStatus === "No Aprobado") 
          {
            fechaFinInput.disabled = false;
          }   
          else 
          {
            fechaFinInput.disabled = true;
            fechaFinInput.value = ""; // Limpia el valor si se desactiva
          }
        }

        // Ejecutar al cargar la página
        toggleFechaFin();

        // Ejecutar cada vez que cambie el status
        statusSelect.addEventListener("change", toggleFechaFin);
      });
    </script>
</body>
</html>