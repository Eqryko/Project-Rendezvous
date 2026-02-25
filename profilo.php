<?php
// profilo.php
session_start();
require "config.php";

// Se non loggato, torna a login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$messaggio = "";
$errore = "";
$edit_mode = isset($_POST['enable_edit']);

// logout
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Salvataggio modifiche profilo
if (isset($_POST['save_profile'])) {
    $user_id = $_SESSION["user_id"];
    $nuovo_username = trim($_POST['username']);
    $nuovo_nome = trim($_POST['nome']);
    $nuovo_cognome = trim($_POST['cognome']);
    $nuova_email = trim($_POST['email']);
    $nuova_pass = $_POST['password'];

    // 1. Validazione campi obbligatori
    if (empty($nuovo_username) || empty($nuovo_nome) || empty($nuovo_cognome) || empty($nuova_email)) {
        $errore = "Tutti i campi (tranne la password) sono obbligatori.";
        $edit_mode = true;
    }
    // 2. Controllo lunghezza password (se inserita)
    elseif (!empty($nuova_pass) && strlen($nuova_pass) < 5) {
        $errore = "La nuova password deve contenere almeno 5 caratteri.";
        $edit_mode = true;
    } else {
        try {
            // 3. Controllo duplicati Username
            $stmt_check = $conn->prepare("SELECT id_utente FROM utente WHERE username = ? AND id_utente != ?");
            $stmt_check->execute([$nuovo_username, $user_id]);

            if ($stmt_check->fetch()) {
                $errore = "Lo username '$nuovo_username' è già occupato.";
                $edit_mode = true;
            } else {
                // 4. Esecuzione UPDATE
                if (!empty($nuova_pass)) {
                    $hash_pass = password_hash($nuova_pass, PASSWORD_DEFAULT);
                    $sql = "UPDATE utente SET username = ?, nome = ?, cognome = ?, email = ?, password_hash = ? WHERE id_utente = ?";
                    $params = [$nuovo_username, $nuovo_nome, $nuovo_cognome, $nuova_email, $nuova_pass, $user_id];
                } else {
                    $sql = "UPDATE utente SET username = ?, nome = ?, cognome = ?, email = ? WHERE id_utente = ?";
                    $params = [$nuovo_username, $nuovo_nome, $nuovo_cognome, $nuova_email, $user_id];
                }

                $stmt = $conn->prepare($sql);
                $stmt->execute($params);

                // Aggiornamento Sessione
                $_SESSION["username"] = $nuovo_username;
                $_SESSION["nome"] = $nuovo_nome;
                $_SESSION["cognome"] = $nuovo_cognome;
                $_SESSION["email"] = $nuova_email;

                $messaggio = "Profilo aggiornato con successo!";
                $edit_mode = false;
            }
        } catch (PDOException $e) {
            $errore = "Errore: " . $e->getMessage();
            $edit_mode = true;
        }
    }

    
}

// Recupero voci create dall'utente
$voci_utente = [];

try {
    $stmt_voci = $conn->prepare("SELECT id_voce, nome FROM voce WHERE creatore = ? ORDER BY id_voce DESC");
    $stmt_voci->execute([$_SESSION["user_id"]]);
    $voci_utente = $stmt_voci->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errore = "Errore nel recupero delle voci: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo Utente</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
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
    
    <div class="container">
        <h1>Profilo Utente</h1>

        <?php if ($messaggio): ?>
            <div class="msg"><?= htmlspecialchars($messaggio) ?></div> <?php endif; ?>
        <?php if ($errore): ?>
            <div class="err"><?= htmlspecialchars($errore) ?></div> <?php endif; ?>

        <form method="post">

            <div class="profile-item">
                <label>Username:</label>
                <?php if ($edit_mode): ?>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION["username"]); ?>"
                        required>
                <?php else: ?>
                    <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <?php endif; ?>
            </div>

            <div class="profile-item">
                <label>Nome:</label>
                <?php if ($edit_mode): ?>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($_SESSION["nome"]); ?>" required>
                <?php else: ?>
                    <span><?php echo htmlspecialchars($_SESSION["nome"]); ?></span>
                <?php endif; ?>
            </div>

            <div class="profile-item">
                <label>Cognome:</label>
                <?php if ($edit_mode): ?>
                    <input type="text" name="cognome" value="<?php echo htmlspecialchars($_SESSION["cognome"]); ?>"
                        required>
                <?php else: ?>
                    <span><?php echo htmlspecialchars($_SESSION["cognome"]); ?></span>
                <?php endif; ?>
            </div>

            <div class="profile-item">
                <label>Email:</label>
                <?php if ($edit_mode): ?>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION["email"]); ?>" required>
                <?php else: ?>
                    <span><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
                <?php endif; ?>
            </div>

            


            <?php if ($edit_mode): ?>
                <div class="profile-item">
                    <label>Cambia Password:</label>
                    <input type="password" name="password" placeholder="Minimo 5 caratteri" minlength="5">
                    <p style="font-size: 0.8em; color: #6b6a6a;">Lascia vuoto per mantenere la password attuale.</p>
                </div>
            <?php endif; ?>

            <div class="profile-item">
                <label>Ruolo:</label>
                <span><?php echo htmlspecialchars($_SESSION["ruolo"]); ?></span>
            </div>

            <div class="profile-item">
                <label>Data Registrazione:</label>
                <span><?php echo htmlspecialchars($_SESSION["data_registrazione"]); ?></span>
            </div>
<div class="actions">
                <?php if ($edit_mode): ?>
                    <button type="submit" name="save_profile" class="btn-green">Salva Modifiche</button>
                    <button type="submit" name="cancel_edit" class="btn-gray">Annulla</button>
                <?php else: ?>
                    <button type="submit" name="enable_edit" class="btn-blue">Modifica Profilo</button>
                    <button type="submit" name="logout" class="logout">Logout</button>
                <?php endif; ?>
            </div>

<br><br><br>
<hr>
    <div class="user-posts">
    <h2>Le tue voci create</h2>

    <?php if (empty($voci_utente)): ?>
        <p>Non hai ancora creato nessuna voce.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($voci_utente as $voce): ?>
                <li>
                    <a href="voce.php?id=<?php echo $voce['id_voce']; ?>">
                        <?php echo htmlspecialchars($voce['nome']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
<hr><br><br>
                

            

        </form>
    </div>
</body>
</html>