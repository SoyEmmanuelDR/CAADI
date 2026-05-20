<?php
require '../loginconnection/conexiondb.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = $_POST['id'];

    // Obtener horas antiguas antes de actualizar
    $sqlHorasOld = "SELECT horas, no_control_Alumno, id_historial FROM registro WHERE id = ?";
    $stmtHorasOld = $conn->prepare($sqlHorasOld);
    $stmtHorasOld->bind_param("i", $id);
    $stmtHorasOld->execute();
    $resultHorasOld = $stmtHorasOld->get_result();
    $rowHorasOld = $resultHorasOld->fetch_assoc();
    $horasAntiguas = $rowHorasOld['horas'];
    $no_control_Alumno_old = $rowHorasOld['no_control_Alumno'];
    $id_historial_old = $rowHorasOld['id_historial'];

    // Valores nuevos del formulario
    $fecha = $_POST['fecha'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $horas = $_POST['horas'];
    $descripcion = $_POST['descripcion'];
    $laboratorio = $_POST['laboratorio'];
    $no_control_Coach = $_POST['no_control_Coach'];
    $no_control_Alumno = $_POST['no_control_Alumno']; // por si cambia
    $id_historial = $_POST['id_historial'];           // por si cambia
    $actividades = $_POST['actividades'] ?? [];

    // Actualizar registro
    $sql = "UPDATE registro SET 
        fecha = ?, hora_inicio = ?, hora_fin = ?, horas = ?, descripcion = ?, 
        laboratorio = ?, no_control_Coach = ?, no_control_Alumno = ?, id_historial = ?
        WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdsssiii", $fecha, $hora_inicio, $hora_fin, $horas, $descripcion,
        $laboratorio, $no_control_Coach, $no_control_Alumno, $id_historial, $id);
    $stmt->execute();

    // Actualizar actividades: borrar y agregar nuevas
    $stmt = $conn->prepare("DELETE FROM registro_actividades WHERE id_registro = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    if (!empty($actividades)) {
        $stmt = $conn->prepare("INSERT INTO registro_actividades (id_registro, actividad) VALUES (?, ?)");
        foreach ($actividades as $act) {
            $stmt->bind_param("is", $id, $act);
            $stmt->execute();
        }
    }

    // Actualizar horas acumuladas en historial_modulos
    // 1) Restar horas antiguas del historial/modulo antiguo
    $sqlRestar = "UPDATE historial_modulos SET horas_acumuladas = horas_acumuladas - ? WHERE id_historial = ? AND no_control_Alumno = ?";
    $stmtRestar = $conn->prepare($sqlRestar);
    $stmtRestar->bind_param("dis", $horasAntiguas, $id_historial_old, $no_control_Alumno_old);
    $stmtRestar->execute();

    // 2) Sumar horas nuevas al historial/modulo nuevo
    $sqlSumar = "UPDATE historial_modulos SET horas_acumuladas = horas_acumuladas + ? WHERE id_historial = ? AND no_control_Alumno = ?";
    $stmtSumar = $conn->prepare($sqlSumar);
    $stmtSumar->bind_param("dis", $horas, $id_historial, $no_control_Alumno);
    $stmtSumar->execute();

    // Redirigir con mensaje de éxito
    header("Location: registrosByModulo.php?buscar=$no_control_Alumno&id_historial=$id_historial&msg=editado");
    exit;

}

// Si no es POST, mostrar el formulario
$id = $_GET['id'] ?? null;
if (!$id) {
    die("ID de registro no proporcionado.");
}

// Obtener datos del registro
$sql = "SELECT * FROM registro WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$registro = $stmt->get_result()->fetch_assoc();
if (!$registro) {
    die("Registro no encontrado.");
}

// Obtener actividades actuales
$sql_acts = "SELECT actividad FROM registro_actividades WHERE id_registro = ?";
$stmt = $conn->prepare($sql_acts);
$stmt->bind_param("i", $id);
$stmt->execute();
$result_acts = $stmt->get_result();
$actividades_actuales = [];
while ($row = $result_acts->fetch_assoc()) {
    $actividades_actuales[] = $row['actividad'];
}

// Actividades predefinidas
$actividades_disponibles = [
    "Asesoría Individual", "Asesoría Grupal", "Resolución de Ejercicios", "Lectura de Comprensión",
    "Club de conversación", "Juegos de Mesa", "Reproducción de video", "Uso de Rosetta",
    "Exámenes de práctica", "DVD Interactivo", "Reproducción de música", "Reproducción de películas/videos"
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro</title>
    <link rel="stylesheet" href="../estilos/style.css">
</head>
<body>
    <h2>Editar Registro</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($registro['id']) ?>">

        <label>Fecha:</label>
        <input type="date" name="fecha" value="<?= htmlspecialchars($registro['fecha']) ?>" required><br>

        <label>Hora inicio:</label>
        <input type="time" name="hora_inicio" value="<?= htmlspecialchars($registro['hora_inicio']) ?>" required><br>

        <label>Hora fin:</label>
        <input type="time" name="hora_fin" value="<?= htmlspecialchars($registro['hora_fin']) ?>" required><br>

        <label>Horas:</label>
        <input type="number" step="0.01" name="horas" value="<?= htmlspecialchars($registro['horas']) ?>" required><br>

        <label>Descripción:</label>
        <input type="text" name="descripcion" value="<?= htmlspecialchars($registro['descripcion']) ?>"><br>

        <label>Laboratorio:</label>
        <input type="text" name="laboratorio" value="<?= htmlspecialchars($registro['laboratorio']) ?>" required><br>

        <label>No. Control Coach:</label>
        <input type="text" name="no_control_Coach" value="<?= htmlspecialchars($registro['no_control_Coach']) ?>" required><br>

        <label>No. Control Alumno:</label>
        <input type="text" name="no_control_Alumno" value="<?= htmlspecialchars($registro['no_control_Alumno']) ?>" required><br>

        <label>Módulo (id_historial):</label>
        <input type="number" name="id_historial" value="<?= htmlspecialchars($registro['id_historial']) ?>" required><br>

        <label>Actividades:</label><br>
        <?php foreach ($actividades_disponibles as $actividad): ?>
            <label>
                <input type="checkbox" name="actividades[]" value="<?= htmlspecialchars($actividad) ?>"
                    <?= in_array($actividad, $actividades_actuales) ? 'checked' : '' ?>>
                <?= htmlspecialchars($actividad) ?>
            </label><br>
        <?php endforeach; ?>

        <button type="submit">Guardar Cambios</button>
    </form>
</body>
</html>
