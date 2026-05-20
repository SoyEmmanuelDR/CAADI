<?php 
    session_start();
    //Si la sesión no ha sido iniciada, redirige a index.php 
    if (!isset($_SESSION['user_id'])) 
    { 
        header("Location: ../index.php"); 
        exit; 
    } 
?> 
<!DOCTYPE html> 
<html> 
    <head> 
        <meta charset="UTF-8"> 
        <title>CAADI: Dashboard</title>
        <link rel="stylesheet" href="../estilos/style.css" /> 
    </head> 
    <body> 

      <header>
        <div class="logo-titulo">
          <img src="../imagenes/itesg_vert.png" alt="Logo ITESG">
          <span>Sistema CAADI</span>
        </div>
  
        <nav>
          <a href="dashboard.php">Inicio</a>

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

      <main>
        <h1>Bienvenido (a) al panel</h1> 
        <p>Sesión iniciada correctamente.</p>
      </main> 
    </body> 
</html>