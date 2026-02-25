<?php
session_start();
require "config.php";

// Recupero elenchi per i menu a tendina
$aziende = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'azienda'")->fetchAll(PDO::FETCH_ASSOC);
$programmi = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'programma'")->fetchAll(PDO::FETCH_ASSOC);
$vettori = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'vettore'")->fetchAll(PDO::FETCH_ASSOC);
$veicoli = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'veicolo'")->fetchAll(PDO::FETCH_ASSOC);
$lanci = $conn->query("SELECT id_voce, nome FROM voce WHERE tipo = 'evento'")->fetchAll(PDO::FETCH_ASSOC);

// Funzione utile per trasformare stringhe vuote in NULL per il DB
function emptyToNull($value)
{
    return (trim($value) === '') ? null : $value;
}

// Protezione: solo utenti loggati
if (!isset($_SESSION['user_id'])) {
    die("Devi effettuare il login per creare una voce.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $id_creatore = $_SESSION['id_utente'];

    try {
        $conn->beginTransaction();

        // 1. Inserimento nella tabella 'voce'
        $stmt = $conn->prepare("INSERT INTO voce (nome, tipo, creatore, stato) VALUES (?, ?, ?, 'IN_APPROVAZIONE')");
        $stmt->execute([$nome, $tipo, $id_creatore]);
        $id_voce = $conn->lastInsertId();

        // 2. Inserimento nella tabella specifica in base al tipo
        // Nota: Qui recuperiamo i campi dinamici dal $_POST
        switch ($tipo) {
            case 'astronauta':
                $sql = "INSERT INTO astronauta (id_voce, nome, cognome, nazione, data_nascita, data_morte) VALUES (?, ?, ?, ?, ?, ?)";
                $params = [$id_voce, $_POST['astro_nome'], $_POST['astro_cognome'], $_POST['astro_nazione'], $_POST['astro_nascita'], $_POST['astro_morte']];
                break;
            case 'azienda':
                $sql = "INSERT INTO azienda (id_voce, nomeIntero, nazione, sede, tipo) VALUES (?, ?, ?, ?)";
                $params = [$id_voce, $_POST['az_nome'], $_POST['az_nazione'], $_POST['az_sede'], $_POST['az_tipo']];
                break;
            case 'missione':
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
                    emptyToNull($_POST['miss_lancio'])
                ];
                break;

        }

        if (isset($sql)) {
            $stmt_det = $conn->prepare($sql);
            $stmt_det->execute($params);
        }

        $conn->commit();
        header("Location: voce.php?id=" . $id_voce);
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Errore durante il salvataggio: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Crea Nuova Voce</title>
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
                <input type="text" name="miss_cospar">

                <label>Tipo Missione:</label>
                <input type="text" name="miss_tipo" placeholder="Es: Esplorazione lunare">

                <label>Destinazione:</label>
                <input type="text" name="miss_dest">

                <label>Esito:</label>
                <select name="miss_esito">
                    <option value="Successo">Successo</option>
                    <option value="Fallimento">Fallimento</option>
                    <option value="Parziale">Parziale</option>
                </select>

                <hr> <label>Azienda Responsabile:</label>
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

                <label>Evento di Lancio:</label>
                <select name="miss_lancio">
                    <option value="">-- Seleziona Evento --</option>
                    <?php foreach ($lanci as $ln): ?>
                        <option value="<?= $ln['id_voce'] ?>">
                            <?= htmlspecialchars($ln['nome']) ?>
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