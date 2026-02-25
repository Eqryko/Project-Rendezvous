<?php
// config.php
// Nome database: astronauticadb
$host = "localhost";
$db   = "astronauticadb";   
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO è un'estensione di PHP che fornisce un'interfaccia per accedere a diversi database in modo sicuro e efficiente.

try {
    $conn = new PDO($dsn, $user, $pass); // crea connessione al database usando PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // imposta il modo di gestione degli errori su eccezioni
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // imposta il modo di fetch predefinito su array associativi
    //echo "Connessione riuscita!";
} catch (PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}
?>