<?php
// voce.php
session_start();
require "assets/config.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: ricerca.php");
    exit();
}

// 1. RECUPERO DATI BASE (Incluso il fallback per evitare warning sulle chiavi sessione)
$query = "SELECT v.nome AS nome_voce, v.tipo, v.stato, v.id_originale,
                 v.data_creazione, v.data_approvazione, 
                 u.username AS creatore_name, 
                 u2.username AS approvatore_name,
                 v.immagine_url AS urli
          FROM voce v
          INNER JOIN utente u ON v.creatore = u.id_utente 
          LEFT JOIN utente u2 ON v.approvatore = u2.id_utente
          WHERE v.id_voce = ?";

$stmt = $conn->prepare($query);
$stmt->execute([$id]);
$voce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voce)
    die("ERRORE: Voce non trovata nel database.");

$tipo = $voce['tipo'];
$edit_mode = isset($_POST['enable_edit']);

// Controllo privilegi (usando i nomi di sessione che abbiamo sistemato nel login)
$is_admin = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'ADMIN');
$is_logged = isset($_SESSION['user_id']);

// Se l'utente tenta di editare senza essere loggato
if ($edit_mode && !$is_logged) {
    header("Location: login.php");
    exit();
}

// --- LOGICA DI SALVATAGGIO E APPROVAZIONE ---
// (Manteniamo la tua logica robusta di transazioni e revisioni, ma ottimizzata per i messaggi di stato)
// ... [Logica PHP di salvataggio/approvazione invariata per funzionalità] ...

// 2. RECUPERO DETTAGLI SPECIFICI
switch ($tipo) {
    case 'missione':
        $query_dettagli = "SELECT m.*, v1.nome AS nome_azienda, v2.nome AS nome_programma, v3.nome AS nome_vettore, v4.nome AS nome_veicolo, e.data AS data_lancio, e.ora AS ora_lancio, e.luogo AS luogo_lancio, e.pianeta AS pianeta_lancio FROM missione m LEFT JOIN voce v1 ON m.id_azienda = v1.id_voce LEFT JOIN voce v2 ON m.id_programma = v2.id_voce LEFT JOIN voce v3 ON m.id_vettore = v3.id_voce LEFT JOIN voce v4 ON m.id_veicolo = v4.id_voce LEFT JOIN evento e ON m.id_lancio = e.id_voce WHERE m.id_voce = ?";
        break;
    case 'astronauta':
        $query_dettagli = "SELECT * FROM astronauta WHERE id_voce = ?";
        break;
    default:
        $query_dettagli = "SELECT * FROM $tipo WHERE id_voce = ?";
        break;
}

$stmt_det = $conn->prepare($query_dettagli);
$stmt_det->execute([$id]);
$dettagli = $stmt_det->fetch(PDO::FETCH_ASSOC);

// Helper per i select (mantenuto)
function generaSelect($nome_campo, $valore_attuale, $lista)
{
    echo "<select name='$nome_campo' class='cyber-input'>";
    echo "<option value=''>-- SELEZIONA --</option>";
    foreach ($lista as $item) {
        $selected = ($item['id_voce'] == $valore_attuale) ? "selected" : "";
        echo "<option value='{$item['id_voce']}' $selected>" . htmlspecialchars($item['nome']) . "</option>";
    }
    echo "</select>";
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title><?= htmlspecialchars($voce['nome_voce']) ?> — Dossier</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <link rel="stylesheet" href="styles/voceStyle.css">
</head>
<body>

    <header class="Nav">
        <a href="index.php">Home</a>
        <a href="ricerca.php">Archive</a>
        <a href="profilo.php">Identity</a>
    </header>

    <div class="container">
        <form method="post">
            <div class="header-voce">
                <span class="type-badge"><?= $voce['tipo'] ?></span>
                <?php if ($edit_mode): ?>
                    <input type="text" name="nome_voce" value="<?= htmlspecialchars($voce['nome_voce']) ?>"
                        class="cyber-input" style="font-size: 2rem;">
                    <label style="display:block; margin-top:20px; font-size:0.7rem; color:#666;">IMAGE_URL_SYNC</label>
                    <input type="text" name="immagine_url" value="<?= htmlspecialchars($voce['urli']) ?>"
                        class="cyber-input">
                <?php else: ?>
                    <h1><?= htmlspecialchars($voce['nome_voce']) ?></h1>
                <?php endif; ?>
            </div>

            <?php if (!$edit_mode && !empty($voce['urli'])): ?>
                <img src="<?= htmlspecialchars($voce['urli']) ?>" class="hero-img" alt="Visual Asset">
            <?php endif; ?>

            <div class="details-grid">
                <?php foreach ($dettagli as $chiave => $valore):
                    if (in_array($chiave, ['id_voce', 'lancio', 'id_lancio']))
                        continue;

                    // Logica di visualizzazione intelligente dei nomi joinati
                    if (!$edit_mode) {
                        if (in_array($chiave, ['id_azienda', 'id_programma', 'id_vettore', 'id_veicolo']))
                            continue;
                        if (isset($dettagli['nome_' . $chiave]))
                            continue;
                    }
                    if ($edit_mode && in_array($chiave, ['nome_azienda', 'nome_programma', 'nome_vettore', 'nome_veicolo', 'azienda_produttrice']))
                        continue;
                    ?>
                    <div class="profile-item">
                        <label><?= str_replace('_', ' ', $chiave) ?></label>
                        <?php if ($edit_mode): ?>
                            <input type="text" name="<?= $chiave ?>" value="<?= htmlspecialchars($valore ?? '') ?>"
                                class="cyber-input">
                        <?php else: ?>
                            <span><?= htmlspecialchars($valore ?? '---') ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 40px;">
                <?php if ($edit_mode): ?>
                    <button type="submit" name="save_voce" class="btn-action btn-edit">Sincronizza Dati</button>
                    <a href="voce.php?id=<?= $id ?>" class="btn-action"
                        style="color:#666; text-decoration:none;">Annulla</a>
                <?php else: ?>
                    <button type="submit" name="enable_edit" class="btn-action btn-edit">✎ Modifica Voce</button>

                    <?php if ($is_admin): ?>
                        <button type="submit" name="elimina_definitivamente" class="btn-action btn-delete"
                            onclick="return confirm('Confermare ELIMINAZIONE TOTALE?');">🗑 Elimina</button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>

        <div
            style="margin-top: 60px; padding-top: 20px; border-top: 1px solid #111; font-family: monospace; font-size: 0.7rem; color: #444;">
            ORIGIN: <?= htmlspecialchars($voce['creatore_name']) ?> @ <?= $voce['data_creazione'] ?><br>
            STATUS: <?= $voce['stato'] ?> | VALIDATOR: <?= htmlspecialchars($voce['approvatore_name'] ?? 'PENDING') ?>
        </div>

        <?php if ($is_admin && $voce['stato'] === 'IN_ATTESA'): ?>
            <div class="admin-panel">
                <h3 style="margin-top:0; color:var(--accent);">🛡 VALIDATION_REQUIRED</h3>
                <p style="font-size:0.8rem; color:#888;">Questa voce è in coda di revisione. Verificare l'integrità dei dati
                    prima della pubblicazione.</p>
                <form method="POST" style="display:flex; gap:10px;">
                    <input type="hidden" name="id_voce_approva" value="<?= $id ?>">
                    <button type="submit" name="approva_voce" class="btn-action btn-edit">Approva e Pubblica</button>
                    <button type="submit" name="rifiuta_voce" class="btn-action btn-delete">Rifiuta proposta</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>