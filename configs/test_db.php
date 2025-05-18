<?php
require_once 'db_config.php';

$mysqli = getDbConnection();

if ($mysqli) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "Error al conectar.";
}
?>
    