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

    $id = $_POST['id']; // id de registro checkin
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora'];
    $hora_fin = $_POST['hora_salida'];
    $horas = $_POST['horas'];
    $laboratorio = $_POST['laboratorio'];
    $no_control_Coach = $_POST['coach'];
    $no_control_Alumno = $_POST['no_control'];
    $id_historial = $_POST['id_historial'];
    $actividades = $_POST['actividades'] ?? [];

    // Actualizar el registro de checkin y checkout
    $sql_checkout = "UPDATE registro 
                    SET fecha = ?, hora_inicio = ?, hora_fin = ?, horas = ?, laboratorio = ?, no_control_Coach = ?
                    WHERE id = ? AND no_control_Alumno = ? AND id_historial = ?";
    $stmt_checkout = $conn->prepare($sql_checkout);
    $stmt_checkout->bind_param("sssdsiiii", $fecha, $hora_inicio, $hora_fin, $horas, $laboratorio, $no_control_Coach, $id, $no_control_Alumno, $id_historial);
    $exitoCheckout = $stmt_checkout->execute();

    // Actividades realizadas en tabla registro_actividades
    foreach ($actividades as $actividad) 
    {
        $sql_act = "INSERT INTO registro_actividades (id_registro, actividad) VALUES (?, ?)";
        $stmt_act = $conn->prepare($sql_act);
        $stmt_act->bind_param("is", $id, $actividad);
        $stmt_act->execute();
    }
    
    // Actualizar horas acumuladas en registro de módulo
    $sql_horas =   "UPDATE historial_modulos 
                    SET horas_acumuladas = IFNULL(horas_acumuladas, 0) + ?
                    WHERE id_historial = ?";
    $stmt_horas = $conn->prepare($sql_horas);
    $stmt_horas->bind_param("di", $horas, $id_historial);
    $exitoHoras = $stmt_horas->execute();
    
    if($exitoCheckout && $exitoHoras)
    {
        echo "
            <html><head>
            <link rel='stylesheet' href='../resources/sweetalert2.css'>
            <script src='../resources/sweetalert2.js'></script>
            </head><body>
            <script>
            Swal.fire({
            title: 'Check-Out registrado',
            text: 'El Check-Out fue exitoso.',
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
        echo "
            <html>
                <head>
                    <link rel='stylesheet' href='../resources/sweetalert2.css'>
                    <script src='../resources/sweetalert2.js'></script>
                </head><body>
                <script>
                Swal.fire({
                title: 'Error',
                text: 'El check-out no se pudo registrar',
                icon: 'error',
                confirmButtonText: 'Aceptar'
                }).then(() => {
                    window.location.href = '../loginconnection/dashboard.php';
                });
                </script>
                </body>
            </html>";    
    }
    $conn->close();
?>