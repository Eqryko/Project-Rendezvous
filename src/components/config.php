<?php
// config.php
// Nome database: astronauticadb
$host = "localhost";
$db   = "astronauticadb";   
$user = "root";
$pass = "";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$db;charset=$charset"; // stringa x connessione db

// PDO = estensione PHP che fornisce un'interfaccia per accedere a diversi database in modo sicuro e efficiente. 
// (scalabilità, sicurezza, gestione degli errori)
// PHP Data Objects

try {
    // $conn = new mysqli($host, $user, $pass, $db); // crea connessione al database usando mysqli

    $conn = new PDO($dsn, $user, $pass); // crea connessione al database usando PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // imposta il modo di gestione degli errori su eccezioni
    // attributi di pdo per la gestione degli errori e il modo di fetch dei risultati
    /*
        ATTR_ERRMODE: è una costante che indica il modo in cui PDO gestisce gli errori.
        ERRMODE_EXCEPTION: è una costante che indica che PDO lancerà un'eccezione (PDOException) quando si verifica un errore.
        ATTR_DEFAULT_FETCH_MODE: è una costante che indica il modo di fetch predefinito per le query.
        FETCH_ASSOC: è una costante che indica che i risultati delle query saranno restituiti come array associativi, dove le chiavi sono i nomi delle colonne del database.
    */
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); // imposta il modo di fetch predefinito su array associativi
    
    //echo "Connessione riuscita!";
    
} catch (PDOException $e) {
    die("Errore connessione: " . $e->getMessage());
}
?>