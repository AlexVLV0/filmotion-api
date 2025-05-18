<?php

ob_start();
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
ini_set('html_errors', 0);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include_once '../controllers/peliculasController.php';
include_once '../controllers/loginController.php';
include_once '../models/valoracionModel.php';


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['action']) && $_GET['action'] == 'getPeliculas') {
        echo json_encode(obtenerPeliculasBBDD());
    } elseif (isset($_GET['action']) && $_GET['action'] == 'actualizarPeliculas') {
        obtenerYGuardarPeliculas();
        echo json_encode(['message' => 'Películas actualizadas correctamente']);
    } elseif (isset($_GET['action']) && $_GET['action'] == 'buscarPeliculas' && isset($_GET['query'])) {
        $query = $_GET['query'];
        echo json_encode(buscarPeliculasPorTitulo($query));
    } else {
        echo json_encode(['error' => 'Acción no válida']);
    }
    exit;
}

// POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['login', 'register'])) {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
            exit;
        }

        if ($action == 'register') {
            $result = registerUsuario($email, $password);
        } elseif ($action == 'login') {
            $result = loginUsuario($email, $password);
        }

        echo json_encode($result);
        exit;
    }

    if ($action == 'getPerfil') {
        $email = $_POST['email'] ?? '';
        if (empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Email faltante']);
            exit;
        }

        $usuario = obtenerPerfilPorEmail($email);
        echo json_encode($usuario ? ['status' => 'success', 'usuario' => $usuario] : ['status' => 'error', 'message' => 'Usuario no encontrado']);
        exit;
    }

    if ($action == 'guardarValoracion') {
        $id_usuario = $_POST['id_usuario'] ?? null;
        $id_pelicula = $_POST['id_pelicula'] ?? null;
        $puntuacion = $_POST['puntuacion'] ?? null;
        $emocion = $_POST['emocion'] ?? null;
        $fecha = date('Y-m-d');

        if (!$id_usuario || !$id_pelicula || !$puntuacion || $emocion === null) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
            exit;
        }

        include_once '../configs/db_config.php';
        $mysqli = getDbConnection();
        $stmt = $mysqli->prepare("
            INSERT INTO valoraciones (id_usuario, id_pelicula, puntuacion, emocion, fecha)
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE puntuacion = VALUES(puntuacion), emocion = VALUES(emocion), fecha = VALUES(fecha)
        ");

        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Error en prepare(): ' . $mysqli->error]);
            exit;
        }

        $stmt->bind_param("iiiis", $id_usuario, $id_pelicula, $puntuacion, $emocion, $fecha);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Valoración guardada']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar valoración: ' . $stmt->error]);
        }

        $stmt->close();
        $mysqli->close();
        exit;
    }

    if ($action == 'consultarValoracion') {
        $id_usuario = $_POST['id_usuario'] ?? null;
        $id_pelicula = $_POST['id_pelicula'] ?? null;

        if (!$id_usuario || !$id_pelicula) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos para consultar']);
            exit;
        }

        include_once '../models/valoracionModel.php';
        $valoracion = obtenerValoracion($id_usuario, $id_pelicula);
        echo json_encode($valoracion ? ['status' => 'success', 'valoracion' => $valoracion] : ['status' => 'not_found', 'message' => 'No hay valoración previa']);
        exit;
    }

    if ($action == 'getPeliculasValoradas') {
        $id_usuario = $_POST['id_usuario'] ?? null;
    
        if (!$id_usuario) {
            echo json_encode(['status' => 'error', 'message' => 'Falta el ID de usuario']);
            exit;
        }
    
        include_once '../models/valoracionModel.php';
        $peliculas = obtenerPeliculasValoradasPorUsuario($id_usuario);
    
        echo json_encode($peliculas);
        exit;
    }

    if ($action == 'getOlvidada') {
        error_log("getOlvidada: usuario ID recibido = " . ($_POST['id_usuario'] ?? 'NULL'));
        $id_usuario = $_POST['id_usuario'] ?? null;
    
        if (!$id_usuario) {
            echo json_encode(['status' => 'error', 'message' => 'Falta ID de usuario']);
            exit;
        }
    
        include_once '../models/valoracionModel.php';
        $peliculas = obtenerPeliculasOlvidadasPorUsuario($id_usuario);
        error_log("Películas olvidadas encontradas: " . json_encode($peliculas));

        if (empty($peliculas)) {
            echo json_encode(['status' => 'not_found', 'message' => 'No hay películas olvidadas']);
        } else {
            $aleatoria = $peliculas[array_rand($peliculas)];
            echo json_encode(['status' => 'success', 'pelicula' => $aleatoria]);
        }
        exit;

    }  if ($action == 'getRecomendacion') {
        $id_usuario = $_POST['id_usuario'] ?? null;
        $emocion_actual = $_POST['emocion_actual'] ?? null;
    
        if (!$id_usuario || $emocion_actual === null) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan parámetros']);
            exit;
        }
    
        include_once '../models/valoracionModel.php';
        $preferencia_deseada = $_POST['preferencia'] ?? null;
        if ($preferencia_deseada === null) {
    echo json_encode(['status' => 'error', 'message' => 'Falta preferencia']);
    exit;
}

    
        $pelicula = obtenerRecomendacionPorAfinidad($id_usuario, $emocion_actual, $preferencia_deseada);
    
        if ($pelicula) {
            echo json_encode(['status' => 'success', 'pelicula' => $pelicula]);
        } else {
            echo json_encode(['status' => 'not_found', 'message' => 'No hay recomendaciones']);
        }
        exit;
    } if ($action == 'guardarPreferenciasEmocionales') {
        $id_usuario = $_POST['id_usuario'] ?? null;
        $pref_feliz = $_POST['pref_feliz'] ?? null;
        $pref_triste = $_POST['pref_triste'] ?? null;
    
        error_log("➡️ guardarPreferenciasEmocionales: id=$id_usuario, feliz=$pref_feliz, triste=$pref_triste");
    
        if (!$id_usuario || $pref_feliz === null || $pref_triste === null) {
            error_log("Faltan datos");
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos']);
            exit;
        }
    
        include_once '../configs/db_config.php';
        $mysqli = getDbConnection();
        $stmt = $mysqli->prepare("UPDATE usuarios SET pref_feliz = ?, pref_triste = ? WHERE id = ?");
        $stmt->bind_param("iii", $pref_feliz, $pref_triste, $id_usuario);
    
        if ($stmt->execute()) {
            error_log("Preferencias guardadas correctamente para usuario $id_usuario");
            echo json_encode(['status' => 'success']);
        } else {
            error_log("Error en execute(): " . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Error en base de datos']);
        }
    
        $stmt->close();
        $mysqli->close();
        exit;
    } if ($action == 'getPreferenciasEmocionales') {
        $id_usuario = $_POST['id_usuario'] ?? null;
    
        error_log("🔍 getPreferenciasEmocionales: id=$id_usuario");
    
        if (!$id_usuario) {
            echo json_encode(['status' => 'error', 'message' => 'Falta ID']);
            exit;
        }
    
        include_once '../configs/db_config.php';
        $mysqli = getDbConnection();
        $stmt = $mysqli->prepare("SELECT pref_feliz, pref_triste FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
    
        if ($result) {
            error_log("Preferencias recuperadas: " . json_encode($result));
            echo json_encode(['status' => 'success', 'preferencias' => $result]);
        } else {
            error_log("Usuario no encontrado o sin preferencias");
            echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
        }
    
        $stmt->close();
        $mysqli->close();
        exit;
    }
    
    
    
    
    

    echo json_encode(['status' => 'error', 'message' => 'Acción no válida']);
    exit;
}

echo json_encode(['error' => 'Método no permitido']);
$output = ob_get_clean();
echo $output;
