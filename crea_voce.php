<?php
// crea_voce.php

session_start();
require "config.php";

// Recupero elenchi per i menu a tendina
$aziende = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'azienda'")->fetchAll(PDO::FETCH_ASSOC); // fetchAll per ottenere un array completo di x
$programmi = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'programma'")->fetchAll(PDO::FETCH_ASSOC);
$vettori = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'vettore'")->fetchAll(PDO::FETCH_ASSOC);
$veicoli = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'veicolo'")->fetchAll(PDO::FETCH_ASSOC);
$lanci = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'evento'")->fetchAll(PDO::FETCH_ASSOC);
$immagine_url = !empty($_POST['immagine_url']) ? $_POST['immagine_url'] : null;



// Funzione utile per trasformare stringhe vuote in NULL per il DB
function emptyToNull($value)
{
    return (trim($value) === '') ? null : $value;
}

// Protezione: solo utenti loggati
if (!isset($_SESSION['user_id'])) {
    die("Devi effettuare il login per creare una voce.");
}

// Gestione del form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $id_creatore = $_SESSION['user_id'];
    $immagine_url = !empty($_POST['immagine_url']) ? $_POST['immagine_url'] : null;

    try {
        $conn->beginTransaction();

        // 1. Inserimento nella tabella 'voce'
        $stmt = $conn->prepare("INSERT INTO voce (nome, tipo, creatore, stato, immagine_url) VALUES (?, ?, ?, 'IN_ATTESA', ?)");
        $stmt->execute([$nome, $tipo, $id_creatore, $immagine_url]);
        $id_voce = $conn->lastInsertId();

        // 2. Inserimento nella tabella specifica
        switch ($tipo) {
            case 'astronauta':
                $sql = "INSERT INTO astronauta (id_voce, nome, cognome, nazione, data_nascita, data_morte) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [
                    $id_voce,
                    emptyToNull($_POST['astro_nome']),
                    emptyToNull($_POST['astro_cognome']),
                    emptyToNull($_POST['astro_nazione']),
                    emptyToNull($_POST['astro_nascita']),
                    emptyToNull($_POST['astro_morte'])
                ];
                break;

            case 'azienda':
                // Corretto: 5 colonne e 5 punti di domanda
                $sql = "INSERT INTO azienda (id_voce, nomeIntero, nazione, sede, tipo) VALUES (?, ?, ?, ?, ?)";
                $params = [
                    $id_voce,
                    emptyToNull($_POST['az_nome']),
                    emptyToNull($_POST['az_nazione']),
                    emptyToNull($_POST['az_sede']),
                    emptyToNull($_POST['az_tipo'])
                ];
                break;

            case 'missione':
                // 2a. Prima creiamo l'evento di lancio correlato
                $stmt_ev = $conn->prepare("INSERT INTO evento (nome, data, ora, luogo, pianeta) VALUES (?, ?, ?, ?, ?)");
                $nome_evento = "Lancio di " . $nome;
                $stmt_ev->execute([
                    $nome_evento,
                    emptyToNull($_POST['miss_data_lancio']),
                    emptyToNull($_POST['miss_ora_lancio']),
                    emptyToNull($_POST['miss_luogo_lancio']),
                    emptyToNull($_POST['miss_pianeta_lancio'] ?? 'Terra')
                ]);
                $id_lancio_generato = $conn->lastInsertId();

                // 2b. Poi inseriamo la missione usando l'ID dell'evento appena creato
                $sql = "INSERT INTO missione (id_voce, cospar_id, nome, tipo, destinazione, esito, id_azienda, id_programma, id_vettore, id_veicolo, id_lancio) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [
                    $id_voce,
                    emptyToNull($_POST['miss_cospar']),
                    $nome,
                    emptyToNull($_POST['miss_tipo']),
                    emptyToNull($_POST['miss_dest']),
                    emptyToNull($_POST['miss_esito']),
                    emptyToNull($_POST['miss_azienda']),
                    emptyToNull($_POST['miss_programma']),
                    emptyToNull($_POST['miss_vettore']),
                    emptyToNull($_POST['miss_veicolo']),
                    $id_lancio_generato // Collega l'evento creato sopra
                ];
                break;
        }

        if (isset($sql)) {
            $stmt_det = $conn->prepare($sql);
            $stmt_det->execute($params);
        }

        $conn->commit();
        // Reindirizzamento alla pagina della voce appena creata
        header("Location: voce.php?id=" . $id_voce . "&msg=created");
        exit();

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // Salviamo l'errore per mostrarlo all'utente
        $errore_creazione = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Crea Nuova Voce</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <style>
        .dynamic-fields {
            display: none;
            margin-top: 20px;
            border-top: 1px solid #11e4ff;
            padding-top: 20px;
        }

        input,
        select {
            margin-bottom: 10px;
            width: 100%;
            padding: 8px;
        }

        label {
            color: #11e4ff;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
    </style>
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
        <h1>Nuova Voce </h1>
        <?php if (isset($errore_creazione)): ?>
            <div style="background: red; color: white; padding: 10px; margin-bottom: 20px;">
                Errore:
                <?= htmlspecialchars($errore_creazione) ?>
            </div>
        <?php endif; ?>

        <form action="crea_voce.php" method="POST">
            <label>Nome della Voce (Titolo):</label>
            <input type="text" name="nome" required placeholder="Es: Apollo 11 o Yuri Gagarin">

            <label>Tipo di Entità:</label>
            <select name="tipo" id="tipoSelect" required onchange="mostraCampi()">
                <option value="">-- Seleziona Tipo --</option>
                <option value="astronauta">Astronauta</option>
                <option value="azienda">Azienda</option>
                <option value="missione">Missione</option>

            </select>

            <label>Link Immagine (URL):</label>
            <input type="url" name="immagine_url" placeholder="https://esempio.com/immagine.jpg">

            <div id="fields_astronauta" class="dynamic-fields">
                <h3>Dettagli Astronauta</h3>
                <label>Nome:</label>
                <input type="text" name="astro_nome">
                <label>Cognome:</label>
                <input type="text" name="astro_cognome">
                <label>Nazione:</label>
                <input type="text" name="astro_nazione">
                <label>Data di Nascita:</label>
                <input type="date" name="astro_nascita">
                <label>Data di Morte (se applicabile):</label>
                <input type="date" name="astro_morte">
            </div>

            <div id="fields_azienda" class="dynamic-fields">
                <h3>Dettagli Azienda</h3>
                <label>Nome Intero:</label>
                <input type="text" name="az_nome">
                <label>Nazione:</label>
                <input type="text" name="az_nazione">
                <label>Sede:</label>
                <input type="text" name="az_sede">
                <label>Tipo:</label>
                <input type="text" name="az_tipo" placeholder="Es: Azienda Privata">
            </div>

            <div id="fields_missione" class="dynamic-fields">
                <h3>Dettagli Missione</h3>

                <label>COSPAR ID:</label>
                <input type="text" name="miss_cospar" placeholder="Es: 1969-059A">

                <label>Tipo Missione:</label>
                <input type="text" name="miss_tipo" placeholder="Es: Esplorazione lunare">

                <label>Destinazione:</label>
                <input type="text" name="miss_dest" placeholder="Es: Luna, Marte, LEO">

                <label>Esito:</label>
                <select name="miss_esito">
                    <option value="Successo">Successo</option>
                    <option value="Fallimento">Fallimento</option>
                    <option value="Parziale">Parziale</option>
                </select>

                <div style="background: rgba(17, 228, 255, 0.1); padding: 15px; border-radius: 5px; margin-top: 15px;">
                    <h4 style="color: #11e4ff; margin-top: 0;">🚀 Dettagli Lancio (Evento)</h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div>
                            <label>Data Lancio:</label>
                            <input type="date" name="miss_data_lancio">
                        </div>
                        <div>
                            <label>Ora Lancio:</label>
                            <input type="time" name="miss_ora_lancio">
                        </div>
                    </div>
                    <label>Luogo di Lancio:</label>
                    <input type="text" name="miss_luogo_lancio" placeholder="Es: Kennedy Space Center, Pad 39A">

                    <label>Pianeta di Partenza:</label>
                    <input type="text" name="miss_pianeta_lancio" value="Terra">
                </div>

                <hr style="margin: 20px 0; border: 0; border-top: 1px dashed #11e4ff;">

                <label>Azienda Responsabile:</label>
                <select name="miss_azienda">
                    <option value="">-- Nessuna / Sconosciuta --</option>
                    <?php foreach ($aziende as $az): ?>
                        <option value="<?= $az['id_voce'] ?>">
                            <?= htmlspecialchars($az['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Programma Spaziale:</label>
                <select name="miss_programma">
                    <option value="">-- Nessun Programma --</option>
                    <?php foreach ($programmi as $pr): ?>
                        <option value="<?= $pr['id_voce'] ?>">
                            <?= htmlspecialchars($pr['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Vettore (Razzo):</label>
                <select name="miss_vettore">
                    <option value="">-- Seleziona Vettore --</option>
                    <?php foreach ($vettori as $vt): ?>
                        <option value="<?= $vt['id_voce'] ?>">
                            <?= htmlspecialchars($vt['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label>Veicolo / Sonda:</label>
                <select name="miss_veicolo">
                    <option value="">-- Seleziona Veicolo --</option>
                    <?php foreach ($veicoli as $vc): ?>
                        <option value="<?= $vc['id_voce'] ?>">
                            <?= htmlspecialchars($vc['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit"
                style="margin-top:20px; background:#11e4ff; border:none; padding:10px 20px; cursor:pointer; font-weight:bold;">SALVA
                VOCE</button>
        </form>
    </div>

    <script>
        function mostraCampi() {
            // Nascondi tutti i campi extra
            document.querySelectorAll('.dynamic-fields').forEach(div => div.style.display = 'none');

            // Mostra solo quello selezionato
            const tipo = document.getElementById('tipoSelect').value;
            if (tipo) {
                const target = document.getElementById('fields_' + tipo);
                if (target) target.style.display = 'block';
            }
        }
    </script>
</body>
</html>