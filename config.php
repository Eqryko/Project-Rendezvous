<?php
// config.php
$host = "localhost";
$db   = "astronauticadb";   
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

try {
    $conn = new PDO($dsn, $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    //echo "Connessione riuscita!";
} catch (PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}
?>
