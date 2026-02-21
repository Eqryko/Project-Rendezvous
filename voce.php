<?php
session_start();
$id = $_GET['id'] ?? null;
require "config.php";

$query = "SELECT v.nome AS nome_voce, 
                 v.stato, 
                 v.data_creazione, 
                 v.data_approvazione, 
                 u.username AS creatore_name, 
                 u2.username AS approvatore_name
          FROM voce v
          INNER JOIN utente u ON v.creatore = u.id_utente 
          LEFT JOIN utente u2 ON v.approvatore = u2.id_utente
          WHERE v.id_voce = ?";

$stmt = $conn->prepare($query);
$stmt->execute([$id]);
$voce = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch associativo

if (!$voce) {
    die("Errore: Voce non trovata.");
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio Voce: <?php echo htmlspecialchars($voce['nome']); ?></title>
    <link rel="stylesheet" href="styles/stylee.css">
    <link rel="stylesheet" href="styles/vstyle.css">
</head>
<body>
    
    <img class="logo" src="media/greenmantis.png">
    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://github.com/Eqryko" target="_blank"> GitHub Profile</a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>
    <a href="profilo.php" class="toggle-link" target="_blank">Profilo</a>
    <div class="container">
        <br><br>
        <h1>Scheda Voce</h1>

        <div class="profile-item">
            <label>Nome Voce:</label>
            <span><?php echo htmlspecialchars($voce["nome_voce"]); ?></span>
        </div>

        <div class="profile-item">
            <label>Stato:</label>
            <span class="status-badge"><?php echo htmlspecialchars($voce["stato"]); ?></span>
        </div>

        <div class="profile-item">
            <label>Creatore:</label>
            <span><?php echo htmlspecialchars($voce["creatore_name"]); ?></span>
        </div>

        <div class="profile-item">
            <label>Data creazione:</label>
            <span><?php echo htmlspecialchars($voce["data_creazione"]); ?></span>
        </div>

        <div class="profile-item">
            <label>Approvatore:</label>
            <span><?php echo htmlspecialchars($voce["approvatore_name"] ?? 'Non ancora approvata'); ?></span>
        </div>

        <div class="profile-item">
            <label>Data approvazione:</label>
            <span><?php echo htmlspecialchars($voce["data_approvazione"]); ?></span>
        </div>
    </div>
</body>
</html>