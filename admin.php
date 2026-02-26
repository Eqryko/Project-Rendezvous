<?php
// admin.php
session_start();
require "config.php"; 

// 1. Controllo Accesso Base
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// 2. Recupero info utente loggato
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT ruolo FROM utente WHERE id_utente = ?");
$stmt->execute([$userId]);
$userLoggato = $stmt->fetch(PDO::FETCH_ASSOC);

// Solo ADMIN e UTENTE possono vedere questa pagina
if (!$userLoggato || ($userLoggato['ruolo'] !== 'ADMIN' && $userLoggato['ruolo'] !== 'UTENTE')) {
    header("Location: index.php"); 
    exit();
}

$isAdmin = ($userLoggato['ruolo'] === 'ADMIN');

// 3. Gestione Cambio Ruolo (SOLO SE ADMIN)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambia_ruolo'])) {
    if ($isAdmin) {
        $id_target = $_POST['id_utente_target'];
        $nuovo_ruolo = $_POST['nuovo_ruolo'];

        if ($id_target != $userId) {
            $updateStmt = $conn->prepare("UPDATE utente SET ruolo = ? WHERE id_utente = ?");
            $updateStmt->execute([$nuovo_ruolo, $id_target]);
            $msg = "Ruolo aggiornato con successo!";
        }
    } else {
        $error = "Azione non consentita.";
    }
}

// 4. Recupero Voci in Attesa (Visibili a entrambi)
$query_attesa = "SELECT v.*, u.username 
                 FROM voce v 
                 JOIN utente u ON v.creatore = u.id_utente 
                 WHERE v.stato = 'IN_ATTESA' 
                 ORDER BY v.data_creazione DESC";
$voci_attesa = $conn->query($query_attesa)->fetchAll(PDO::FETCH_ASSOC);

// 5. Recupero Lista Utenti (SOLO SE ADMIN)
$utenti = [];
if ($isAdmin) {
    $utenti = $conn->query("SELECT id_utente, username, ruolo FROM utente ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; color: white; background: rgba(0, 0, 0, 0.3); }
        th, td { padding: 12px; border: 1px solid #11e4ff; text-align: left; }
        th { background-color: rgba(17, 228, 255, 0.2); }
        .section-title { margin-top: 50px; border-bottom: 2px solid #11e4ff; padding-bottom: 10px; }
        .btn-update { background: #11e4ff; border: none; padding: 5px 10px; cursor: pointer; font-weight: bold; color: black; }
        .alert { padding: 10px; background: #00ff88; color: black; margin-bottom: 20px; }
        .error { padding: 10px; background: #ff4444; color: white; margin-bottom: 20px; }
    </style>
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

<br><br>
    <div class="container" style="margin-top: 80px; padding: 20px;">
        <h1><?= $isAdmin ? "Dashboard Amministratore" : "Area Revisione" ?></h1>
        
        <?php if (isset($msg)): ?> <div class="alert"><?= $msg ?></div> <?php endif; ?>
        <?php if (isset($error)): ?> <div class="error"><?= $error ?></div> <?php endif; ?>

        <h2 class="section-title">Voci in attesa di approvazione</h2>
        <?php if (empty($voci_attesa)): ?>
            <p>Non ci sono voci in attesa.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nome</th>
                        <th>Autore</th>
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
                                <a href="voce.php?id=<?= $v['id_voce'] ?>&admin_review=1" class="btn-update" style="text-decoration:none;">🔍 Esamina</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <h2 class="section-title">Gestione Ruoli Utenti</h2>
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Ruolo Attuale</th>
                        <th>Cambia Ruolo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utenti as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><strong><?= $u['ruolo'] ?></strong></td>
                            <td>
                                <form method="POST" style="display: flex; gap: 10px;">
                                    <input type="hidden" name="id_utente_target" value="<?= $u['id_utente'] ?>">
                                    <select name="nuovo_ruolo" style="background:#222; color:white; border:1px solid #11e4ff;">
                                        <option value="UTENTE" <?= $u['ruolo'] === 'UTENTE' ? 'selected' : '' ?>>UTENTE</option>
                                        <option value="ADMIN" <?= $u['ruolo'] === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                                    </select>
                                    <button type="submit" name="cambia_ruolo" class="btn-update">Aggiorna</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
    <br><br><br><br><footer>
        <p id="usage"></p>
        <i> Credits: <br>
            Refosco Enrico - enricoorefosco@gmail.com <br>
            Munaro Alex - alexmunaro22@gmail.com
        </i> <br>
    </footer>
</body>
</html>