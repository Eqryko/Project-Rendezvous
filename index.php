<!--

Refosco Enrico, Munaro Alex
Classe 5BII
Informatica

PROJECT RENDEZVOUS

-->

<?php
// index.php
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
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <script src="scripts/script.js" defer></script>
</head>

<body>

    <img class="logo" src="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>
    <img class="pad" src="media/iss.jpg">
    <br><br>
    <div class="Main">
        <h1> Project Rendezvous </h1>

        <form action="" method="post">
            <h2> Il Progetto </h2>

            <p> Lorem Ipsum </p>
            
            <h2> Catalogo</h2>

            <p>Ultime aggiunte</p>
            <a href="ricerca.php" class="ricerca">Ricerca</a><br>
            
        </form>
    </div>
    <br><br><br>
    <hr>
    <div class="Astronaut-Highlight">
    <div class="Highlight-Container">
        <div class="Highlight-Text">
            <h2>Ispirazione dallo Spazio</h2>
            <p>Quando Yuri Gagarin atterrò il 12 aprile 1961 dopo il volo della Vostok 1, la prima persona che incontrò fu una bambina (Rita) e sua nonna, in un campo vicino al villaggio di Smelovka. Indossava ancora la tuta spaziale arancione, e loro erano spaventate.</p>
            <p class="quote">
"Non abbiate paura, sono uno di voi, un sovietico! Sono venuto dallo spazio e devo chiamare Mosca!"</p>
            <cite> - Yuri Gagarin </cite>
        </div>
        <div class="Highlight-Frame">
            <img src="media/YuriGagarin.jpg" alt="Astronauta">
        </div>
    </div>
</div>
    <footer>
        <p id="usage"></p>
        <i> Credits: <br>
            Refosco Enrico - enricoorefosco@gmail.com <br>
            Munaro Alex - alexmunaro22@gmail.com
        </i> <br>
    </footer>
</body>

</html>