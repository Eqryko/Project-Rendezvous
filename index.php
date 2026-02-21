<!--
Classe 5BII
Informatica
-->

<?php
    session_start();
?>

<!DOCTYPE html>

<html lang="it">

<head>
    <title> Project Rendezvous </title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon"
        href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico">
    
    <link rel="stylesheet" href="styles/stylee.css">
    <script src="scripts/script.js" defer></script>
</head>

<body>

    <img class="logo" src="media/greenmantis.png">
    <header class="Nav">
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://github.com/Eqryko" target="_blank"> GitHub Profile</a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>
    <div class="Main">
        <h1> Project Rendezvous </h1>

        <form action="" method="post">
            <h2> Catalogo</h2>

            <p>Ultime aggiunte</p>
            <a href="ricerca.php" class="toggle-link">Ricerca</a><br>
            
            <p>
                <input type="submit" value="Invia" name="Invia">
                <input type="reset" value="Cancella" name="Cancella">
            </p>
        </form>
    </div><br><br><br>
    <hr>
    <footer>
        <p id="usage"></p>
        <i> Credits: Refosco Enrico <br>
            enricoorefosco@gmail.com </i> <br>
    </footer>


</body>

</html>