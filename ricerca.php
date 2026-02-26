<?php
// ricerca.php
session_start();
require "config.php";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) { die("Connessione fallita."); }

// --- LOGICA AJAX ---
if (isset($_GET['ajax'])) {
    $input = isset($_GET['nome']) ? trim($_GET['nome']) : '';

    if (empty($input)) {
        $sql = "SELECT * FROM voce WHERE stato = 'APPROVATA'";
        $stmt = $conn->prepare($sql);
    } else {
        $input_pulito = str_replace("VETTOREVEICOLO", "VETTORE VEICOLO", strtoupper($input));
        $termini = explode(" ", $input_pulito);
        $termini = array_filter($termini); 

        $sql = "SELECT * FROM voce WHERE stato = 'APPROVATA' AND (";
        $condizioni = [];
        $params = [];
        $types = "";

        foreach ($termini as $termine) {
            $condizioni[] = "(nome LIKE ? OR tipo LIKE ?)";
            $cerca = "%$termine%";
            $params[] = $cerca;
            $params[] = $cerca;
            $types .= "ss";
        }
        $sql .= implode(" OR ", $condizioni) . ")";
        $stmt = $conn->prepare($sql);
        if ($types) { $stmt->bind_param($types, ...$params); }
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['id_voce'];
            $nome = htmlspecialchars($row['nome']);
            $tipo = htmlspecialchars($row['tipo']);

            // Riga con classe reveal per animazione
            echo "<tr class='result-row'>
                    <td style='color: #333; font-family: monospace;'>#{$id}</td>
                    <td><a href='voce.php?id={$id}'>{$nome}</a></td>
                    <td style='letter-spacing: 0.1em; font-size: 0.8rem; color: #888;'>{$tipo}</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='3' style='padding: 50px 0; color: #444;'>NESSUN DATO TROVATO NELL'ARCHIVIO.</td></tr>";
    }
    $stmt->close(); $conn->close(); exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <title>Archive Search — Rendezvous</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    
    <link rel="stylesheet" href="styles/rstyle.css">
    <link rel="stylesheet" href="styles/nav_style.css">
</head>
<body>

    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="Main1">
        <div class="reveal">
            <h2>Archive<br>Explorer</h2>
            <p>Database Query Interface</p>
        </div>

        <a href="crea_voce.php" target="_blank" id="creavoce" class="reveal">Add Entry +</a>

        <div class="search-container reveal">
            <input type="text" id="cercaNome" placeholder="TYPE TO FILTER ARCHIVE..." oninput="caricaDati()" autocomplete="off">
        </div>

        <table class="reveal">
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="60%">Designazione</th>
                    <th width="30%">Categoria</th>
                </tr>
            </thead>
            <tbody id="corpoTabella">
                </tbody>
        </table>
    </div>

    <footer>
        <i> Project Rendezvous — Archive System v2.0 </i>
    </footer>

    <script>
        // Funzione per animare le righe che appaiono
        function animateRows() {
            const rows = document.querySelectorAll('.result-row');
            rows.forEach((row, i) => {
                setTimeout(() => {
                    row.style.opacity = "1";
                    row.style.transform = "translateY(0)";
                }, i * 50);
            });
        }

        function caricaDati() {
            const query = document.getElementById('cercaNome').value;
            const tabella = document.getElementById('corpoTabella');

            fetch(`ricerca.php?ajax=1&nome=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(data => {
                    tabella.innerHTML = data;
                    // Reset stile per animazione entry
                    document.querySelectorAll('.result-row').forEach(r => {
                        r.style.opacity = "0";
                        r.style.transform = "translateY(10px)";
                        r.style.transition = "all 0.4s ease";
                    });
                    animateRows();
                });
        }

        // Intersection Observer per i titoli iniziali
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) entry.target.classList.add('active');
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

            // Logica parametri URL
            const urlParams = new URLSearchParams(window.location.search);
            const tipoQuery = urlParams.get('tipo');
            if (tipoQuery) { document.getElementById('cercaNome').value = tipoQuery; }
            caricaDati();
        });
    </script>
</body>
</html>