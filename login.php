<?php
session_start();
require "config.php";

// Se già loggato, vai in area riservata
if (isset($_SESSION["user_id"])) {
    header("Location: profilo.php");
    exit();
}

$errore = "";
$successo = "";

// Quale tab mostrare
$tab = $_GET["tab"] ?? "login";
if (!in_array($tab, ["login", "register"], true)) $tab = "login";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    // -------------------------
    // REGISTRAZIONE
    // -------------------------
    if ($action === "register") {
        $username = trim($_POST["username"] ?? "");
        $email    = trim($_POST["email"] ?? "");
        $password = $_POST["password"] ?? "";

        if ($username === "" || $email === "" || $password === "") {
            $errore = "Compila tutti i campi.";
            $tab = "register";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errore = "Email non valida.";
            $tab = "register";
        } elseif (strlen($password) < 6) {
            $errore = "La password deve avere almeno 6 caratteri.";
            $tab = "register";
        } else {
            // controllo duplicati
            $stmt = $conn->prepare("SELECT id FROM utente WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errore = "Username o email già registrati.";
                $tab = "register";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("INSERT INTO utente (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->execute([$username, $email, $hash]);

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
        $password    = $_POST["password"] ?? "";

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
    if (/*password_verify($password, $user["password_hash"])*/$password == $user["password_hash"]) {
        $_SESSION["user_id"] = $user["id_utente"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["nome"] = $user["nome"];
        $_SESSION["cognome"] = $user["cognome"];
        $_SESSION["email"] = $user["email"];
        $_SESSION["ruolo"] = $user["ruolo"];
        $_SESSION["data_registrazione"] = $user["data_registrazione"];
        echo "Login riuscito! E' possibile chiudere questa scheda";
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
  <style>
    body{font-family:Arial; max-width:480px; margin:40px auto;}
    .tabs a{padding:10px 14px; display:inline-block; text-decoration:none; border:1px solid #ccc; margin-right:6px;}
    .active{background:#eee;}
    form{border:1px solid #ccc; padding:16px; margin-top:12px;}
    input{width:100%; padding:10px; margin:8px 0;}
    button{padding:10px 14px;}
    .err{color:#b00020;}
    .ok{color:green;}
  </style>
</head>
<body>

<h2>Accesso</h2>

<div class="tabs">
  <a href="auth.php?tab=login" class="<?= $tab==="login" ? "active":"" ?>">Login</a>
  <a href="auth.php?tab=register" class="<?= $tab==="register" ? "active":"" ?>">Registrati</a>
</div>

<?php if($errore): ?><p class="err"><?= htmlspecialchars($errore) ?></p><?php endif; ?>
<?php if($successo): ?><p class="ok"><?= htmlspecialchars($successo) ?></p><?php endif; ?>

<?php if($tab === "login"): ?>
  <form method="post">
    <input type="hidden" name="action" value="login">
    <label>Username o Email</label>
    <input type="text" name="userOrEmail" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Accedi</button>
  </form>
<?php else: ?>
  <form method="post">
    <input type="hidden" name="action" value="register">
    <label>Username</label>
    <input type="text" name="username" required>
    <label>Email</label>
    <input type="email" name="email" required>
    <label>Password</label>
    <input type="password" name="password" required>
    <button type="submit">Registrati</button>
  </form>
<?php endif; ?>

</body>
</html>
