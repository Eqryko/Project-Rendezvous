<?php
// profilo.php
session_start();
require "assets/config.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit();
}

$messaggio = "";
$errore = "";
$edit_mode = isset($_POST['enable_edit']);

// Logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: auth/login.php");
    exit();
}

// Salvataggio modifiche
if (isset($_POST['save_profile'])) {
    $user_id = $_SESSION["user_id"];
    $nuovo_username = trim($_POST['username']);
    $nuovo_nome = trim($_POST['nome']);
    $nuovo_cognome = trim($_POST['cognome']);
    $nuova_email = trim($_POST['email']);
    $nuova_pass = $_POST['password'];

    if (empty($nuovo_username) || empty($nuovo_nome) || empty($nuovo_cognome) || empty($nuova_email)) {
        $errore = "ERRORE: Campi obbligatori mancanti.";
        $edit_mode = true;
    } elseif (!empty($nuova_pass) && strlen($nuova_pass) < 5) {
        $errore = "ERRORE: Password troppo breve.";
        $edit_mode = true;
    } else {
        try {
            $stmt_check = $conn->prepare("SELECT id_utente FROM utente WHERE username = ? AND id_utente != ?");
            $stmt_check->execute([$nuovo_username, $user_id]);

            if ($stmt_check->fetch()) {
                $errore = "ERRORE: Username già in uso.";
                $edit_mode = true;
            } else {
                if (!empty($nuova_pass)) {
                    // Nota: Qui puoi rimettere password_hash se decidi di usarlo
                    $sql = "UPDATE utente SET username = ?, nome = ?, cognome = ?, email = ?, password_hash = ? WHERE id_utente = ?";
                    $params = [$nuovo_username, $nuovo_nome, $nuovo_cognome, $nuova_email, $nuova_pass, $user_id];
                } else {
                    $sql = "UPDATE utente SET username = ?, nome = ?, cognome = ?, email = ? WHERE id_utente = ?";
                    $params = [$nuovo_username, $nuovo_nome, $nuovo_cognome, $nuova_email, $user_id];
                }

                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                $_SESSION["username"] = $nuovo_username;
                $_SESSION["nome"] = $nuovo_nome;
                $_SESSION["cognome"] = $nuovo_cognome;
                $_SESSION["email"] = $nuova_email;

                $messaggio = "SUCCESS: Profilo sincronizzato.";
                $edit_mode = false;
            }
        } catch (PDOException $e) {
            $errore = "SYSTEM_FAIL: " . $e->getMessage();
        }
    }
}

// Recupero voci utente
$voci_utente = [];
$stmt_voci = $conn->prepare("SELECT id_voce, nome, tipo, stato FROM voce WHERE creatore = ? ORDER BY id_voce DESC");
$stmt_voci->execute([$_SESSION["user_id"]]);
$voci_utente = $stmt_voci->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Dossier — <?= htmlspecialchars($_SESSION["username"]) ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <link rel="stylesheet" href="styles/profiloStyle.css">
</head>
<body>

    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="ricerca.php" class="toggle-link">Archive</a>
        <?php if ($_SESSION['ruolo'] === 'ADMIN'): ?>
            <a href="admin.php" class="toggle-link" style="color:var(--accent)">Control Unit</a>
        <?php endif; ?>
    </header>

    <div class="profile-grid">
        
        <section class="profile-info reveal">
            <h1>DOSSIER_<?= htmlspecialchars($_SESSION["username"]) ?></h1>
            <p style="font-family: monospace; color: #444; margin-bottom: 40px;">
                ID: <?= $_SESSION["user_id"] ?> // RANK: <?= $_SESSION["ruolo"] ?>
            </p>

            <?php if ($messaggio): ?> <div class="msg ok" style="border-left: 3px solid var(--accent); padding: 10px; font-family: monospace; font-size: 0.8rem; margin-bottom: 20px;"><?= $messaggio ?></div> <?php endif; ?>
            <?php if ($errore): ?> <div class="msg err" style="border-left: 3px solid #ff4d4d; padding: 10px; font-family: monospace; font-size: 0.8rem; margin-bottom: 20px; color: #ff4d4d;"><?= $errore ?></div> <?php endif; ?>

            <form method="post">
                <h2>Security & Identity</h2>
                
                <div class="info-row">
                    <label>Designation</label>
                    <?php if ($edit_mode): ?>
                        <input type="text" name="username" value="<?= htmlspecialchars($_SESSION["username"]) ?>" required>
                    <?php else: ?>
                        <span><?= htmlspecialchars($_SESSION["username"]) ?></span>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="info-row">
                        <label>First Name</label>
                        <?php if ($edit_mode): ?>
                            <input type="text" name="nome" value="<?= htmlspecialchars($_SESSION["nome"]) ?>" required>
                        <?php else: ?>
                            <span><?= htmlspecialchars($_SESSION["nome"]) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="info-row">
                        <label>Last Name</label>
                        <?php if ($edit_mode): ?>
                            <input type="text" name="cognome" value="<?= htmlspecialchars($_SESSION["cognome"]) ?>" required>
                        <?php else: ?>
                            <span><?= htmlspecialchars($_SESSION["cognome"]) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="info-row">
                    <label>Com-Link (Email)</label>
                    <?php if ($edit_mode): ?>
                        <input type="email" name="email" value="<?= htmlspecialchars($_SESSION["email"]) ?>" required>
                    <?php else: ?>
                        <span><?= htmlspecialchars($_SESSION["email"]) ?></span>
                    <?php endif; ?>
                </div>

                <?php if ($edit_mode): ?>
                    <div class="info-row">
                        <label>New Security Phrase (Optional)</label>
                        <input type="password" name="password" placeholder="Min 5 chars">
                    </div>
                <?php endif; ?>

                <div class="btn-group">
                    <?php if ($edit_mode): ?>
                        <button type="submit" name="save_profile" class="btn-auth" style="margin:0; flex:1;">Commit Changes</button>
                        <a href="profilo.php" class="logout-btn" style="text-decoration:none; text-align:center; flex:1; border-color: #444; color: #444;">Abort</a>
                    <?php else: ?>
                        <button type="submit" name="enable_edit" class="btn-auth" style="margin:0; flex:1;">Edit Profile</button>
                        <button type="submit" name="logout" class="logout-btn" style="flex:1;">Terminate Session</button>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="profile-activity reveal">
            <div class="profile-info">
                <h2>Operational History</h2>
                <p style="font-family: monospace; color: #444; font-size: 0.8rem;">Voci inviate al database centrale.</p>
                
                <ul class="entry-list" style="margin-top: 30px;">
                    <?php if (empty($voci_utente)): ?>
                        <li style="color: #222; font-family: monospace;">[ NO_RECORDS_FOUND ]</li>
                    <?php else: ?>
                        <?php foreach ($voci_utente as $voce): ?>
                            <li class="entry-item">
                                <div>
                                    <span style="font-size: 0.6rem; color: #444; display: block; font-family: monospace;"><?= $voce['tipo'] ?></span>
                                    <a href="voce.php?id=<?= $voce['id_voce'] ?>"><?= htmlspecialchars($voce['nome']) ?></a>
                                </div>
                                <span class="status-pill"><?= $voce['stato'] ?? 'ACTIVE' ?></span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </section>
    </div>

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