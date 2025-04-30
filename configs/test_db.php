<?php
require_once 'db_config.php'; // Incluye la configuración de la base de datos

$mysqli = getDbConnection(); // Llama a tu función

if ($mysqli) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "Error al conectar.";
}
?>
    