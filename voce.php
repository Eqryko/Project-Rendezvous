<?php
// voce.php
// file + grande
session_start();

$id = $_GET['id'] ?? null;
require "config.php";

// 1. Recupero dati base dalla tabella 'voce'
$query = "SELECT v.nome AS nome_voce, 
                 v.tipo, 
                 v.stato, 
                 v.data_creazione, 
                 v.data_approvazione, 
                 u.username AS creatore_name, 
                 u2.username AS approvatore_name,
                 v.immagine_url AS urli
          FROM voce v
          INNER JOIN utente u ON v.creatore = u.id_utente 
          LEFT JOIN utente u2 ON v.approvatore = u2.id_utente
          WHERE v.id_voce = ?";

// Recupero liste per i menu a tendina (solo se siamo in modalità modifica)
$aziende = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'azienda'")->fetchAll(PDO::FETCH_ASSOC);
$programmi = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'programma'")->fetchAll(PDO::FETCH_ASSOC);
$vettori = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'vettore'")->fetchAll(PDO::FETCH_ASSOC);
$veicoli = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'veicolo'")->fetchAll(PDO::FETCH_ASSOC);
$lanci = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'evento'")->fetchAll(PDO::FETCH_ASSOC);

// Funzione helper per generare le opzioni del select
function generaSelect($nome_campo, $valore_attuale, $lista)
{
    echo "<select name='$nome_campo'>";
    echo "<option value=''>-- Seleziona --</option>";
    foreach ($lista as $item) {
        $selected = ($item['id_voce'] == $valore_attuale) ? "selected" : "";
        echo "<option value='{$item['id_voce']}' $selected>" . htmlspecialchars($item['nome']) . "</option>";
    }
    echo "</select>";
}

$stmt = $conn->prepare($query);
$stmt->execute([$id]);
$voce = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$voce) {
    die("Errore: Voce non trovata.");
}

// 2. Recupero dettagli specifici in base al tipo
$tipo = $voce['tipo'];
$dettagli = null;

// risoluzione relazioni
switch ($tipo) {
    case 'missione':
        $query_dettagli = "SELECT m.*, 
                             v1.nome AS nome_azienda, 
                             v2.nome AS nome_programma,
                             v3.nome AS nome_vettore,
                             v4.nome AS nome_veicolo,
                             e.data AS data_lancio,
                             e.ora AS ora_lancio,
                             e.luogo AS luogo_lancio,
                             e.pianeta AS pianeta_lancio
                      FROM missione m
                      LEFT JOIN voce v1 ON m.id_azienda = v1.id_voce
                      LEFT JOIN voce v2 ON m.id_programma = v2.id_voce
                      LEFT JOIN voce v3 ON m.id_vettore = v3.id_voce
                      LEFT JOIN voce v4 ON m.id_veicolo = v4.id_voce
                      LEFT JOIN evento e ON m.id_lancio = e.id_voce
                      WHERE m.id_voce = ?";
        break;

    case 'astronauta':
        $query_dettagli = "SELECT * FROM astronauta WHERE id_voce = ?";
        break;

    case 'veicolo':
        $query_dettagli = "SELECT v.*, a.nomeIntero AS azienda_produttrice 
                           FROM veicolo v 
                           LEFT JOIN azienda a ON v.id_azienda = a.id_voce 
                           WHERE v.id_voce = ?";
        break;

    default:
        $query_dettagli = "SELECT * FROM $tipo WHERE id_voce = ?";
        break;
}
// Controllo se è stata richiesta la modalità modifica
$edit_mode = isset($_POST['enable_edit']);
if (!isset($_SESSION["user_id"]) && $edit_mode) {
    header("Location: login.php");
    exit();
}

// Logica di Salvataggio -> se salvata la voce dopo modifica
if (isset($_POST['save_voce'])) {
    try {
        $conn->beginTransaction();
        $id_voce = $_GET['id'];

        // 1. Aggiornamento Tabella Voce
        $stmt_v = $conn->prepare("UPDATE voce SET nome = ?, immagine_url = ? WHERE id_voce = ?"); // 'immagine_url' è il nome della colonna nel DB, urli è il nome del campo nel form
        $stmt_v->execute([$_POST['nome_voce'], $_POST['immagine_url'], $id_voce]);

        // 2. Aggiornamento Tabella Specifica
        $campi_esclusi = [
            'save_voce',
            'nome_voce',
            'immagine_url',
            'data_lancio',
            'ora_lancio',
            'luogo_lancio',
            'pianeta_lancio' // Escludiamo i campi dell'"evento" dal ciclo automatico, così da gestirli separatamente dopo
        ];

        $update_parts = []; // array che conterrà le parti della query di update
        $params = []; // array che conterrà i valori da bindare alla query di update, così da evitare problemi di SQL injection e formattazione

        foreach ($_POST as $key => $value) {
            if (!in_array($key, $campi_esclusi) && strpos($key, 'nome_') !== 0 && strpos($key, 'azienda_') !== 0) {
                $update_parts[] = "$key = ?";
                $params[] = ($value === '') ? null : $value;
            }
        }

        if (!empty($update_parts)) {
            $sql_spec = "UPDATE $tipo SET " . implode(', ', $update_parts) . " WHERE id_voce = ?";
            $params[] = $id_voce;
            $stmt_s = $conn->prepare($sql_spec);
            $stmt_s->execute($params);
        }

        // 3. Aggiornamento Tabella Evento (se è una missione)
        if ($tipo === 'missione' && !empty($dettagli['id_lancio'])) {
            $stmt_e = $conn->prepare("UPDATE evento SET data = ?, ora = ?, luogo = ?, pianeta = ? WHERE id_voce = ?");
            $stmt_e->execute([
                $_POST['data_lancio'] ?? null,
                $_POST['ora_lancio'] ?? null,
                $_POST['luogo_lancio'] ?? null,
                $_POST['pianeta_lancio'] ?? null,
                $dettagli['id_lancio']
            ]);
        }

        $conn->commit();
        header("Location: voce.php?id=$id_voce&msg=success");
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        $errore = "Errore durante il salvataggio: " . $e->getMessage();
        $edit_mode = true;
    }
}
$stmt_det = $conn->prepare($query_dettagli);
$stmt_det->execute([$id]);
$dettagli = $stmt_det->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dettaglio <?php echo ucfirst($tipo) ?>: <?php echo htmlspecialchars($voce['nome_voce']); ?></title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
</head>
<body>

    <img class="logo"
        src="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing"
            target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="container">
        <form method="post" style="border: 0px solid #ccc; margin-top: 0px;">
            <?php if ($edit_mode): ?>
                <input type="text" name="nome_voce" value="<?= htmlspecialchars($voce['nome_voce']) ?>" class="edit-title">
                <label>URL Immagine:</label>
                <input type="text" name="immagine_url" value="<?= htmlspecialchars($voce['urli']) ?>"
                    placeholder="Inserisci URL immagine..." class="edit-title">
            <?php else: ?>
                <h1><?= htmlspecialchars($voce['nome_voce']) ?></h1>
                <?php if (!$edit_mode && !empty($voce['urli'])): ?>
                    <div style="text-align: center; margin: 20px 0;">
                        <img src="<?= htmlspecialchars($voce['urli']) ?>" style="max-width: 50%; border-radius: 10px;">
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="details-section">
                <div class="details-header"
                    style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 2px solid #11e4ff; padding-bottom: 10px;">
                    <h3 style="margin: 0;">Dettagli Specifici</h3>
                    <span class="type-badge <?= htmlspecialchars($voce['tipo']) ?>"
                        style="background: #11e4ff; color: #000; padding: 5px 15px; border-radius: 20px; font-weight: bold;">
                        <?= strtoupper(htmlspecialchars($voce['tipo'])) ?>
                    </span>
                </div>

                <div class="details-grid">
                    <?php foreach ($dettagli as $chiave => $valore):
                        // 1. Campi tecnici da non mostrare mai
                        if ($chiave == 'id_voce' || $chiave == 'lancio' || $chiave == 'id_lancio')
                            continue;

                        // 2. Filtri per la Modalità VISUALIZZAZIONE
                        if (!$edit_mode) {
                            $ids_nascosti = ['id_azienda', 'id_programma', 'id_vettore', 'id_veicolo'];
                            if (in_array($chiave, $ids_nascosti))
                                continue;

                            if (isset($dettagli['nome_' . $chiave]))
                                continue;
                            if (isset($dettagli['azienda_produttrice']) && $chiave == 'azienda')
                                continue;
                        }

                        // 3. Filtri per la Modalità MODIFICA
                        if ($edit_mode) {
                            // Qui NON mettiamo data_lancio, luogo_lancio etc, così appariranno come input modificabili
                            $campi_readonly = ['nome_azienda', 'nome_programma', 'nome_vettore', 'nome_veicolo', 'azienda_produttrice'];
                            if (in_array($chiave, $campi_readonly))
                                continue;
                        }
                        ?>
                        <div class="profile-item">
                            <label><?= ucwords(str_replace(['id_', '_'], ['', ' '], $chiave)) ?>:</label>

                            <?php if ($edit_mode): ?>
                                <?php
                                switch ($chiave) {
                                    case 'id_azienda':
                                        generaSelect($chiave, $valore, $aziende);
                                        break;
                                    case 'id_programma':
                                        generaSelect($chiave, $valore, $programmi);
                                        break;
                                    case 'id_vettore':
                                        generaSelect($chiave, $valore, $vettori);
                                        break;
                                    case 'id_veicolo':
                                        generaSelect($chiave, $valore, $veicoli);
                                        break;
                                    // id_lancio rimosso: non vogliamo più il select del lancio
                        
                                    // Gestione specifica per tipi di input corretti
                                    case 'data_lancio': ?>
                                        <input type="date" name="data_lancio" value="<?= htmlspecialchars($valore ?? '') ?>">
                                        <?php break;
                                    case 'ora_lancio': ?>
                                        <input type="time" name="ora_lancio" value="<?= htmlspecialchars($valore ?? '') ?>">
                                        <?php break;
                                    default: ?>
                                        <input type="text" name="<?= $chiave ?>" value="<?= htmlspecialchars($valore ?? '') ?>">
                                        <?php break;
                                } ?>
                            <?php else: ?>
                                <span><?= htmlspecialchars($valore ?? 'N/D') ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="actions">
                <?php if ($edit_mode): ?>
                    <button type="submit" name="save_voce" class="btn-green">Salva Modifiche</button>
                    <a href="voce.php?id=<?= $id ?>" class="btn-gray">Annulla</a>
                <?php else: ?>
                    <button type="submit" name="enable_edit" class="btn-blue"
                        style="background-color: #11e4ff; color: black; font-weight: bold;">✎ Modifica Voce</button>
                    <a href="index.php">Torna alla Home</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($voce['approvatore_name']): ?>
            <div class="approval-info" style="margin-top: 20px; font-size: 0.8em; color: gray;">
                Creata da
                <?php echo htmlspecialchars($voce["creatore_name"]); ?> il
                <?php echo $voce["data_creazione"]; ?>
                <div class="profile-item">
                    Stato:</label>
                    <span style="color:black;" class="status-badge">
                        <?php echo htmlspecialchars($voce["stato"]); ?>
                    </span>
                    <br>Approvata da <?php echo htmlspecialchars($voce["approvatore_name"]); ?> il
                    <?php echo $voce["data_approvazione"]; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <br>
</body>
</html>