<?php
// ricerca.php
session_start();
require "config.php";

// Connessione mysqli come da tuo setup
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connessione fallita.");
}

// --- LOGICA DI RICERCA AJAX ---
if (isset($_GET['ajax'])) {
    $input = isset($_GET['nome']) ? trim($_GET['nome']) : '';

    if (empty($input)) {
        $sql = "SELECT * FROM voce WHERE stato = 'APPROVATA'";
        $stmt = $conn->prepare($sql);
    } else {
        // Logica esclusiva: separiamo le parole per cercare in AND/OR
        // Gestiamo il caso specifico "VETTOREVEICOLO" splittandolo
        $input_pulito = str_replace("VETTOREVEICOLO", "VETTORE VEICOLO", strtoupper($input));
        $termini = explode(" ", $input_pulito);
        $termini = array_filter($termini); 

        $sql = "SELECT * FROM voce WHERE stato = 'APPROVATA' AND (";
        $condizioni = [];
        $params = [];
        $types = "";

        foreach ($termini as $termine) {
            // Cerchiamo il termine sia nel nome che nel tipo
            $condizioni[] = "(nome LIKE ? OR tipo LIKE ?)";
            $cerca = "%$termine%";
            $params[] = $cerca;
            $params[] = $cerca;
            $types .= "ss";
        }

        $sql .= implode(" OR ", $condizioni) . ")";
        $stmt = $conn->prepare($sql);
        
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['id_voce'];
            $nome_pulito = htmlspecialchars($row['nome']);
            $tipo_pulito = htmlspecialchars($row['tipo']);

            echo "<tr>
                    <td>{$id}</td>
                    <td>
                        <a href='voce.php?id={$id}'>{$nome_pulito}</a>
                    </td>
                    <td>{$tipo_pulito}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3'>Nessun risultato trovato.</td></tr>";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Ricerca Live</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    <link rel="stylesheet" href="styles/rstyle.css">
    <link rel="stylesheet" href="styles/nav_style.css">
</head>
<body>

    <img class="logo" src="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>
    

    <div class="Main1">
        <br>
        <h2>Gestione Voci</h2>
        <p>Digita per filtrare. Clicca sul nome per vedere i dettagli.</p>

        <input type="text" id="cercaNome" placeholder="Cerca una voce o un tipo..." oninput="caricaDati()">
        <a href="crea_voce.php" class="toggle-link" target="_blank" id="creavoce">Crea voce</a>


        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome (Clicca per dettagli)</th>
                    <th>Tipo</th>
                </tr>
            </thead>
            <tbody id="corpoTabella">
            </tbody>
        </table>

        <script>
            function caricaDati() {
                const query = document.getElementById('cercaNome').value;
                const tabella = document.getElementById('corpoTabella');

                fetch(`ricerca.php?ajax=1&nome=${encodeURIComponent(query)}`)
                    .then(response => response.text())
                    .then(data => {
                        tabella.innerHTML = data;
                    })
                    .catch(error => console.error('Errore durante la ricerca:', error));
            }

            // Gestione automatica del parametro proveniente dalle stat-cards
            window.onload = function() {
                const urlParams = new URLSearchParams(window.location.search);
                const tipoQuery = urlParams.get('tipo');

                if (tipoQuery) {
                    document.getElementById('cercaNome').value = tipoQuery;
                }
                
                caricaDati();
            };
        </script>
    </div>
    <footer>
        <p id="usage"></p>
        <i> Credits: Refosco Enrico <br>
            enricoorefosco@gmail.com </i> <br>
    </footer>
</body>
</html>