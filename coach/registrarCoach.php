<?php
require '../loginconnection/conexiondb.php'; 
include 'funcionesCoach.php';

$no_control = $_POST['no_control'];
$nombres = $_POST['nombres'];
$ap_paterno = $_POST['apellido_paterno'];
$ap_materno = $_POST['apellido_materno'];
$password = $_POST['password'];

// Verificar si ya existe ese no_control
$verificar = $conn->prepare("SELECT * FROM coach WHERE no_control = ?");
$verificar->bind_param("s", $no_control);
$verificar->execute();
$resultado = $verificar->get_result();

if ($resultado->num_rows > 0) {
    // ❌ Ya existe ese no_control
    echo "
    <html><head>
    <link rel='stylesheet' href='../resources/sweetalert2.css'>
    <script src='../resources/sweetalert2.js'></script>
    </head><body>
    <script>
    Swal.fire({
        title: 'Número de control duplicado',
        text: 'Ya existe un coach con ese número de control.',
        icon: 'warning',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        window.history.back();
    });
    </script>
    </body></html>";
    exit;
}

// Insertar nuevo coach
if (insertarCoach($conn, $no_control, $nombres, $ap_paterno, $ap_materno, $password)) {
    // ✅ Coach registrado correctamente
    echo "
    <html><head>
    <link rel='stylesheet' href='../resources/sweetalert2.css'>
    <script src='../resources/sweetalert2.js'></script>
    </head><body>
    <script>
    Swal.fire({
        title: 'Coach registrado',
        text: 'El coach ha sido registrado correctamente.',
        icon: 'success',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        window.location.href = 'formConsultarCoach.php';
    });
    </script>
    </body></html>";
    exit;
} else {
    // ❌ Error al registrar
    echo "
    <html><head>
    <link rel='stylesheet' href='../resources/sweetalert2.css'>
    <script src='../resources/sweetalert2.js'></script>
    </head><body>
    <script>
    Swal.fire({
        title: 'Error',
        text: 'Hubo un problema al registrar el coach.',
        icon: 'error',
        confirmButtonText: 'Aceptar'
    }).then(() => {
        window.history.back();
    });
    </script>
    </body></html>";
    exit;
}
?>
