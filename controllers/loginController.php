<?php
include_once '../models/usuarioModel.php';
require_once '../vendor/autoload.php';  // Asegúrate de que la librería esté cargada

use \Firebase\JWT\JWT;

// Función para manejar el login
function loginUsuario($email, $password) {
    $usuario = obtenerUsuarioPorEmail($email);  // Buscar al usuario en la base de datos

    if ($usuario) {
        // Verificar la contraseña utilizando password_verify
        if (password_verify($password, $usuario['password'])) {
            return [
                'status' => 'success',
                'message' => 'Login exitoso',
                'user_id' => $usuario['id'],
                'email' => $usuario['email']
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Contraseña incorrecta'
            ];
        }
    } else {
        return [
            'status' => 'error',
            'message' => 'Usuario no encontrado'
        ];
    }
}

function validarEmail($email) {
    // Verificar si el formato del correo es válido
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;  // El formato del correo es válido
    } else {
        return false; // El formato del correo es inválido
    }
}

function registerUsuario($email, $password) {
    $mysqli = getDbConnection();
    
    // Validar el formato del correo electrónico
    if (!validarEmail($email)) {
        return [
            'status' => 'error',
            'message' => 'El correo electrónico no tiene un formato válido'
        ];
    }

    // Verificar si el usuario ya existe
    $stmt = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        return [
            'status' => 'error',
            'message' => 'El correo electrónico ya está registrado'
        ];
    }

    // Hashear la contraseña antes de guardarla
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Insertar el nuevo usuario
    $stmt = $mysqli->prepare("INSERT INTO usuarios (email, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $passwordHash);
    if ($stmt->execute()) {
        return [
            'status' => 'success',
            'message' => 'Registro exitoso',
            'user_id' => $mysqli->insert_id,
            'email' => $email
        ];
    } else {
        return [
            'status' => 'error',
            'message' => 'Error en el registro'
        ];
    }
}

?>
