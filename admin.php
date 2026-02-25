<?php
// admin.php
session_start();

/*
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}
*/
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}else{
    require "config.php";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Connessione fallita.");
    }

    // Verifica se l'utente è un admin
    $userId = $_SESSION['user_id'];
    $sql = "SELECT ruolo FROM utente WHERE id_utente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['ruolo'] !== 'ADMIN') {
            header("Location: admin.php");
            exit();
        }
    } else {
    
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="styles/rstyle.css">
    <link rel="stylesheet" href="styles/nav_style.css">
</head>
<body>
    <img class="logo" src="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <br><br><br><br>
    <div class="container">
        <h1>Benvenuto, Admin!</h1>
        <p>Qui puoi gestire le voci e gli utenti del tuo sito.</p>
    </div>

    <footer>
        <p id="usage"></p>
        <i> Credits: <br>
            Refosco Enrico - enricoorefosco@gmail.com <br>
            Munaro Alex - alexmunaro22@gmail.com
        </i> <br>
    </footer>
</html>