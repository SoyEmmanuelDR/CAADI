<?php
require 'conexiondb.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username         = trim($_POST['username']);
    $nombre           = trim($_POST['nombre']);
    $apellidos        = trim($_POST['apellidos']);
    $correo           = trim($_POST['correo']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validación básica
    if (empty($username) || empty($nombre) || empty($correo) || empty($password) || empty($confirm_password)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

    if ($password !== $confirm_password) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    // Validar si el usuario ya existe
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? OR correo = ?");
    $stmt->bind_param("ss", $username, $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "El nombre de usuario o correo ya está registrado.";
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Hashear contraseña y guardar usuario
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Declaración preparada
    $stmt = $conn->prepare("INSERT INTO usuarios (username, nombre, apellidos, correo, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $nombre, $apellidos, $correo, $hashed_password);

    if ($stmt->execute()) 
    {
        echo "Usuario registrado correctamente. <a href='index.html'>Iniciar sesión</a>";
    } 
    else {
        echo "Error al registrar usuario: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>