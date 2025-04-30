<?php
// peliculasController.php
include_once '../models/peliculaModel.php';
include_once '../utils/tmdbUtils.php';
set_time_limit(0); // 0 significa tiempo ilimitado

// Obtener las películas desde TMDb y guardarlas en la base de datos
function obtenerYGuardarPeliculas() {
    set_time_limit(0); // Sin límite de tiempo de ejecución

    $endpoints = ['popular', 'top_rated', 'now_playing', 'upcoming'];
    $max_paginas = 500;

    foreach ($endpoints as $endpoint) {
        $pagina = 1;

        while ($pagina <= $max_paginas) {
            $resultados = obtenerPeliculasTMDb($pagina, $endpoint);

            if (!isset($resultados['results']) || empty($resultados['results'])) {
                break;
            }

            guardarPeliculasEnBBDD($resultados['results']);
            $pagina++;
            sleep(1);
        }
    }
}


// Obtener las películas almacenadas en la base de datos
function obtenerPeliculasBBDD() {
    return obtenerPeliculas();
}



?>
