<?php
require '../loginconnection/conexiondb.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID no válido.");
}

// Obtener datos para restar horas y redirigir después
$stmt = $conn->prepare("SELECT horas, no_control_Alumno, id_historial FROM registro WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$datos = $stmt->get_result()->fetch_assoc();

if (!$datos) {
    die("Registro no encontrado.");
}

$horas = $datos['horas'];
$no_control = $datos['no_control_Alumno'];
$id_historial = $datos['id_historial'];

// Restar horas del historial_modulos
$stmtUpdate = $conn->prepare("UPDATE historial_modulos SET horas_acumuladas = horas_acumuladas - ? WHERE id_historial = ? AND no_control_Alumno = ?");
$stmtUpdate->bind_param("dis", $horas, $id_historial, $no_control);
$stmtUpdate->execute();

// Eliminar actividades asociadas
$stmt = $conn->prepare("DELETE FROM registro_actividades WHERE id_registro = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Eliminar registro principal
$stmt = $conn->prepare("DELETE FROM registro WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: registrosByModulo.php?buscar=$no_control&id_historial=$id_historial&msg=eliminado");
exit;
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
    <div class="mensaje-exito">
        ✅ Registro eliminado correctamente.
    </div>
<?php endif; ?>

<style>
.mensaje-exito {
    background-color: #d4edda;
    color: #155724;
    padding: 12px;
    margin: 15px 0;
    border: 1px solid #c3e6cb;
    border-radius: 6px;
    font-weight: bold;
}
</style>
