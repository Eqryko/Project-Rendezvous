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
    <link rel="icon" href="https://scaling.spaggiari.eu/VIIT0005/favicon/75.png&amp;rs=%2FtccTw2MgxYfdxRYmYOB6AaWDwig7Mjl0zrQBslusFLrgln8v1dFB63p5qTp4dENr3DeAajXnV%2F15HyhNhRR%2FG8iNdqZaJxyUtaPePHkjhBWQioJKGUGZCYSU7n9vRa%2FmjC9hNCI%2BhCFdoBQkMOnT4UzIQUf8IQ%2B8Qm0waioy5M%3D">
    <meta name="author" content="Refosco Enrico">
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <script src="scripts/script.js" defer></script>

    <style>
        /* CSS Interno per rendere la pagina più tecnica */
        .Main h1 {
            letter-spacing: 4px;
            text-transform: uppercase;
            border-bottom: 2px solid #11e4ff;
            display: inline-block;
            padding-bottom: 10px;
        }

        .project-description {
            line-height: 1.6;
            color: #e0e0e0;
            text-align: justify;
            background: rgba(17, 228, 255, 0.05);
            padding: 20px;
            border-left: 3px solid #11e4ff;
            margin: 20px 0;
        }

        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 30px 0;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border: 1px solid rgba(17, 228, 255, 0.2);
            transition: 0.3s;
        }

        .stat-card:hover {
            border-color: #11e4ff;
            background: rgba(17, 228, 255, 0.1);
        }

        .stat-card strong {
            display: block;
            color: #11e4ff;
            font-size: 1.2em;
        }

        .btn-container {
            margin-top: 30px;
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-action {
            text-decoration: none;
            padding: 12px 30px;
            border: 1px solid #11e4ff;
            color: #11e4ff;
            font-weight: bold;
            text-transform: uppercase;
            transition: 0.4s;
            position: relative;
            overflow: hidden;
        }

        .btn-action:hover {
            background: #11e4ff;
            color: #000;
            box-shadow: 0 0 15px rgba(17, 228, 255, 0.5);
        }

        .admin-link {
            color: #888;
            font-size: 0.8em;
            text-decoration: none;
            margin-top: 20px;
            display: inline-block;
        }

        .admin-link:hover { color: #ff4d4d; }

        .tech-label {
            font-family: monospace;
            color: #11e4ff;
            font-size: 0.9em;
            margin-bottom: 5px;
            display: block;
        }
    </style>
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
        <span class="tech-label">[ SYSTEM-STATUS: OPERATIONAL ]</span>
        <h1> Project Rendezvous </h1>

        <div class="project-description">
            <h2> Obiettivo della Missione </h2>
            <p>
                Project Rendezvous è un'iniziativa di catalogazione scientifica dedicata all'esplorazione spaziale. 
                Il sistema centralizza dati relativi a missioni, astronauti e vettori, permettendo una consultazione 
                incrociata dei traguardi tecnologici raggiunti dall'umanità oltre l'atmosfera terrestre.
            </p>
        </div>
        
        <h2> Archivio Dati </h2>
        <p>Seleziona una categoria per iniziare la navigazione nel database:</p>

        <div class="stats-container">
    <div class="stat-card" onclick="window.location.href='ricerca.php?tipo=MISSIONE'" style="cursor: pointer;">
        <strong>MISSIONI</strong>
        <p>Esplorazioni & Eventi</p>
    </div>
    <div class="stat-card" onclick="window.location.href='ricerca.php?tipo=ASTRONAUTA'" style="cursor: pointer;">
        <strong>ASTRONAUTI</strong>
        <p>Equipaggi & Biografìe</p>
    </div>
    <div class="stat-card" onclick="window.location.href='ricerca.php?tipo=VETTORE VEICOLO'" style="cursor: pointer;">
        <strong>TECNOLOGIA</strong>
        <p>Vettori & Veicoli</p>
    </div>
    <div class="stat-card" onclick="window.location.href='ricerca.php?tipo=AZIENDA'" style="cursor: pointer;">
        <strong>AZIENDE</strong>
        <p>Agenzie & Partner</p>
    </div>
</div>

        <div class="btn-container">
            <a href="ricerca.php" class="btn-action">Accedi al Catalogo Completo</a>
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="crea_voce.php" class="btn-action">Contribuisci all'Archivio</a>
            <?php endif; ?>
        </div>
                <br>
        <?php if(isset($_SESSION['ruolo'])): ?>
            <a href="admin.php" class="btn-action">PANNELLO DI CONTROLLO AMMINISTRATIVO</a>
        <?php endif; ?>
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