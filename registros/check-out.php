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
$noEncontradoCheckIn = true; 

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

        //buscar registros donde hora_fin sea null para hacer el checkout
        $sql_checkout = "SELECT * FROM registro
                        WHERE no_control_Alumno = ? AND hora_fin IS NULL
                        ORDER BY fecha DESC LIMIT 1";
        $stmt_checkout = $conn->prepare($sql_checkout);
        $stmt_checkout->bind_param("s", $no_control);
        $stmt_checkout->execute();
        $resultado_checkout = $stmt_checkout->get_result();

        if ($filas_checkout = $resultado_checkout->fetch_assoc()) // Si tiene registros para poder realizar la hora de salida
        {
            $id = $filas_checkout['id']; //id del registro para checkout
            $fecha = $filas_checkout['fecha']; 
            $hora_inicio = $filas_checkout['hora_inicio']; 
            $hora_fin = null; 
            $laboratorio = $filas_checkout['laboratorio']; 
            $no_control_Coach = $filas_checkout['no_control_Coach']; 
            $id_historial = $filas_checkout['id_historial']; 
            $noEncontradoCheckIn = false;

            //Realizar consulta para obtener nombres de los coach
            $sql_coaches = "SELECT * FROM coach";
            $stmt_coaches = $conn->prepare($sql_coaches);
            $stmt_coaches->execute();
            $resultado_coaches = $stmt_coaches->get_result();
        }
        else
        {
            $noEncontradoCheckIn = true;
            echo "
            <html>
                <head>
                    <link rel='stylesheet' href='../resources/sweetalert2.css'>
                    <script src='../resources/sweetalert2.js'></script>
                </head><body>
                <script>
                Swal.fire({
                title: 'Error',
                text: 'El estudiante no ha realizado un Check-In previo',
                icon: 'error',
                confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '../registros/check-in.php';
                });
                </script>
                </body>
            </html>";
        } 
    }
    else // si no hay nadie con ese número de control ni para check-in
    {
        $noEncontrado = true;
        $noEncontradoCheckIn = true;
    }

    $stmt_checkout->close();

}
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Check-out</title>
  <link rel="stylesheet" href="../estilos/style.css" />
  <link rel="stylesheet" href="../estilos/styletable.css" />
  <link rel="stylesheet" href="../estilos/estiloslider.css" />
</head>
<body>
    <!-- Cabecera y menú-->
    <header>
    <div class="logo-titulo">
      <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Check-Out</span>
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
                <input type="text" name="buscar" placeholder="Buscar estudiante...">
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

        <!-- Registrar check-out -->
        <div class="panel">
            <div class="panel-titulo">REALIZAR CHECK-OUT</div>
            
            <div>
                <!-- Botón que se activa cuando el no. control si existe  -->
                <?php if (!$noEncontrado && !$noEncontradoCheckIn): ?>
                    <div class="panel-datos">
                <div>
                    <strong>Fecha:</strong>
                    <!-- Obtener Fecha previamente --> 
                    <input type="text" name="fecha" id="fechaInput" value= <?= $fecha ?>> 
                </div> 
                <div>
                    <strong>Hora inicio:</strong> 
                    <!-- Obtener horas del inicio previamente -->
                    <input type="text" name="hora" id="horaInicioInput" onchange="calcularHoras()" value=<?= $hora_inicio ?>>
                </div> 
                <div>
                    <!-- Obtener laboratorio registrado previamente -->
                    <strong>Laboratorio: </strong> 
                    <select id="laboratorio" name="laboratorio" required>
                        <option value="CAADI" <?php if ($laboratorio == "CAADI") echo "selected"; ?>>CAADI</option>
                        <option value="e-CAADI" <?php if ($laboratorio == "e-CAADI") echo "selected"; ?>>e-CAADI</option>
                        <option value="Otro" <?php if ($laboratorio == "Otro") echo "selected"; ?>>Otro</option>
                    </select>
                </div>
                <div>
                    <!-- Obtener Nombres de Coaches registrados en la tabla o si les parece más fácil que obtenga el id del coach que 
                     inició sesión -->
                    <strong>Coach encargado: </strong>
                    <select id="coach" name="coach" required>
                        <option value="" disabled selected>Selecciona un Coach</option>
                        <?php
                            if ($resultado_coaches->num_rows > 0) 
                            {
                                while ($filaCoach = $resultado_coaches->fetch_assoc()) 
                                {
                                    echo '<option value="' . htmlspecialchars($filaCoach["no_control"]) . '">' . htmlspecialchars($filaCoach["nombres"]) . '</option>';
                                }
                            } 
                            else 
                            {
                                echo '<option value="">Error: Registra coaches</option>';
                            }
                        ?>
                    </select>
                </div>
                <div>
                    <strong>Hora salida:</strong> 
                    <input type="text" name="hora" id="horaSalidaInput" onchange="calcularHoras()">
                        <script>
                            //Obtener la hora de salida
                            const ahora = new Date();
                            const horas = String(ahora.getHours()).padStart(2, '0');
                            const minutos = String(ahora.getMinutes()).padStart(2, '0');
                            const segundos = String(ahora.getSeconds()).padStart(2, '0');
                            const horaActual = `${horas}:${minutos}:${segundos}`;
                            document.getElementById('horaSalidaInput').value = horaActual;
                        </script>
                </div> 
                <div>
                    <strong>Horas:</strong> 
                    <input type="text" name="horas" id="horasInput"> 
                        <script>
                            //Obtener horas totales
                            function calcularHoras() 
                            {
                                const fecha = document.getElementById("fechaInput").value;
                                const horaInicio = document.getElementById("horaInicioInput").value;
                                const horaSalida = document.getElementById("horaSalidaInput").value;
                                if (!fecha || !horaInicio || !horaSalida) 
                                {   
                                    alert("Por favor, asegúrate de que todos los campos estén llenos.");
                                    return;
                                }

                                // Combinar fecha con hora para crear objetos Date válidos
                                const inicio = new Date(`${fecha}T${horaInicio}`);
                                const fin = new Date(`${fecha}T${horaSalida}`);
                                if (isNaN(inicio) || isNaN(fin)) 
                                {   
                                    alert("Formato de fecha u hora inválido.");
                                    return;
                                }
                                const diferenciaMs = fin - inicio;
                                const horas = diferenciaMs / (1000 * 60 * 60);

                                if (horas < 0) 
                                {
                                    alert("La hora de salida debe ser posterior a la de entrada.");
                                    return;
                                }

                                // Mostrar el resultado en el input
                                document.getElementById("horasInput").value = horas.toFixed(1);
                            }    
                            window.addEventListener('DOMContentLoaded', () => 
                            {
                                calcularHoras();
                            });
                        </script>
                </div>
                
                <div class="grupo-actividades"> 
                    <strong>Actividades:</strong> 
                    
                    <table>              
                        <tr>
                            <td>
                                Asesoría Individual
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="A-IND">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Asesoría Grupal
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="A-GRU">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Resolución de Ejercicios
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="EJE">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Lectura de Comprensión
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="LEC">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                    </table>

                </div>

                <div class="grupo-actividades"> 
                    <br>
                    <table>              
                        <tr>
                            <td>
                                Club de conversación
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="CLUB">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Juegos de Mesa
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="JGO">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Reproducción de video
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="MEDIA">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Uso de Rosetta
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="ROSETTA">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="grupo-actividades"> 
                    <br>
                    <table>              
                        <tr>
                            <td>
                                Exámenes de práctica
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="TEST">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                DVD Interactivo
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="DVD">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Reproducción de música
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="MUSIC">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Reproducción de películas/videos
                            </td>
                            <td><label class="uiverse-switch">
                                <input type="checkbox" name="actividades[]" value="MOVIE">
                                <span class="slider-uiverse"></span> 
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div style="display: flex; justify-content: center;">
                <form action="registrarCheckOut.php" method="POST" class="formulario-checkout" onsubmit="return validarCheckbox()">
                    <!-- Datos ocultos para pase de formulario -->
                    <input type="hidden" name="no_control" value="<?php echo $no_control; ?>"> <!-- no_Control de Alumno -->
                    <input type="hidden" name="id" value="<?php echo $id; ?>"> <!-- id de registro -->
                    <input type="hidden" name="id_historial" value="<?php echo $id_historial; ?>"> <!-- id de historial de módulo -->
                    

                    <input type="submit" value="Check-Out" class="boton-estilizado boton-checkout">
                </form>
            </div>
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

    <!-- Script para validar que se haya seleccionado al menos una actividad -->
    <script>
        function validarCheckbox() 
        {
            // Selecciona todos los checkboxes dentro de cualquier div con clase "grupo-actividades"
            const checkboxes = document.querySelectorAll('.grupo-actividades input[type="checkbox"]');
            const algunoSeleccionado = Array.from(checkboxes).some(cb => cb.checked);

            if (!algunoSeleccionado) 
            {
                alert("Por favor, selecciona al menos una actividad.");
                return false;
            }

            // Agrega los valores seleccionados como inputs ocultos al formulario
            const form = document.querySelector('.formulario-checkout');

            // Elimina inputs ocultos anteriores (si los hay)
            form.querySelectorAll('input[name="actividades[]"]').forEach(e => e.remove());

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'actividades[]';
                    hiddenInput.value = cb.value;
                    form.appendChild(hiddenInput);
                }
            });


            
            // Obtener el valor del input de horas y agregarlo como oculto
            const horasInput = document.getElementById('horasInput');
            if (horasInput && horasInput.value.trim() !== '') 
            {
                const hiddenHoras = document.createElement('input');
                hiddenHoras.type = 'hidden';
                hiddenHoras.name = 'horas';
                hiddenHoras.value = horasInput.value;
                form.appendChild(hiddenHoras);
            }

            // Obtener el valor del input de fecha y agregarlo como oculto
            const fechaInput = document.getElementById('fechaInput');
            if (fechaInput && fechaInput.value.trim() !== '') 
            {
                const hiddenFecha = document.createElement('input');
                hiddenFecha.type = 'hidden';
                hiddenFecha.name = 'fecha';
                hiddenFecha.value = fechaInput.value;
                form.appendChild(hiddenFecha);
            }

            // Obtener el valor del input de hora de inicio y agregarlo como oculto
            const horaInicioInput = document.getElementById('horaInicioInput');
            if (horaInicioInput && horaInicioInput.value.trim() !== '') {
                const hiddenHora = document.createElement('input');
                hiddenHora.type = 'hidden';
                hiddenHora.name = 'hora';
                hiddenHora.value = horaInicioInput.value;
                form.appendChild(hiddenHora);
            }

            // Obtener el valor del input de hora de salida y agregarlo como oculto
            const horaSalidaInput = document.getElementById('horaSalidaInput');
            if (horaSalidaInput && horaSalidaInput.value.trim() !== '') 
            {
                const hiddenHoraSalida = document.createElement('input');
                hiddenHoraSalida.type = 'hidden';
                hiddenHoraSalida.name = 'hora_salida';
                hiddenHoraSalida.value = horaSalidaInput.value;
                form.appendChild(hiddenHoraSalida);
            }

            // Obtener el valor del select de coach y agregarlo como oculto
            const coachSelect = document.getElementById('coach');

            if (!coachSelect || coachSelect.value === "") 
            {
                alert("Por favor, selecciona un Coach encargado.");
                return false; // Detener envío del formulario
            }
            const hiddenCoach = document.createElement('input');
            hiddenCoach.type = 'hidden';
            hiddenCoach.name = 'coach';
            hiddenCoach.value = coachSelect.value;
            form.appendChild(hiddenCoach);
            

            // Obtener el valor del select de laboratorio y agregarlo como oculto
            const laboratorioSelect = document.getElementById('laboratorio');
            if (laboratorioSelect && laboratorioSelect.value) {
                const hiddenLab = document.createElement('input');
                hiddenLab.type = 'hidden';
                hiddenLab.name = 'laboratorio';
                hiddenLab.value = laboratorioSelect.value;
                form.appendChild(hiddenLab);
            }
            return true;
        }
    </script>
</body>
</html>