<?php
  session_start();
  //Checar si hay sesión ya iniciada
  if (isset($_SESSION['user_id'])) {
    header("Location: loginconnection/dashboard.php");
    exit();
  }
?>
<!DOCTYPE html> 
<html> 
    <html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CAADI: Inicio de sesión</title>
  <link rel="stylesheet" href="estilos/style.css" />
</head>
    <body> 
        <header>
    <div class="logo-titulo">
      <img src="imagenes/itesg_vert.png" alt="Logo ITESG">
      <span>Sistema CAADI - Consulta de estudiantes</span>
    </div>
    
  </header>
        <main>
          <form action="loginconnection/login.php" method="POST"> 
              <h2>Iniciar sesión</h2> 
              <label>No. Control:</label>
              <input type="text" name="username" required><br> 

              <label>Contraseña:</label>
              <input type="password" name="password" required><br> 
              <input type="submit" value="Ingresar"> 
          </form> 
        </main>

        <footer>
          Instituto Tecnológico Superior de Guanajuato </br>
          Desarrollado por Academia y estudiantes de Ingeniería en Sistemas Computacionales 2025.
        </footer>
    </body> 
</html>