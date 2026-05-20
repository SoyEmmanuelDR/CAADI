<?php 
session_start();
//Si la sesión no ha sido iniciada, redirige a index.php 
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../index.php"); 
    exit; 
}
?> 

<?php
require '../loginconnection/conexiondb.php'; 

$buscar = $_GET['buscar'] ?? '';

// --- Paginación ---
$registros_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $registros_por_pagina;

$sql = "SELECT * FROM alumno WHERE no_control LIKE ? OR nombres LIKE ? OR ap_paterno LIKE ? OR ap_materno LIKE ? OR email LIKE ?";
$like = "%" . $buscar . "%";

// --- Total de registros ---
$stmt_total = $conn->prepare($sql);
$stmt_total->bind_param("sssss", $like, $like, $like, $like, $like);
$stmt_total->execute();
$resultado_total = $stmt_total->get_result();
$total_registros = $resultado_total->num_rows;

// --- Consulta con paginación ---
$sql .= " LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssii", $like, $like, $like, $like, $like, $offset, $registros_por_pagina);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultar Estudiantes</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
</head>
<body>

  <header>
    <div class="logo-titulo">
      <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Consulta de estudiantes</span>
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
        <a href="#">Consultar</a>
      </div>
    </div>

    <a href="../registros/exportarExcel.php">Reportes</a>
    <a href="../loginconnection/logout.php">Logout</a>
  </nav>

  </header>

  <main style="padding: 30px;">
    <div class="contenedor">
    <form class = "buscar" method="GET" action="">
      <input type="text" name="buscar" placeholder="Buscar estudiante..." value="<?= htmlspecialchars($buscar) ?>">
      <button type="submit">Buscar</button>
    </form>

    <table>
      <thead>
        <tr>
          <th>No. Control</th>
          <th>Nombre (s)</th>
          <th>Apellido Paterno</th>
          <th>Apellido Materno</th>
          <th>Carrera</th>
          <th>Semestre</th>
          <th>Grupo</th>
          <th>e-mail</th>
          <th>Género</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        
     <?php while ($row = $resultado->fetch_assoc()): ?>
  <tr>
    <td><?= $row['no_control'] ?></td>
    <td><?= $row['nombres'] ?></td>
    <td><?= $row['ap_paterno'] ?></td>
    <td><?= $row['ap_materno'] ?></td>
    <td><?= $row['carrera'] ?></td>
    <td><?= $row['semestre'] ?></td>
    <td><?= $row['grupo'] ?></td>
    <td><?= $row['email'] ?></td>
    <td><?= $row['genero'] ?></td>
    <td class="acciones">
      <a class="historial" href="../modulo/inscribirAModulo.php?buscar=<?= $row['no_control'] ?>">
        <img src="../imagenes/icon-historial.png" alt="Historial" width="20" height="20" title="Historial">
      </a>
      <a class="editar" href="editarEstudiante.php?no_control=<?= $row['no_control'] ?>">
        <img src="../imagenes/icon-edit.png" alt="Editar" width="20" height="20" title="Editar">
      </a>
      <a class="eliminar" href="eliminarEstudiante.php?no_control=<?= $row['no_control'] ?>" onclick="return confirm('¿Estás seguro de eliminar este estudiante?')">
        <img src="../imagenes/icon-delete.png" alt="Eliminar" width="20" height="20" title="Eliminar">
      </a>
    </td>
  </tr>
<?php endwhile; ?>
      </tbody>
    </table>

    <div class="info-paginacion">
      <?php
      $inicio = $offset + 1;
      $fin = min($offset + $registros_por_pagina, $total_registros);
      $mostrados = $fin - $inicio + 1;
      if ($total_registros > 0) {
          echo "<p style='margin-top:10px;'>Mostrando $inicio a $fin de $total_registros registros (actualmente visualizando $mostrados)</p>";
      } else {
          echo "<p style='margin-top:10px;'>No se encontraron registros</p>";
      }
      ?>
    </div>

    <?php
// --- Paginación visual ---
$total_paginas = ceil($total_registros / $registros_por_pagina);

if ($total_paginas > 1) {
    echo '<div class="paginacion">';
    
    // Botón anterior
    if ($pagina_actual > 1) {
        echo '<a href="?buscar=' . urlencode($buscar) . '&pagina=' . ($pagina_actual - 1) . '" class="flecha" title="Página anterior">&laquo; Anterior</a>';
    } else {
        echo '<span class="flecha disabled" title="No hay página anterior">&laquo; Anterior</span>';
    }

    // Mostrar solo algunas páginas alrededor de la actual
    $inicio_pagina = max(1, $pagina_actual - 2);
    $fin_pagina = min($total_paginas, $pagina_actual + 2);
    
    // Primera página con elipsis si es necesario
    if ($inicio_pagina > 1) {
        echo '<a href="?buscar=' . urlencode($buscar) . '&pagina=1">1</a>';
        if ($inicio_pagina > 2) {
            echo '<span class="puntos">...</span>';
        }
    }
    
    // Páginas alrededor de la actual
    for ($i = $inicio_pagina; $i <= $fin_pagina; $i++) {
        if ($i == $pagina_actual) {
            echo '<span class="actual">' . $i . '</span>';
        } else {
            echo '<a href="?buscar=' . urlencode($buscar) . '&pagina=' . $i . '">' . $i . '</a>';
        }
    }
    
    // Última página con elipsis si es necesario
    if ($fin_pagina < $total_paginas) {
        if ($fin_pagina < $total_paginas - 1) {
            echo '<span class="puntos">...</span>';
        }
        echo '<a href="?buscar=' . urlencode($buscar) . '&pagina=' . $total_paginas . '">' . $total_paginas . '</a>';
    }

    // Botón siguiente
    if ($pagina_actual < $total_paginas) {
        echo '<a href="?buscar=' . urlencode($buscar) . '&pagina=' . ($pagina_actual + 1) . '" class="flecha" title="Página siguiente">Siguiente &raquo;</a>';
    } else {
        echo '<span class="flecha disabled" title="No hay página siguiente">Siguiente &raquo;</span>';
    }

    echo '</div>';
}
?>
    </div>
  </main>

  <footer>
    Instituto Tecnológico Superior de Guanajuato </br>
    Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
  </footer>

<script>
  const inputBuscar = document.querySelector('input[name="buscar"]');
  inputBuscar.addEventListener('input', function () {
    if (this.value.trim() === '') {
      window.location.href = window.location.pathname; // recarga sin parámetros, vuelve a refrescar la tabla cuando se borra del "Buscar"
    }
  });
</script>

</body>
</html>
