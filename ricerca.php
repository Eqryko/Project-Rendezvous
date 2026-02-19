<?php
session_start();
// --- CONFIGURAZIONE DATABASE ---
$host = "localhost";
$user = "root";
$password = "";
$db_name = "astronauticadb";

$conn = new mysqli($host, $user, $password, $db_name);

if ($conn->connect_error) {
    die("Connessione fallita.");
}

// --- LOGICA DI RICERCA AJAX ---
if (isset($_GET['ajax'])) {
    $nome = isset($_GET['nome']) ? $_GET['nome'] : '';

    if (empty($nome)) {
        $sql = "SELECT * FROM voce";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT * FROM voce WHERE nome LIKE ?";
        $stmt = $conn->prepare($sql);
        $param = "%$nome%";
        $stmt->bind_param("s", $param);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Creazione del link dinamico con l'ID
            $id = $row['id_voce'];
            $nome_pulito = htmlspecialchars($row['nome']);

            echo "<tr>
                    <td>{$id}</td>
                    <td>
                        <a href='voce.php?id={$id}' target='_blank'>{$nome_pulito}</a>
                    </td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2'>Nessun risultato trovato.</td></tr>";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Ricerca Live</title>

    <link rel="stylesheet" href="styles/rstyle.css">
</head>
<body>
    <header class="Nav">
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://github.com/Eqryko" target="_blank"> GitHub Profile</a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>
    <a href="profilo.php" class="toggle-link" target="_blank">Profilo</a>
    

    <div class="Main">
        <h2>Gestione Voci</h2>
        <p>Digita per filtrare. Clicca sul nome per vedere i dettagli.</p>

        <input type="text" id="cercaNome" placeholder="Cerca un nome..." oninput="caricaDati()">

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome (Clicca per dettagli)</th>
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

            // Caricamento iniziale
            window.onload = caricaDati;
        </script>
    </div>
    <footer>
        <p id="usage"></p>
        <i> Credits: Refosco Enrico <br>
            enricoorefosco@gmail.com </i> <br>
    </footer>
</body>
</html>