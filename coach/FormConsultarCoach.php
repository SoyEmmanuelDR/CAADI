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
$buscar = $_GET['buscar'] ?? '';
$sql = "SELECT * FROM coach WHERE no_control LIKE ? OR nombres LIKE ? OR ap_paterno LIKE ? OR ap_materno LIKE ?";
$stmt = $conn->prepare($sql);
$like = "%" . $buscar . "%";
$stmt->bind_param("ssss", $like, $like, $like, $like);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Consultar Coaches</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
</head>
<body>

  <header>
    <div class="logo-titulo">
      <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Consulta de Coaches</span>
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
    <div class="contenedor">
    <form class = "buscar" method="GET" action="">
      <input type="text" name="buscar" placeholder="Buscar coach..." value="<?= htmlspecialchars($buscar) ?>">
      <button type="submit">Buscar</button>
    </form>
    

    <table>
      <thead>
        <tr>
          <th>No. Control</th>
          <th>Nombre (s)</th>
          <th>Apellido Paterno</th>
          <th>Apellido Materno</th>
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
            <td class="acciones">
              <a class="editar" href="editarCoach.php?no_control=<?= $row['no_control'] ?>">
                <img src="../imagenes/icon-edit.png" alt="Editar" width="20" height="20" title="Editar">
              </a>
              <a class="eliminar" href="eliminarCoach.php?no_control=<?= $row['no_control'] ?>" onclick="return confirm('¿Estás seguro de eliminar este coach?')">
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