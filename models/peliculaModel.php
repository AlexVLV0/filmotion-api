<?php
// peliculaModel.php
include_once '../configs/db_config.php';

function guardarPeliculasEnBBDD($peliculas) {
    $mysqli = getDbConnection();

    foreach ($peliculas as $pelicula) {
        $tmdb_id = $pelicula['id'];
        $titulo = $pelicula['title'];
        $descripcion = $pelicula['overview'];
        $fecha_lanzamiento = $pelicula['release_date'];
        $imagen_url = 'https://image.tmdb.org/t/p/w500' . $pelicula['poster_path'];
        $duracion = isset($pelicula['runtime']) ? $pelicula['runtime'] : 0;
        $genero = isset($pelicula['genres'][0]['name']) ? $pelicula['genres'][0]['name'] : '';

        // Usamos INSERT IGNORE para evitar duplicados
        $stmt = $mysqli->prepare("INSERT IGNORE INTO peliculas (tmdb_id, titulo, descripcion, fecha_lanzamiento, imagen_url, duracion, genero) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $tmdb_id, $titulo, $descripcion, $fecha_lanzamiento, $imagen_url, $duracion, $genero);
        $stmt->execute();
    }

    $stmt->close();
    $mysqli->close();
}


function obtenerPeliculas() {
    $mysqli = getDbConnection();

    $result = $mysqli->query("SELECT * FROM peliculas");

    $peliculas = [];
    while ($row = $result->fetch_assoc()) {
        $peliculas[] = $row;
    }

    $mysqli->close();

    return $peliculas;
}
?>