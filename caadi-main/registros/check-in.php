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
$noEncontrado = true; 

//Inicializar las variables
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

// Ejecutar la consulta si el input Buscar tiene contenido
if (trim($buscar) !== '') 
{
    $sql = "SELECT * FROM alumno WHERE no_control = ?";
    $stmt = $conn->prepare($sql);
    $like = $buscar;
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $resultado = $stmt->get_result();


    // Obtener una sola fila
    $fila = $resultado->fetch_assoc();

    if ($fila) //si encuentra a alguien con ese número de control
    {
        $noEncontrado = false;
        //Inicializar valores con los que encontró en la consulta 
        $no_control = $fila['no_control'];
        $nombres = $fila['nombres'];
        $ap_paterno = $fila['ap_paterno'];
        $ap_materno = $fila['ap_materno'];
        $carrera = $fila['carrera'];
        $semestre = $fila['semestre'];
        $grupo = $fila['grupo'];
        $email = $fila['email'];
        $genero = $fila['genero'];
    } 
    else // si no hay nadie con ese número de control
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
      <span>Sistema CAADI - Check-In</span>
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

        <!-- Registrar check-in -->
        <div class="panel">
            <div class="panel-titulo">REALIZAR CHECK-IN</div>
            
            
            <div style="display: flex; justify-content: center;">
                <!-- Botón que se activa cuando el no. control si existe  -->
                <?php if (!$noEncontrado): ?>
                    <form action="registrarCheckIn.php" method="POST" class="formulario-checkout">
                        <div class="panel-datos">
                            <div>
                                <strong>Fecha:</strong> 
                                <input type="text" name="fechaInput" id="fechaInput"> 
                                <script>
                                    const hoy = new Date();
                                    const año = hoy.getFullYear();
                                    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
                                    const dia = String(hoy.getDate()).padStart(2, '0');
                                    const fechaLocal = `${año}-${mes}-${dia}`;
                                    document.getElementById('fechaInput').value = fechaLocal;
                                    //año-mes-día y con horario de México
                                </script>
                            </div> 
                            <div>
                                <strong>Hora inicio:</strong> 
                                <input type="text" name="horaInicioInput" id="horaInicioInput">
                                <script>
                                    const ahora = new Date();
                                    const horas = String(ahora.getHours()).padStart(2, '0');
                                    const minutos = String(ahora.getMinutes()).padStart(2, '0');
                                    const segundos = String(ahora.getSeconds()).padStart(2, '0');
                                    const horaActual = `${horas}:${minutos}:${segundos}`;
                                    document.getElementById('horaInicioInput').value = horaActual;
                                </script>
                            </div> 
                            <div>
                                <strong>Laboratorio: </strong> 
                                <select id="laboratorio" name="laboratorio" required>
                                    <option value="CAADI">CAADI</option>
                                    <option value="e-CAADI">e-CAADI</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <!-- Enviar datos ocultos  -->
                            <input type="hidden" name="no_control" value="<?php echo $no_control; ?>">

                            <div>
                                <button type="submit" value="Check-In" class="boton-estilizado boton-checkin" <?= $noEncontrado ? 'disabled' : '' ?>> Check-In </button>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
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
</body>
</html>