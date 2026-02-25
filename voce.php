<?php
session_start();
$id = $_GET['id'] ?? null;
require "config.php";

// 1. Recupero i dati base dalla tabella 'voce' includendo il campo 'tipo'
$query = "SELECT v.nome AS nome_voce, 
                 v.tipo, 
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
$voce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voce) {
    die("Errore: Voce non trovata.");
}

// 2. Recupero i dettagli specifici in base al tipo
$tipo = $voce['tipo'];
$dettagli = null;

// Eseguiamo query specifiche per tipo per risolvere le relazioni (JOIN)
switch ($tipo) {
    case 'missione':
        $query_dettagli = "SELECT m.*, 
                             v1.nome AS nome_azienda, 
                             v2.nome AS nome_programma,
                             v3.nome AS nome_vettore,
                             v4.nome AS nome_veicolo,
                             e.data AS data_lancio,
                             e.luogo AS luogo_lancio
                      FROM missione m
                      LEFT JOIN voce v1 ON m.id_azienda = v1.id_voce
                      LEFT JOIN voce v2 ON m.id_programma = v2.id_voce
                      LEFT JOIN voce v3 ON m.id_vettore = v3.id_voce
                      LEFT JOIN voce v4 ON m.id_veicolo = v4.id_voce
                      LEFT JOIN evento e ON m.id_lancio = e.id_voce
                      WHERE m.id_voce = ?";
        break;

    case 'astronauta':
        // Se avessi una tabella per le nazioni, faresti un altro join qui
        $query_dettagli = "SELECT * FROM astronauta WHERE id_voce = ?";
        break;

    case 'veicolo':
        $query_dettagli = "SELECT v.*, a.nomeIntero AS azienda_produttrice 
                           FROM veicolo v 
                           LEFT JOIN azienda a ON v.id_azienda = a.id_voce 
                           WHERE v.id_voce = ?";
        break;

    default:
        $query_dettagli = "SELECT * FROM $tipo WHERE id_voce = ?";
        break;
}

$stmt_det = $conn->prepare($query_dettagli);
$stmt_det->execute([$id]);
$dettagli = $stmt_det->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio <?php echo ucfirst($tipo) ?>: <?php echo htmlspecialchars($voce['nome_voce']); ?></title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
</head>
<body>

    <img class="logo" src="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="container">
        <h1><?php echo htmlspecialchars($voce["nome_voce"]); ?></h1>
        <div class="profile-section">
            <div class="profile-item">
                <span><?php echo ucfirst(htmlspecialchars($voce["tipo"])); ?></span>
        </div>

        <div class="details-section">
            <h3>Informazioni Generali</h3>
            <div class="profile-item">
                <label>Nome:</label>
                <span><?php echo htmlspecialchars($voce["nome_voce"]); ?></span>
            </div>
            <?php if ($dettagli): ?>
                <?php foreach ($dettagli as $chiave => $valore):
                    // 1. Saltiamo l'ID principale
                    if ($chiave == 'id_voce')
                        continue;

                    // 2. Saltiamo TUTTE le chiavi che iniziano con 'id_' (id_azienda, id_lancio, ecc.)
                    if (strpos($chiave, 'id_') === 0)
                        continue;

                    // 3. Saltiamo il campo 'nome' se è identico a 'nome_voce' (per evitare ripetizioni)
                    if ($chiave == 'nome' && $valore == $voce['nome_voce'])
                        continue;
                    ?>

                    <div class="profile-item">
                        <label><?php echo ucwords(str_replace('_', ' ', $chiave)); ?>:</label>
                        <span>
                            <?php
                            // Gestione speciale per la durata
                            if ($chiave == 'durata' && $valore) {
                                echo htmlspecialchars($valore) . " giorni";
                            } else {
                                echo htmlspecialchars($valore ?? 'N/D');
                            }
                            ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Nessun dettaglio aggiuntivo trovato per questa voce.</p>
            <?php endif; ?>
        </div>
        <?php if ($voce['approvatore_name']): ?>
            <div class="approval-info" style="margin-top: 20px; font-size: 0.8em; color: gray;">
                Creata da
                <?php echo htmlspecialchars($voce["creatore_name"]); ?> il
                <?php echo $voce["data_creazione"]; ?>
                <div class="profile-item">
                    Stato:</label>
                    <span style="color:black;" class="status-badge">
                        <?php echo htmlspecialchars($voce["stato"]); ?>
                    </span>
                    <br>Approvata da <?php echo htmlspecialchars($voce["approvatore_name"]); ?> il
                    <?php echo $voce["data_approvazione"]; ?>
                </div>
            <?php endif; ?>


        </div>
</body>
</html>