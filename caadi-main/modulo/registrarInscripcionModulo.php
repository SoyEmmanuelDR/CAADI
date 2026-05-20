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

// Validar que el no_control se haya encontrado
// si no, entonces regresa
if(!isset($_POST['no_control']) )
{
    header("Location: inscribirAModulo.php"); 
    exit; 
}

// Asegurar el no_control desde el formulario o desde una sesión
if (isset($_POST['no_control']) && isset($_POST['modulo']) && isset($_POST['fecha_inicio']) && isset($_POST['status'])) 
{
    $no_control = $_POST['no_control'];
    $id_Modulo = $_POST['modulo'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $status = $_POST['status'];

    // Preparar la consulta
    $sql = "INSERT INTO historial_modulos (no_control_Alumno, id_modulo, fecha_inicio, fecha_fin, status) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $no_control, $id_Modulo, $fecha_inicio, $fecha_fin, $status);

    if ($stmt->execute()) 
    {
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
        window.location.href = 'inscribirAModulo.php';
    });
    </script>
    </body></html>";
    } 
    else 
    {
        echo "Error al registrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} 
else 
{
    echo "Faltan datos para registrar la inscripción. Ingresa No. Control y Fecha de inicio";
}
?>