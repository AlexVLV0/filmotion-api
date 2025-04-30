<?php
// index.php
ob_start();
ini_set('display_errors', 1);
ini_set('html_errors', 0);

error_reporting(E_ALL);
header('Content-Type: application/json');  // Establecer el tipo de contenido como JSON
header('Access-Control-Allow-Origin: *');  // Permitir acceso desde cualquier origen

// Incluir el controlador
include_once '../controllers/peliculasController.php';
include_once '../controllers/loginController.php'; // o donde esté loginUsuario()

// Si la solicitud es un GET, devuelve las películas de la base de datos
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['action']) && $_GET['action'] == 'getPeliculas') {
        echo json_encode(obtenerPeliculasBBDD());
    } elseif (isset($_GET['action']) && $_GET['action'] == 'actualizarPeliculas') {
        obtenerYGuardarPeliculas();
        echo json_encode(['message' => 'Películas actualizadas correctamente']);
    } else {
        echo json_encode(['error' => 'Acción no válida']);
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir los datos del formulario
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $action = $_POST['action'] ?? '';  // Obtener la acción (login o register)

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
        exit;
    }

    // Llamar a la función según la acción
    if ($action == 'register') {
        // Registrar usuario
        $result = registerUsuario($email, $password);
    } else if ($action == 'login') {
        // Iniciar sesión
        $result = loginUsuario($email, $password);
    } else if ($action == 'getPerfil') {
        $email = $data['email'] ?? '';
    
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email faltante']);
            exit;
        }
    
        $usuario = obtenerPerfilPorEmail($email);
    
        if ($usuario) {
            echo json_encode(['status' => 'success', 'usuario' => $usuario]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        }
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
        exit;
    }

    // Retornar la respuesta como JSON
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'Método no permitido']);
}


$output = ob_get_clean();
echo $output;

?>