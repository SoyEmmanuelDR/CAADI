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
    // Conexión a la base de datos
    require '../loginconnection/conexiondb.php';

    $no_control = $_POST['no_control'];
    $fecha = $_POST['fechaInput'];
    $hora_inicio = $_POST['horaInicioInput'];
    $laboratorio = $_POST['laboratorio'];

    // Consulta, buscar id_historial con status "En Curso"
    $sql_historial = "SELECT id_historial FROM historial_modulos
                            WHERE no_control_Alumno = ? AND status = 'En Curso'
                            ORDER BY fecha_inicio DESC LIMIT 1";
    $stmt_historial = $conn->prepare($sql_historial);
    $stmt_historial->bind_param("s", $no_control);
    $stmt_historial->execute();
    $resultado_historial = $stmt_historial->get_result();

    if ($filas_historial = $resultado_historial->fetch_assoc()) // Si tiene módulos inscritos En Curso
    {
        $id_historial = $filas_historial['id_historial'];
        
        // Insertar en tabla registro con id_historial
        $sql_checkin = "INSERT INTO registro (fecha, hora_inicio, laboratorio, no_control_Alumno, id_historial)
                        VALUES (?, ?, ?, ?, ?)";
        $stmt_checkin = $conn->prepare($sql_checkin);
        $stmt_checkin->bind_param("sssss", $fecha, $hora_inicio, $laboratorio, $no_control, $id_historial);

        if ($stmt_checkin->execute()) 
        {
            // Mensaje de confirmación
            echo "
                <html><head>
                <link rel='stylesheet' href='../resources/sweetalert2.css'>
                <script src='../resources/sweetalert2.js'></script>
                </head><body>
                <script>
                Swal.fire({
                    title: 'CheckIn registrado',
                    text: 'El CheckIn fue exitoso.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '../loginconnection/dashboard.php';
                });
                </script>
                </body></html>";
        } 
        else 
        {
            echo "Error al registrar: " . $stmt_checkin->error;
        }
        $stmt_checkin->close();
    } 
    else //No tiene módulos inscritos
    {
        echo "
            <html>
                <head>
                    <link rel='stylesheet' href='../resources/sweetalert2.css'>
                    <script src='../resources/sweetalert2.js'></script>
                </head><body>
                <script>
                Swal.fire({
                title: 'Error',
                text: 'El estudiante no se ha inscrito a un módulo.',
                icon: 'error',
                confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '../modulo/inscribirAModulo.php';
                });
                </script>
                </body>
            </html>";
    }
    $stmt_historial->close();
    $conn->close();
?>