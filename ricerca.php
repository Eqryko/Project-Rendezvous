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
    <style>
        body { font-family: sans-serif; padding: 20px; line-height: 1.6; }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #f8f8f8; }
        input { padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 10px; }
        /* Stile per il link */
        td a { color: #007bff; text-decoration: none; font-weight: bold; }
        td a:hover { text-decoration: underline; color: #0056b3; }
    </style>
</head>
<body>
    <a href="profilo.php" class="toggle-link" target="_blank">Profilo</a>
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

</body>
</html>