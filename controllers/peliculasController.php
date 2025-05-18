<?php
// peliculasController.php
include_once '../models/peliculaModel.php';
include_once '../utils/tmdbUtils.php';
set_time_limit(0); 

function obtenerYGuardarPeliculas() {
    set_time_limit(0); 

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


function obtenerPeliculasBBDD() {
    return obtenerPeliculas();
}



?>
