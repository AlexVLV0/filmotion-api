<?php
// db_config.php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'filmotion');

// Conexión a la base de datos
function getDbConnection() {
    $mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($mysqli->connect_error) {
        die("Conexión fallida: " . $mysqli->connect_error);
    }
    return $mysqli;
}
?>
