<?php
// crea_voce.php
session_start();
require "assets/config.php";

// Protezione: solo utenti loggati
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // O un redirect alla home
    exit("Accesso negato.");
}

// Recupero elenchi per i menu a tendina
$aziende = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'azienda'")->fetchAll(PDO::FETCH_ASSOC);
$programmi = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'programma'")->fetchAll(PDO::FETCH_ASSOC);
$vettori = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'vettore'")->fetchAll(PDO::FETCH_ASSOC);
$veicoli = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'veicolo'")->fetchAll(PDO::FETCH_ASSOC);

// Funzione di utilità per convertire stringhe vuote in NULL
function emptyToNull($value) {
    return (trim($value) === '') ? null : $value;
}

// Gestione del form (Logica invariata per sicurezza)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $id_creatore = $_SESSION['user_id'];
    $immagine_url = !empty($_POST['immagine_url']) ? $_POST['immagine_url'] : null;

    // Inserimento voce principale e dettagli specifici in transazione
    try {
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO voce (nome, tipo, creatore, stato, immagine_url) VALUES (?, ?, ?, 'IN_ATTESA', ?)");
        $stmt->execute([$nome, $tipo, $id_creatore, $immagine_url]);
        $id_voce = $conn->lastInsertId();

        // Inserimento dettagli specifici in base al tipo di voce
        switch ($tipo) {
            case 'astronauta':
                $sql = "INSERT INTO astronauta (id_voce, nome, cognome, nazione, data_nascita, data_morte) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$id_voce, emptyToNull($_POST['astro_nome']), emptyToNull($_POST['astro_cognome']), emptyToNull($_POST['astro_nazione']), emptyToNull($_POST['astro_nascita']), emptyToNull($_POST['astro_morte'])];
                break;
            case 'azienda':
                $sql = "INSERT INTO azienda (id_voce, nomeIntero, nazione, sede, tipo) VALUES (?, ?, ?, ?, ?)";
                $params = [$id_voce, emptyToNull($_POST['az_nome']), emptyToNull($_POST['az_nazione']), emptyToNull($_POST['az_sede']), emptyToNull($_POST['az_tipo'])];
                break;
            case 'missione':
                $stmt_ev = $conn->prepare("INSERT INTO evento (nome, data, ora, luogo, pianeta) VALUES (?, ?, ?, ?, ?)");
                $stmt_ev->execute(["Lancio di " . $nome, emptyToNull($_POST['miss_data_lancio']), emptyToNull($_POST['miss_ora_lancio']), emptyToNull($_POST['miss_luogo_lancio']), emptyToNull($_POST['miss_pianeta_lancio'] ?? 'Terra')]);
                $id_lancio_generato = $conn->lastInsertId();

                $sql = "INSERT INTO missione (id_voce, cospar_id, nome, tipo, destinazione, esito, id_azienda, id_programma, id_vettore, id_veicolo, id_lancio) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$id_voce, emptyToNull($_POST['miss_cospar']), $nome, emptyToNull($_POST['miss_tipo']), emptyToNull($_POST['miss_dest']), emptyToNull($_POST['miss_esito']), emptyToNull($_POST['miss_azienda']), emptyToNull($_POST['miss_programma']), emptyToNull($_POST['miss_vettore']), emptyToNull($_POST['miss_veicolo']), $id_lancio_generato];
                break;
        }

        // Esecuzione inserimento dettagli specifici
        if (isset($sql)) {
            $stmt_det = $conn->prepare($sql);
            $stmt_det->execute($params);
        }
        // Se tutto va bene, commit della transazione
        $conn->commit();
        header("Location: voce.php?id=" . $id_voce . "&msg=created");
        exit();
    } catch (Exception $e) {
        // In caso di errore, rollback e mostra messaggio
        if ($conn->inTransaction()) $conn->rollBack();
        $errore_creazione = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>New Entry — Rendezvous</title>
    <<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <link rel="stylesheet" href="styles/creaVoceStyle.css">
    <script src="assets/scroll.js" defer></script>
    <script src="assets/showFields.js" defer></script>
</head>
<body>

    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="Main">
        <div class="reveal">
            <h1 style="font-size: clamp(3rem, 10vw, 6rem); text-align:left;">Create<br>New Entry</h1>
            <p style="color: var(--accent); font-family: monospace; letter-spacing: 0.3em;">DATA_UPLOAD_UNIT_v2.0</p>
        </div>

        <?php if (isset($errore_creazione)): ?>
            <div class="reveal" style="color: #ff4d4d; border: 1px solid #ff4d4d; padding: 20px; margin: 20px 0; font-family: monospace;">
                [ERROR]: <?= htmlspecialchars($errore_creazione) ?>
            </div>
        <?php endif; ?>

        <form action="crea_voce.php" method="POST" class="form-section">
            
            <div class="field-group reveal">
                <label>Designazione Identificativa (Nome)</label>
                <input type="text" name="nome" required placeholder="es. VOYAGER 1">
            </div>

            <div class="field-group reveal">
                <label>Classificazione Entità</label>
                <select name="tipo" id="tipoSelect" required onchange="mostraCampi()">
                    <option value="">-- SELEZIONA --</option>
                    <option value="astronauta">ASTRONAUTA</option>
                    <option value="azienda">AZIENDA / AGENZIA</option>
                    <option value="missione">MISSIONE SPAZIALE</option>
                </select>
            </div>

            <div class="field-group reveal">
                <label>Asset Immagine (URL)</label>
                <input type="url" name="immagine_url" placeholder="https://path-to-image.jpg">
            </div>

            <div id="fields_astronauta" class="dynamic-fields">
                <h3 style="color: var(--accent); text-transform: uppercase;">Dettagli Personale</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div class="field-group">
                        <label>Nome</label>
                        <input type="text" name="astro_nome">
                    </div>
                    <div class="field-group">
                        <label>Cognome</label>
                        <input type="text" name="astro_cognome">
                    </div>
                </div>
                <div class="field-group">
                    <label>Nazione d'appartenenza</label>
                    <input type="text" name="astro_nazione">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div class="field-group">
                        <label>Data di Nascita</label>
                        <input type="date" name="astro_nascita">
                    </div>
                    <div class="field-group">
                        <label>Data Decesso (opzionale)</label>
                        <input type="date" name="astro_morte">
                    </div>
                </div>
            </div>

            <div id="fields_azienda" class="dynamic-fields">
                <h3 style="color: var(--accent); text-transform: uppercase;">Profilo Corporate/Gov</h3>
                <div class="field-group">
                    <label>Nome Legale Completo</label>
                    <input type="text" name="az_nome">
                </div>
                <div class="field-group">
                    <label>Sede Centrale</label>
                    <input type="text" name="az_sede">
                </div>
                <div class="field-group">
                    <label>Tipo Organizzazione</label>
                    <input type="text" name="az_tipo" placeholder="Es. Agenzia Governativa">
                </div>
            </div>

            <div id="fields_missione" class="dynamic-fields">
                <h3 style="color: var(--accent); text-transform: uppercase;">Parametri di Missione</h3>
                <div class="field-group">
                    <label>COSPAR ID</label>
                    <input type="text" name="miss_cospar" placeholder="YYYY-NNNA">
                </div>
                
                <div class="lancio-box">
                    <label style="color: var(--accent);">[ Launch Event Configuration ]</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                        <div>
                            <label>Data Lancio</label>
                            <input type="date" name="miss_data_lancio">
                        </div>
                        <div>
                            <label>Ora (UTC)</label>
                            <input type="time" name="miss_ora_lancio">
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <label>Spazioporto / Pad</label>
                        <input type="text" name="miss_luogo_lancio">
                    </div>
                </div>

                <div class="field-group">
                    <label>Esito Missione</label>
                    <select name="miss_esito">
                        <option value="Successo">SUCCESSO</option>
                        <option value="Fallimento">FALLIMENTO</option>
                        <option value="Parziale">PARZIALE</option>
                    </select>
                </div>

                <div class="field-group">
                    <label>Azienda Responsabile</label>
                    <select name="miss_azienda">
                        <option value="">-- SELEZIONA AZIENDA --</option>
                        <?php foreach ($aziende as $az): ?>
                            <option value="<?= $az['id_voce'] ?>"><?= htmlspecialchars($az['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <p style="font-size: 0.7rem; color: #444;">* Altri parametri (Vettore/Veicolo) possono essere collegati dopo la creazione.</p>
            </div>

            <button type="submit" class="submit-btn reveal">Commit Entry to Database</button>
        </form>
    </div>

    <footer>
        <i> Project Rendezvous — Data Entry Module </i>
    </footer>
</body>
</html>