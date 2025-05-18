<?php
include_once '../models/usuarioModel.php';
require_once '../vendor/autoload.php';

use \Firebase\JWT\JWT;

function loginUsuario($email, $password) {
    $usuario = obtenerUsuarioPorEmail($email);  

    if ($usuario) {
        if (password_verify($password, $usuario['password'])) {
            $clave_secreta = "tu_clave_secreta_segura";
            $payload = [
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24),
                'data' => [
                    'user_id' => $usuario['id'],
                    'email' => $usuario['email']
                ]
            ];
            $jwt = JWT::encode($payload, $clave_secreta, 'HS256');

            return [
                'status' => 'success',
                'message' => 'Login exitoso',
                'token' => $jwt,
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
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;  
    } else {
        return false; 
    }
}

function registerUsuario($email, $password) {
    $mysqli = getDbConnection();
    
    if (!validarEmail($email)) {
        return [
            'status' => 'error',
            'message' => 'El correo electrónico no tiene un formato válido'
        ];
    }

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

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

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
