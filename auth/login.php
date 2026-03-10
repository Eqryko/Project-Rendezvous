<?php
// login.php
session_start();
require "../src/components/config.php";

// timezone per coerenza con db
date_default_timezone_set("Europe/Rome");


if (isset($_SESSION["user_id"])) {                  // se utente già loggato
    header("Location: ../profilo.php");
    exit();
}

// msg feedback
$errore = "";
$successo = "";

// Determino tab attivo (login o register)
$tab = $_GET["tab"] ?? "login"; // ?? serve per dare un valore di default (login) se "tab" non è presente nell'URL
if (!in_array($tab, ["login", "register"], true))
    $tab = "login";

// Gestione form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") { // (sia login che register)
    $action = $_POST["action"] ?? "";

    // --- REGISTRAZIONE UTENTE ---
    if ($action === "register") { // registrazione

        // 1. RECUPERO I DATI DAL POST (Fondamentale, altrimenti le variabili restano nulle!)
        $username = trim($_POST["username"] ?? ""); // trim() rimuove spazi bianchi inutili, ?? serve per evitare errori se il campo non è presente
        $nome = trim($_POST["nome"] ?? "");
        $cognome = trim($_POST["cognome"] ?? "");
        $email = trim($_POST["email"] ?? "");
        $password_chiaro = $_POST["password"] ?? ""; // per chiarezza

        // 2. VALIDAZIONI DI BASE
        if ($username === "" || $email === "" || $password_chiaro === "") { // entra se uno è vuoto
            $errore = "STATUS_ERROR: Campi incompleti.";
            $tab = "register";
        } elseif (strlen($password_chiaro) < 5) {
            $errore = "STATUS_ERROR: Password troppo breve (min. 5).";
            $tab = "register";
        } else {
            // 3. CONTROLLO UNICITÀ
            $stmt = $conn->prepare("SELECT id_utente FROM utente WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]); // grazie a PDO, questi valori vengono automaticamente "sanificati" per prevenire SQL injection

            if ($stmt->fetch()) {
                $errore = "STATUS_ERROR: Credenziali già esistenti.";
                $tab = "register";
            } else {
                // 4. CRITTOGRAFIA (hash della password)
                $hashed_password = password_hash($password_chiaro, PASSWORD_DEFAULT); // bycrypt/argon2

                // 5. INSERIMENTO NEL DATABASE
                $stmt = $conn->prepare("INSERT INTO utente (username, nome, cognome, email, password_hash, ruolo, data_registrazione, attivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                // Usiamo $hashed_password invece della password in chiaro
                if ($stmt->execute([$username, $nome, $cognome, $email, $hashed_password, "UTENTE", date("Y-m-d H:i:s"), 1])) {
                    $successo = "STATUS_OK: Registrazione completata.";
                    $tab = "login";
                } else {
                    $errore = "STATUS_ERROR: Errore durante il salvataggio.";
                }
            }
        }
    }

    // --- LOGIN UTENTE ---
    if ($action === "login") { // login
        $userOrEmail = trim($_POST["userOrEmail"] ?? "");
        $password_input = $_POST["password"] ?? "";

        $stmt = $conn->prepare("SELECT * FROM utente WHERE username = ? OR email = ?");
        $stmt->execute([$userOrEmail, $userOrEmail]);
        $user = $stmt->fetch();

        // se l'utente esiste E se la password corrisponde all'hash
        if ($user && password_verify($password_input, $user["password_hash"])) {  // match hash
            // allora salva
            $_SESSION["user_id"] = $user["id_utente"];
            header("Location: ../profilo.php");
            exit();
        } else {
            $errore = "STATUS_DENIED: Credenziali errate.";
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
    <link rel="icon"
        href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">

    <link rel="stylesheet" href="../assets/styles/style.css">
    <link rel="stylesheet" href="../assets/styles/nav_style.css">
    <link rel="stylesheet" href="../assets/styles/loginStyle.css">
    <script src="../assets/scripts/scroll.js" defer></script>
</head>
<body>

    <header>
        <a href="../index.php" class="toggle-link">Home</a>
        <a href="../profilo.php" class="toggle-link">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing"
            target="_blank"> Documentazione </a>
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