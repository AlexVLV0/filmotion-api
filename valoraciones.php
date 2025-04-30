<?php
header('Content-Type: application/json');

$json = file_get_contents("php://input");
$data = json_decode($json, true);

$idUsuario = $data["idUsuario"];
$idPelicula = $data["idPelicula"];
$valoracion = $data["valoracion"];
$feliz = $data["feliz"];

// Aquí harías tu conexión a la BBDD y guardarías esos datos.
// Por ahora simulemos que todo va bien:

echo json_encode([
    "status" => "ok",
    "message" => "Valoración guardada correctamente"
]);
