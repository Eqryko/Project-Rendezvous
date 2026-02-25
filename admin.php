<?php
// admin.php
session_start();
require "config.php"; // config.php definisce $conn come PDO

// 1. Controllo Accesso
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Verifica Ruolo Admin (Usando PDO come nelle altre pagine)
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT ruolo FROM utente WHERE id_utente = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['ruolo'] !== 'ADMIN') {
    header("Location: index.php"); // Reindirizza alla home se non è admin
    exit();
}

// 3. Recupero Voci in Attesa
// anche id_originale per capire se è una nuova voce o una modifica
$query_attesa = "SELECT v.*, u.username 
                 FROM voce v 
                 JOIN utente u ON v.creatore = u.id_utente 
                 WHERE v.stato = 'IN_ATTESA' 
                 ORDER BY v.data_creazione DESC";
$voci_attesa = $conn->query($query_attesa)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #11e4ff;
            text-align: left;
        }

        th {
            background-color: rgba(17, 228, 255, 0.2);
        }

        .badge-modifica {
            background: #ffae00;
            color: black;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
        }

        .badge-nuova {
            background: #00ff88;
            color: black;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link">Profile</a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank">Repository</a>
    </header>

    <div class="container" style="margin-top: 80px;">
        <h1>Dashboard Amministratore</h1>
        <h2>Voci in attesa di approvazione</h2>

        <?php if (empty($voci_attesa)): ?>
            <p>Non ci sono voci in attesa di revisione.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nome</th>
                        <th>Autore</th>
                        <th>Tipo Richiesta</th>
                        <th>Azione</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($voci_attesa as $v): ?>
                        <tr>
                            <td><?= strtoupper($v['tipo']) ?></td>
                            <td><?= htmlspecialchars($v['nome']) ?></td>
                            <td><?= htmlspecialchars($v['username']) ?></td>
                            <td>
                                <?php if ($v['id_originale']): ?>
                                    <span class="badge-modifica">MODIFICA</span>
                                <?php else: ?>
                                    <span class="badge-nuova">NUOVA VOCE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="voce.php?id=<?= $v['id_voce'] ?>&admin_review=1" class="btn-blue"
                                    style="background: #11e4ff; color: black; padding: 5px 10px; text-decoration: none; font-weight: bold;">
                                    🔍 Esamina
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>