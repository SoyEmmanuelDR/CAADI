<?php
function insertarCoach($conn, $no_control, $nombres, $ap_paterno, $ap_materno, $password) {
    $stmt = $conn->prepare("INSERT INTO coach (no_control, nombres, ap_paterno, ap_materno, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $no_control, $nombres, $ap_paterno, $ap_materno, $password);
    return $stmt->execute();
}

function eliminarCoach($conn, $no_control) {
    $stmt = $conn->prepare("DELETE FROM coach WHERE no_control = ?");
    $stmt->bind_param("i", $no_control);
    return $stmt->execute();
}

function editarCoach($conn, $no_control, $nombres, $ap_paterno, $ap_materno, $password) {
    $stmt = $conn->prepare("UPDATE coach SET nombres=?, ap_paterno=?, ap_materno=?, password=? WHERE no_control=?");
    $stmt->bind_param("ssssi", $nombres, $ap_paterno, $ap_materno, $password, $no_control);
    return $stmt->execute();
}
?>
