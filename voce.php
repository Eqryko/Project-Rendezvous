<?php
session_start();
$id = $_GET['id'] ?? null;
require "config.php";

// 1. Corretto "apporvatore" in "approvatore"
// 2. Aggiunti alias (AS nome_voce, ecc.) per accedere facilmente ai dati
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
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .profile-item {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .profile-item label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
            text-transform: uppercase;
            font-size: 12px;
        }

        .profile-item span {
            color: #333;
            font-size: 18px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            background-color: #e2e2e2;
            font-size: 14px;
            font-weight: bold;
        }

        .actions {
            text-align: center;
            margin-top: 30px;
        }

        a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: 0.3s;
        }

        a:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <a href="profilo.php" class="toggle-link" target="_blank">Profilo</a>
    <div class="container">
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