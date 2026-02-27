<?php
// login.php
session_start();
require "../assets/config.php";

// Imposto timezone per coerenza con database
date_default_timezone_set("Europe/Rome");

// Se utente già loggato, reindirizzo a profilo
if (isset($_SESSION["user_id"])) {
    header("Location: ../profilo.php");
    exit();
}
// Variabili per messaggi di feedback
$errore = "";
$successo = "";

// Determino tab attivo (login o register)
$tab = $_GET["tab"] ?? "login";
if (!in_array($tab, ["login", "register"], true)) $tab = "login";

// Gestione form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // REGISTRAZIONE UTENTE
    if ($action === "register") {
        $username = trim($_POST["username"] ?? "");
        $nome = trim($_POST["nome"] ?? "");
        $cognome = trim($_POST["cognome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        // Validazioni di base
        if ($username === "" || $email === "" || $password === "") {
            $errore = "STATUS_ERROR: Campi incompleti.";
            $tab = "register";
        } elseif (strlen($password) < 5) {
            $errore = "STATUS_ERROR: Password troppo breve (min. 5).";
            $tab = "register";
        } else {
            // Controllo unicità username/email
            $stmt = $conn->prepare("SELECT id_utente FROM utente WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errore = "STATUS_ERROR: Credenziali già esistenti.";
                $tab = "register";
            } else {
                // da usare password_hash()
                $stmt = $conn->prepare("INSERT INTO utente (username, nome, cognome, email, password_hash, ruolo, data_registrazione, attivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $nome, $cognome, $email, $password, "UTENTE", date("Y-m-d H:i:s"), 1]);

                $successo = "STATUS_OK: Registrazione completata.";
                $tab = "login";
            }
        }
    }

    // LOGIN UTENTE
    if ($action === "login") {
        $userOrEmail = trim($_POST["userOrEmail"] ?? "");
        $password = $_POST["password"] ?? "";

        // Validazioni di base
        $stmt = $conn->prepare("SELECT * FROM utente WHERE username = ? OR email = ?");
        $stmt->execute([$userOrEmail, $userOrEmail]);
        $user = $stmt->fetch();

        // Controllo credenziali
        if ($user && $password == $user["password_hash"]) {
            // SALVATAGGIO COMPLETO IN SESSIONE
            $_SESSION["user_id"]   = $user["id_utente"];
            $_SESSION["username"]  = $user["username"];
            $_SESSION["nome"]      = $user["nome"];
            $_SESSION["cognome"]   = $user["cognome"];
            $_SESSION["email"]     = $user["email"];
            $_SESSION["ruolo"]     = $user["ruolo"];
            $_SESSION["data_registrazione"] = $user["data_registrazione"];
            
            // Reindirizzo a profilo dopo login
            header("Location: ../profilo.php");
            exit();
        } else {
            $errore = "STATUS_DENIED: Credenziali errate.";
            $tab = "login";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Gate — Rendezvous</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    
    <link rel="stylesheet" href="../styles/style.css">
    <link rel="stylesheet" href="../styles/nav_style.css">
    <link rel="stylesheet" href="../styles/loginStyle.css">
    <script src="../assets/scroll.js" defer></script>
</head>
<body>

    <header>
        <a href="../index.php" class="toggle-link">Home</a>
        <a href="../profilo.php" class="toggle-link">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="auth-container">
        <div class="reveal">
            <h1 style="font-size: 4rem; line-height: 0.8; margin-bottom: 10px;">AUTH</h1>
            <p style="color: #444; font-family: monospace; margin-bottom: 40px;">SECURE_HANDSHAKE_v4.1</p>
        </div>

        <div class="auth-tabs reveal">
            <a href="login.php?tab=login" class="<?= $tab === "login" ? "active" : "" ?>">Login</a>
            <a href="login.php?tab=register" class="<?= $tab === "register" ? "active" : "" ?>">Registrati</a>
        </div>

        <?php if ($errore): ?>
            <div class="msg err reveal"><?= $errore ?></div>
        <?php endif; ?>
        <?php if ($successo): ?>
            <div class="msg ok reveal"><?= $successo ?></div>
        <?php endif; ?>

        <form method="post" class="reveal">
            <?php if ($tab === "login"): ?>
                <input type="hidden" name="action" value="login">
                <div class="field">
                    <label>Identity (Username or Email)</label>
                    <input type="text" name="userOrEmail" required>
                </div>
                <div class="field">
                    <label>Security Phrase (Password)</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-auth">Initialize Session</button>
            <?php else: ?>
                <input type="hidden" name="action" value="register">
                <div class="field">
                    <label>Designation (Username)</label>
                    <input type="text" name="username" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="field">
                        <label>Given Name</label>
                        <input type="text" name="nome" required>
                    </div>
                    <div class="field">
                        <label>Surname</label>
                        <input type="text" name="cognome" required>
                    </div>
                </div>
                <div class="field">
                    <label>Com-Link (Email)</label>
                    <input type="email" name="email" required>
                </div>
                <div class="field">
                    <label>New Security Phrase</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-auth">Register Identity</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>