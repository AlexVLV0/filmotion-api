<?php
include_once '../configs/db_config.php';

// Función para buscar un usuario por su email
function obtenerUsuarioPorEmail($email) {
    $mysqli = getDbConnection(); // Conexión a la base de datos

    $stmt = $mysqli->prepare("SELECT id, email, password_hash FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $email, $password);
        $stmt->fetch();
        return ['id' => $id, 'email' => $email, 'password' => $password];
    }

    return null;
}

function obtenerPerfilPorEmail($email) {
    $mysqli = getDbConnection();

    $stmt = $mysqli->prepare("
        SELECT id, email, nombre_usuario, Animo, Preferencia_Alegre
        FROM usuarios
        WHERE email = ?
    ");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc() ?: null;
}



?>
