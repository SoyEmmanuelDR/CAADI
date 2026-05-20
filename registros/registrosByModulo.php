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
require '../loginconnection/conexiondb.php';

$buscar = $_GET['buscar'] ?? '';
$id_historial = $_GET['id_historial'] ?? null;
$noEncontrado = true;
$datosAlumno = null;
$modulo = null;
$registros = [];

// Obtener datos del estudiante
if ($buscar !== '') {
    $sql = "SELECT * FROM alumno WHERE no_control = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $buscar);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datosAlumno = $resultado->fetch_assoc();
    $noEncontrado = !$datosAlumno;
}

// Si se proporciona un id_historial, obtener módulo y registros
if ($id_historial) 
{
    // Datos del módulo y horas acumuladas
    $sql_mod = "SELECT m.nombre AS nombre_modulo, hm.horas_acumuladas
                FROM historial_modulos hm
                JOIN modulo m ON hm.id_modulo = m.id
                WHERE hm.id_historial = ?";
    $stmt_mod = $conn->prepare($sql_mod);
    $stmt_mod->bind_param("i", $id_historial);
    $stmt_mod->execute();
    $modulo = $stmt_mod->get_result()->fetch_assoc();

    // Registros asociados al módulo
    $sql = "SELECT r.id, r.fecha, r.hora_inicio, r.hora_fin, r.horas, r.laboratorio, 
                   c.nombres AS coach, 
                   GROUP_CONCAT(ra.actividad SEPARATOR ', ') AS actividades
            FROM registro r
            LEFT JOIN coach c ON r.no_control_Coach = c.no_control
            LEFT JOIN registro_actividades ra ON r.id = ra.id_registro
            WHERE r.id_historial = ?
            GROUP BY r.id
            ORDER BY r.fecha DESC, r.hora_inicio DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_historial);
    $stmt->execute();
    $registros = $stmt->get_result();
}
?>



<style>
.mensaje-exito {
    background-color: #d4edda;
    color: #155724;
    padding: 12px;
    margin: 15px 0;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    font-weight: bold;
}
</style>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Check-out</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
  <link rel="stylesheet" href="../resources/sweetalert2.css" />
<script src="../resources/sweetalert2.js"></script>
</head>
<body>
    <!-- Cabecera y menú-->
    <header>
    <div class="logo-titulo">
      <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Historial de registros</span>
    </div>
    
    <nav>
        <a href="../loginconnection/dashboard.php">Inicio</a>

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
        <a href="logout.php">Logout</a>
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
                <div><strong>No. Control:</strong> <?= $datosAlumno['no_control'] ?> </div>
                <div><strong>Nombre(s):</strong> <?= $datosAlumno['nombres'] ?> </div>
                <div><strong>Apellido Paterno:</strong> <?= $datosAlumno['ap_paterno'] ?> </div>
                <div><strong>Apellido Materno:</strong> <?= $datosAlumno['ap_materno'] ?> </div>
                <div><strong>Carrera:</strong> <?= $datosAlumno['carrera'] ?> </div>
                <div><strong>Semestre:</strong> <?= $datosAlumno['semestre'] ?> </div>
                <div><strong>Grupo:</strong> <?= $datosAlumno['grupo'] ?> </div>
                <div><strong>E-mail:</strong> <?= $datosAlumno['email'] ?> </div>
                <div><strong>Género:</strong> <?= $datosAlumno['genero'] ?> </div>
            </div>
        </div>

        <!-- Historial de registros por módulo-->
        <div class="panel">
            <div class="panel-titulo">REGISTROS DE ENTRADA Y SALIDA</div>
            <div class="panel-datos">
                <div>
                    <strong>Módulo:</strong> <?= $modulo['nombre_modulo'] ?>
                </div>
                <div><strong>Horas acumuladas:</strong> <?= $modulo['horas_acumuladas'] ?> </div> <!-- Horas acumuladas por módulo-->
            </div>

            <!-- Para generar excel de registros -->
            <?php if ($id_historial && $datosAlumno): ?>
                <form action="exportarExcel.php" method="POST" target="_blank" class="formulario-checkout">
                    <input type="hidden" name="no_control" value="<?= $datosAlumno['no_control'] ?>">
                    <input type="hidden" name="id_historial" value="<?= $id_historial ?>">
                    <button type="submit" class="boton-estilizado boton-checkin">📥 Descargar Excel</button>
                </form>
            <?php endif; ?>

            <!-- Dependiendo del módulo seleccionado, actualizar la tabla -->
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Laboratorio</th>
                        <th>Actividades</th>
                        <th>Coach</th>
                        <th>Check-In</th>
                        <th>Check-Out</th>
                        <th>Horas</th>
                        <th>Acciones</th> <!-- Editar y eliminar -->
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $registros->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['fecha'] ?></td>
                            <td><?= $row['laboratorio'] ?></td>
                            <td style="width: 200px;"><?= $row['actividades'] ?></td>
                            <td><?= $row['coach'] ?></td>
                            <td><?= $row['hora_inicio'] ?></td>
                            <td><?= $row['hora_fin'] ?></td>
                            <td><?= $row['horas'] ?></td>
                            <td class="acciones">
                                <a class="editar" href="../registros/editarRegistro.php?id=<?= $row['id'] ?>&buscar=<?= $buscar ?>&id_historial=<?= $id_historial ?>">
                                    <img src="../imagenes/icon-edit.png" alt="Editar" width="20" height="20" title="Editar">
                                </a>
                                <a class="eliminar" href="../registros/eliminarRegistro.php?id=<?= $row['id'] ?>&buscar=<?= $buscar ?>&id_historial=<?= $id_historial ?>" onclick="return confirm('¿Estás seguro de eliminar este registro?')">
                                    <img src="../imagenes/icon-delete.png" alt="Eliminar" width="20" height="20" title="Eliminar">
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        Instituto Tecnológico Superior de Guanajuato </br>
        Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
    </footer>

 <?php if (isset($_GET['msg']) && $_GET['msg'] === 'editado'): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Registro editado',
        confirmButtonText: 'Aceptar'
    });
</script>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
<script>
    Swal.fire({
        icon: 'success',
        title: 'Registro eliminado',
        confirmButtonText: 'Aceptar'
    });
</script>
<?php endif; ?>


</body>
</html>
