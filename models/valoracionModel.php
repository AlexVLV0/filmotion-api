<?php
include_once '../configs/db_config.php';

function obtenerValoracion($id_usuario, $id_pelicula) {
    $mysqli = getDbConnection();

    $stmt = $mysqli->prepare("SELECT * FROM valoraciones WHERE id_usuario = ? AND id_pelicula = ?");
    $stmt->bind_param("ii", $id_usuario, $id_pelicula);
    $stmt->execute();
    $result = $stmt->get_result();

    $valoracion = $result->fetch_assoc();

    $stmt->close();
    $mysqli->close();

    return $valoracion;
}

function obtenerPeliculasValoradasPorUsuario($id_usuario) {
    $mysqli = getDbConnection();

    $stmt = $mysqli->prepare("
        SELECT p.*, v.puntuacion, v.emocion
        FROM peliculas p
        JOIN valoraciones v ON p.id = v.id_pelicula
        WHERE v.id_usuario = ?

    ");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $result = $stmt->get_result();
    $peliculas = [];

    while ($row = $result->fetch_assoc()) {
        $peliculas[] = $row;
    }

    $stmt->close();
    $mysqli->close();

    return $peliculas;
}

function obtenerPeliculasOlvidadasPorUsuario($id_usuario) {
    $mysqli = getDbConnection();

    $stmt = $mysqli->prepare("SELECT p.*, v.fecha, v.puntuacion, v.emocion FROM valoraciones v
                              JOIN peliculas p ON p.id = v.id_pelicula
                              WHERE v.id_usuario = ?
                              ORDER BY v.fecha ASC LIMIT 5");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();

    $result = $stmt->get_result();
    $peliculas = [];

    while ($row = $result->fetch_assoc()) {
        $peliculas[] = $row;
    }

    $stmt->close();
    $mysqli->close();

    return $peliculas;
}

function obtenerRecomendacionPorAfinidad($id_usuario, $emocion_actual, $preferencia_deseada) {
    $mysqli = getDbConnection();

    $stmt = $mysqli->prepare("
        SELECT id_pelicula, emocion 
        FROM valoraciones 
        WHERE id_usuario = ?
    ");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $mis_valoraciones = [];
    while ($row = $result->fetch_assoc()) {
        $mis_valoraciones[$row['id_pelicula']] = $row['emocion'];
    }
    $stmt->close();

    if (count($mis_valoraciones) < 5) {
        return null;
    }

    $ids_mis_pelis = implode(",", array_map("intval", array_keys($mis_valoraciones)));

    $query = "
        SELECT v2.id_usuario, 
               SUM(v1.emocion = v2.emocion) as coincidencias, 
               COUNT(*) as total_comunes
        FROM valoraciones v1
        JOIN valoraciones v2 ON v1.id_pelicula = v2.id_pelicula
        WHERE v1.id_usuario = ? 
          AND v2.id_usuario != ?
          AND v1.id_pelicula IN ($ids_mis_pelis)
        GROUP BY v2.id_usuario
        HAVING total_comunes >= 5 AND (coincidencias / total_comunes) >= 0.6
    ";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("ii", $id_usuario, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    $usuarios_afines = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios_afines[] = $row['id_usuario'];
    }
    $stmt->close();

    if (empty($usuarios_afines)) {
        return null;
    }

    $usuario_objetivo = $usuarios_afines[array_rand($usuarios_afines)];
    $mis_ids = array_keys($mis_valoraciones);
    $placeholders = implode(",", array_fill(0, count($mis_ids), "?"));
    $types = str_repeat("i", count($mis_ids) + 2);
    $params = array_merge([$usuario_objetivo, $preferencia_deseada], $mis_ids);

    $sql = "
        SELECT p.*
        FROM valoraciones v
        JOIN peliculas p ON p.id = v.id_pelicula
        WHERE v.id_usuario = ? 
          AND v.emocion = ? 
          AND v.puntuacion >= 3
          AND v.id_pelicula NOT IN ($placeholders)
        ORDER BY RAND()
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $pelicula = $result->fetch_assoc();

    $stmt->close();
    $mysqli->close();

    return $pelicula ?: null;
}

function obtenerPreferenciaPorEmocion($id_usuario, $emocion_actual) {
    $prefs = json_decode(file_get_contents(__DIR__ . '/../prefs.json'), true);
    $clave = "usuario_" . $id_usuario;
    if (!isset($prefs[$clave])) return null;

    return $emocion_actual === "1" ? $prefs[$clave]['pref_feliz'] : $prefs[$clave]['pref_triste'];
}




