<?php
// login.php
session_start();
require "config.php";

date_default_timezone_set("Europe/Rome");

// Se già loggato, vai in area riservata
if (isset($_SESSION["user_id"])) {
    header("Location: profilo.php");
    exit();
}

$errore = "";
$successo = "";

// Quale tab mostrare
$tab = $_GET["tab"] ?? "login";
if (!in_array($tab, ["login", "register"], true))
    $tab = "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // -------------------------
    // REGISTRAZIONE
    // -------------------------
    if ($action === "register") {
        $username = trim($_POST["username"] ?? "");
        $nome = trim($_POST["nome"] ?? "");
        $cognome = trim($_POST["cognome"] ?? "");
        $email = trim($_POST["email"] ?? "");

        $password = $_POST["password"] ?? "";

        if ($username === "" || $email === "" || $password === "") {
            $errore = "Compila tutti i campi.";
            $tab = "register";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errore = "Email non valida.";
            $tab = "register";
        } elseif (strlen($password) < 5) {
            $errore = "La password deve avere almeno 5 caratteri.";
            $tab = "register";
        } else {
            // controllo duplicati
            $stmt = $conn->prepare("SELECT id_utente FROM utente WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errore = "Username o email già registrati.";
                $tab = "register";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $now = date("Y-m-d H:i:s");
                $stmt = $conn->prepare("INSERT INTO utente (username, nome, cognome, email, password_hash, ruolo, data_registrazione, attivo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$username, $nome, $cognome, $email, $password, "UTENTE", $now, 1]);

                $successo = "Registrazione completata! Ora puoi fare login.";
                $tab = "login";
            }
        }
    }

    // -------------------------
    // LOGIN
    // -------------------------
    //var_dump($_POST);
    if ($action === "login") {
        $userOrEmail = trim($_POST["userOrEmail"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($userOrEmail === "" || $password === "") {
            $errore = "Inserisci credenziali.";
            $tab = "login";
        } else {
            $stmt = $conn->prepare("SELECT id_utente, username, nome, cognome, email, password_hash, ruolo, data_registrazione, attivo FROM utente WHERE username = ? OR email = ?");
            $stmt->execute([$userOrEmail, $userOrEmail]);
            $user = $stmt->fetch();

            if (!$user) {
                $errore = "Utente non trovato.";
                $tab = "login";
            } else {
                if (/*password_verify($password, $user["password_hash"])*/ $password == $user["password_hash"]) {
                    $_SESSION["user_id"] = $user["id_utente"];
                    $_SESSION["username"] = $user["username"];
                    $_SESSION["nome"] = $user["nome"];
                    $_SESSION["cognome"] = $user["cognome"];
                    $_SESSION["email"] = $user["email"];
                    $_SESSION["ruolo"] = $user["ruolo"];
                    $_SESSION["data_registrazione"] = $user["data_registrazione"];
                    header("Location: profilo.php");
                    //exit();

                } else {
                    $errore = "Credenziali non corrette.";
                    $tab = "login";
                }
            }

        }
    }
}
?>
<!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <title>Login / Registrazione</title>
    <link rel="stylesheet" href="styles/nav_style.css">
    <link rel="stylesheet" href="styles/style.css">
    <style>
        
    </style>
</head>
<body>

    <img class="logo"
        src="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing"
            target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>
    <br><br><br><br>
    <h2>Accesso</h2>
    <div class="log">
        <div class="tabs">
            <a href="login.php?tab=login" class="<?= $tab === "login" ? "active" : "" ?>">Login</a>
            <a href="login.php?tab=register" class="<?= $tab === "register" ? "active" : "" ?>">Registrati</a>
        </div>

        <?php if ($errore): ?>
            <p class="err"><?= htmlspecialchars($errore) ?></p><?php endif; ?>
        <?php if ($successo): ?>
            <p class="ok"><?= htmlspecialchars($successo) ?></p><?php endif; ?>

        <?php if ($tab === "login"): ?>
            <form method="post">
                <input type="hidden" name="action" value="login">
                <label><b>Username o Email</b></label>
                <input type="text" name="userOrEmail" required>
                <label><b>Password</b></label>
                <input type="password" name="password" required>
                <button type="submit">Accedi</button>
            </form>
        <?php else: ?>
            <form method="post">
                <input type="hidden" name="action" value="register">
                <label>Username</label>
                <input type="text" name="username" required>
                <label>Nome</label>
                <input type="text" name="nome" required>
                <label>Cognome</label>
                <input type="text" name="cognome" required>
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button type="submit">Registrati</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>