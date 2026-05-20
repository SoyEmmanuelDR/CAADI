<?php
require '../loginconnection/conexiondb.php';

$no_control = $_POST['no_control'];
$id_historial = $_POST['id_historial'];

// Configurar encabezados para Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Registros_CAADI_$no_control.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Obtener datos del estudiante
$sql_alu = "SELECT * FROM alumno WHERE no_control = ?";
$stmt = $conn->prepare($sql_alu);
$stmt->bind_param("s", $no_control);
$stmt->execute();
$alumno = $stmt->get_result()->fetch_assoc();

// Obtener datos del módulo
$sql_mod = "SELECT m.nombre, hm.horas_acumuladas
            FROM historial_modulos hm
            JOIN modulo m ON hm.id_modulo = m.id
            WHERE hm.id_historial = ?";
$stmt2 = $conn->prepare($sql_mod);
$stmt2->bind_param("i", $id_historial);
$stmt2->execute();
$modulo = $stmt2->get_result()->fetch_assoc();

// Obtener registros del historial
$sql = "SELECT r.fecha, r.hora_inicio, r.hora_fin, r.horas, r.laboratorio, 
               c.nombres AS coach, 
               GROUP_CONCAT(ra.actividad SEPARATOR ', ') AS actividades
        FROM registro r
        LEFT JOIN coach c ON r.no_control_Coach = c.no_control
        LEFT JOIN registro_actividades ra ON r.id = ra.id_registro
        WHERE r.id_historial = ?
        GROUP BY r.id
        ORDER BY r.fecha DESC";
$stmt3 = $conn->prepare($sql);
$stmt3->bind_param("i", $id_historial);
$stmt3->execute();
$registros = $stmt3->get_result();

// Imprimir contenido como tabla HTML que Excel puede leer
?>

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <tr>
        <td colspan="7" align="center">
            <img src="https://www.itesg.edu.mx/images/Imagen/itesg_vert.png" alt="ITESG" height="80">
        </td>
    </tr>
    <tr>
        <td colspan="7" align="center" style="font-size: 16pt; font-weight: bold;">
            Sistema CAADI - Historial de Registros
        </td>
    </tr>
</table>
<tr><td colspan="7">&nbsp;</td></tr>
<br>
<br>
<br>

<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <tr><th colspan="7">Datos del Estudiante</th></tr>
    <tr><td><strong>No. Control</strong></td><td colspan="6"><?= $alumno['no_control'] ?></td></tr>
    <tr><td><strong>Nombre</strong></td><td colspan="6"><?= $alumno['nombres'] . " " . $alumno['ap_paterno'] . " " . $alumno['ap_materno'] ?></td></tr>
    <tr><td><strong>Carrera</strong></td><td colspan="6"><?= $alumno['carrera'] ?></td></tr>
    <tr><td><strong>Semestre</strong></td><td colspan="6"><?= $alumno['semestre'] ?></td></tr>
    <tr><td><strong>Grupo</strong></td><td colspan="6"><?= $alumno['grupo'] ?></td></tr>
    <tr><td><strong>Email</strong></td><td colspan="6"><?= $alumno['email'] ?></td></tr>
    <tr><td><strong>Género</strong></td><td colspan="6"><?= $alumno['genero'] ?></td></tr>
    <tr><td><strong>Módulo</strong></td><td colspan="6"><?= $modulo['nombre'] ?></td></tr>
    <tr><td><strong>Horas acumuladas</strong></td><td colspan="6"><?= $modulo['horas_acumuladas'] ?></td></tr>
</table>
<tr><td colspan="7">&nbsp;</td></tr>
<br>

<table border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; width: 100%;">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th>Fecha</th>
            <th>Laboratorio</th>
            <th>Actividades</th>
            <th>Coach</th>
            <th>Hora entrada</th>
            <th>Hora salida</th>
            <th>Horas</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $registros->fetch_assoc()): ?>
            <tr>
                <td><?= $row['fecha'] ?></td>
                <td><?= $row['laboratorio'] ?></td>
                <td><?= $row['actividades'] ?></td>
                <td><?= $row['coach'] ?></td>
                <td><?= $row['hora_inicio'] ?></td>
                <td><?= $row['hora_fin'] ?></td>
                <td><?= $row['horas'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>
