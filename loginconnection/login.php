<?php
session_start();
require 'conexiondb.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password_input = $_POST['password'];

    $stmt = $conn->prepare("SELECT no_control, password FROM coach WHERE no_control = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($user_id, $hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!empty($hashed_password) && password_verify($password_input, $hashed_password)) 
    {
        $_SESSION['user_id'] = $user_id;
        header("Location: dashboard.php");
        exit;
    } 
    else 
    {
        // Mostrar alerta SweetAlert2 y regresar al login
        echo "
        <html>
        <head>
          <link rel='stylesheet' href='../resources/sweetalert2.css'>
          <script src='../resources/sweetalert2.js'></script>
        </head>
        <body>
        <script>
          Swal.fire({
            title: 'Error de acceso',
            text: 'Usuario o contraseña incorrectos.',
            icon: 'error',
            confirmButtonText: 'Aceptar'
          }).then(() => {
            window.location.href = '../index.php'; // Ruta a tu formulario login
          });
        </script>
        </body>
        </html>";
        exit;
    }
}
?>
