<?php
// includes/db.php — Conexão com a base de dados MySQL (mysqli OOP)

define('DB_HOST', 'localhost');
define('DB_NAME', 'barbearia');
define('DB_USER', 'whezy');
define('DB_PASS', 'Edgar!1234');

function getDB(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            die('Falha na conexão: ' . $conn->connect_error);
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}
