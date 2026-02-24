<!--

Refosco Enrico, Munaro Alex
Classe 5BII
Informatica

PROJECT RENDEZVOUS

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
    <style>
        
/* Sezione Astronauta Stilizzata */
.Astronaut-Highlight {
    max-width: 800px; /* Larghezza massima del blocco */
    margin: 40px auto; /* Centra il blocco nella pagina */
    padding: 20px;
    background: rgba(0, 0, 0, 0.6); /* Sfondo scuro semitrasparente */
    border: 2px solid #11e4ff; /* Bordo color ciano come il tuo tema */
    border-radius: 20px;
    box-shadow: 0 0 15px rgba(17, 228, 255, 0.2);
    position: relative;
    z-index: 2;
}

.Highlight-Container {
    display: flex;
    align-items: center; /* Centra verticalmente testo e immagine */
    justify-content: space-between;
    gap: 30px;
}

.Highlight-Text {
    flex: 1; /* Il testo occupa tutto lo spazio disponibile a sinistra */
    text-align: left;
}

.Highlight-Text h2 {
    font-size: 24px;
    margin-bottom: 15px;
    color: #11e4ff;
}

.quote {
    font-style: italic;
    font-size: 1.2rem;
    line-height: 1.5;
    margin-bottom: 10px;
}

cite {
    font-weight: bold;
    color: #11e4ff;
    font-style: normal;
}

/* Blocco Immagine a dimensione fissa */
.Highlight-Frame {
    flex: 0 0 300px; /* Non cresce, non rimpicciolisce, base di 250px */
    width: 300px;
    height: 300px;
    border: 3px solid white;
    border-radius: 15px;
    overflow: hidden; /* Taglia l'immagine se esce dai bordi */
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
}

.Highlight-Frame img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* Mantiene le proporzioni dell'immagine riempiendo il quadrato */
}

/* Mobile responsive: se lo schermo è piccolo, l'immagine va sotto */
@media (max-width: 600px) {
    .Highlight-Container {
        flex-direction: column;
        text-align: center;
    }
    .Highlight-Text {
        text-align: center;
    }
    .Highlight-Frame {
        flex: 0 0 200px;
        width: 200px;
        height: 200px;
    }
}
        </style>
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