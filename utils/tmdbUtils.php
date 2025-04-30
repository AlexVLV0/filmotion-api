<?php
// tmdbUtils.php
function obtenerPeliculasTMDb($pagina = 1, $endpoint = 'popular') {
    $api_key = '3d9dbb91d923d72843a5124796a45954';
    $tmdb_url = "https://api.themoviedb.org/3/movie/$endpoint?api_key=$api_key&language=es-ES&page=$pagina";

    $response = file_get_contents($tmdb_url);
    if ($response === FALSE) {
        die('Error al obtener los datos de TMDb');
    }

    return json_decode($response, true);
}

?>
