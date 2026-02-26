<?php
// admin.php
session_start();
require "config.php"; 

// controllo accesso: se non loggato, reindirizza a login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// recupero ruolo utente loggato
$userId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT ruolo FROM utente WHERE id_utente = ?");
$stmt->execute([$userId]);
$userLoggato = $stmt->fetch(PDO::FETCH_ASSOC);

// se ruolo non è ADMIN o UTENTE, reindirizza a index.php
if (!$userLoggato || ($userLoggato['ruolo'] !== 'ADMIN' && $userLoggato['ruolo'] !== 'UTENTE')) {
    header("Location: index.php"); 
    exit();
}

// variabili per gestione messaggi di stato
$isAdmin = ($userLoggato['ruolo'] === 'ADMIN');

// gestione cambio ruolo (solo per admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambia_ruolo'])) {
    if ($isAdmin) {
        $id_target = $_POST['id_utente_target'];
        $nuovo_ruolo = $_POST['nuovo_ruolo'];
        if ($id_target != $userId) {
            $updateStmt = $conn->prepare("UPDATE utente SET ruolo = ? WHERE id_utente = ?");
            $updateStmt->execute([$nuovo_ruolo, $id_target]);
            $msg = "ACCESS_GRANTED: Ruolo aggiornato.";
        }
    }
}

// recupero voci in attesa di approvazione (solo per admin)
$query_attesa = "SELECT v.*, u.username FROM voce v JOIN utente u ON v.creatore = u.id_utente WHERE v.stato = 'IN_ATTESA' ORDER BY v.data_creazione DESC";
$voci_attesa = $conn->query($query_attesa)->fetchAll(PDO::FETCH_ASSOC);

// recupero lista utenti (solo per admin)
$utenti = [];
if ($isAdmin) {
    $utenti = $conn->query("SELECT id_utente, username, ruolo FROM utente ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Control Unit — Rendezvous</title>
    <<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <style>
        /* Override e aggiunte specifiche per Admin Dashboard */
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 120px 24px; }
        
        .status-msg {
            font-family: monospace;
            padding: 15px;
            margin-bottom: 30px;
            border-left: 4px solid var(--accent);
            background: #111;
            text-transform: uppercase;
            font-size: 0.8rem;
        }

        .section-header {
            margin-top: 80px;
            border-bottom: 1px solid #222;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .section-header h2 {
            font-size: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin: 0;
            color: var(--accent);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            font-family: monospace;
            font-size: 0.7rem;
            color: #444;
            text-transform: uppercase;
            padding: 15px 0;
            border-bottom: 1px solid #222;
        }

        td {
            padding: 20px 0;
            border-bottom: 1px solid #111;
            font-size: 0.9rem;
        }

        .type-tag {
            font-family: monospace;
            font-size: 0.7rem;
            padding: 2px 8px;
            border: 1px solid #333;
            color: #888;
        }

        .btn-examine {
            background: #fff;
            color: #000;
            padding: 8px 16px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .btn-examine:hover { background: var(--accent); }

        select.admin-select {
            background: #000;
            color: #fff;
            border: 1px solid #333;
            padding: 5px;
            font-family: monospace;
            font-size: 0.8rem;
            outline: none;
        }

        .update-mini-btn {
            background: transparent;
            color: var(--accent);
            border: 1px solid var(--accent);
            padding: 5px 10px;
            cursor: pointer;
            font-family: monospace;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .update-mini-btn:hover { background: var(--accent); color: #000; }
    </style>
</head>
<body>

    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="admin-container">
        <div class="reveal">
            <h1 style="font-size: clamp(3rem, 8vw, 5rem); line-height: 0.9; margin-bottom: 20px;">
                CONTROL<br>CENTER
            </h1>
            <p style="color: #444; letter-spacing: 0.3em; font-family: monospace;">
                LEVEL: <?= $userLoggato['ruolo'] ?> // SESSION_ACTIVE
            </p>
        </div>

        <?php if (isset($msg)): ?>
            <div class="status-msg reveal"><?= $msg ?></div>
        <?php endif; ?>

        <div class="section-header reveal">
            <h2>Pending Approvals</h2>
            <span style="font-family: monospace; font-size: 0.7rem; color: #444;">
                COUNT: <?= count($voci_attesa) ?>
            </span>
        </div>

        <?php if (empty($voci_attesa)): ?>
            <p class="reveal" style="padding: 40px 0; color: #333; font-family: monospace;">[ NO_DATA_PENDING ]</p>
        <?php else: ?>
            <table class="reveal">
                <thead>
                    <tr>
                        <th width="15%">Classification</th>
                        <th width="45%">Designation</th>
                        <th width="20%">Operator</th>
                        <th width="20%">Command</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($voci_attesa as $v): ?>
                        <tr>
                            <td><span class="type-tag"><?= $v['tipo'] ?></span></td>
                            <td style="font-weight: 700; letter-spacing: -0.02em; font-size: 1.1rem;">
                                <?= htmlspecialchars($v['nome']) ?>
                            </td>
                            <td style="color: #666; font-family: monospace;">@<?= htmlspecialchars($v['username']) ?></td>
                            <td>
                                <a href="voce.php?id=<?= $v['id_voce'] ?>&admin_review=1" class="btn-examine">Review Entry</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
            <div class="section-header reveal" style="margin-top: 120px;">
                <h2>User Management</h2>
            </div>
            <table class="reveal">
                <thead>
                    <tr>
                        <th width="30%">Username</th>
                        <th width="20%">Current Role</th>
                        <th width="50%">Access Override</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utenti as $u): ?>
                        <tr>
                            <td style="font-weight: 700;"><?= htmlspecialchars($u['username']) ?></td>
                            <td style="font-family: monospace; font-size: 0.8rem; color: var(--accent);">
                                [ <?= $u['ruolo'] ?> ]
                            </td>
                            <td>
                                <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                    <input type="hidden" name="id_utente_target" value="<?= $u['id_utente'] ?>">
                                    <select name="nuovo_ruolo" class="admin-select">
                                        <option value="UTENTE" <?= $u['ruolo'] === 'UTENTE' ? 'selected' : '' ?>>UTENTE</option>
                                        <option value="ADMIN" <?= $u['ruolo'] === 'ADMIN' ? 'selected' : '' ?>>ADMIN</option>
                                    </select>
                                    <button type="submit" name="cambia_ruolo" class="update-mini-btn">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>

    <footer>
        <i style="color: #222; font-family: monospace;">System Terminal // Refosco & Munaro</i>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('active');
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });
    </script>
</body>
</html>