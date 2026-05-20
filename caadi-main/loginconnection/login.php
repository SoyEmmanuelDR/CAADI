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
        echo "Usuario o contraseña incorrectos.";
    }
}
?>