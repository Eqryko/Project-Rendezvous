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
    <meta name="author" content="Refosco Enrico, Munaro Alex">
    
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/nav_style.css">
    <script src="scripts/script.js" defer></script>
    <script src="assets/revealOnScroll.js" defer></script>
</head>

<body>

    <header>
        <a href="index.php" class="toggle-link">Home</a>
        <a href="profilo.php" class="toggle-link" target="_blank">Profile</a>
        <a href="https://www.itisrossi.edu.it/" target="_blank">ITIS Rossi</a>
        <a href="https://docs.google.com/document/d/1Jcs8CQ-wG9qLcFgkkqrC7aUbv7rLe4OOsSBoiXvcVh4/edit?usp=sharing" target="_blank"> Documentazione </a>
        <a href="https://github.com/Eqryko/Project-Rendezvous" target="_blank"> Repository </a>
    </header>

    <div class="Main">
        <section class="hero-section reveal">
            <h1>Project<br>Rendezvous</h1>
            <div class="hero-meta">
                <div class="line"></div>
                <span>CORE_DATABASE_V2</span>
                <span>SYSTEM_READY</span>
            </div>
            
            <div class="btn-container">
                <a href="ricerca.php" class="btn-action btn-primary">Esplora Catalogo</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="crea_voce.php" class="btn-action btn-secondary">Aggiungi Voce</a>
                <?php endif; ?>
            </div>
        </section>

        <div class="project-description reveal">
            <p style="color: var(--accent); font-family: monospace; font-size: 0.8rem; letter-spacing: 0.3em; text-transform: uppercase;">The Mission</p>
            <p style="font-size: clamp(1.5rem, 4vw, 2.2rem); font-weight: 500; line-height: 1.2; margin-top: 15px;">
                A digital infrastructure designed to map humanity's technological legacy in deep space.
            </p>
        </div>

        <div class="stats-container">
            <div class="stat-card reveal" onclick="window.location.href='ricerca.php?tipo=MISSIONE'">
                <strong>Missioni</strong>
                <p>Archivio storico dei lanci e degli obiettivi orbitali.</p>
            </div>
            <div class="stat-card reveal" onclick="window.location.href='ricerca.php?tipo=ASTRONAUTA'">
                <strong>Astronauti</strong>
                <p>Registri biogafici e record di permanenza nel cosmo.</p>
            </div>
            <div class="stat-card reveal" onclick="window.location.href='ricerca.php?tipo=VETTORE VEICOLO'">
                <strong>Tecnologia</strong>
                <p>Sistemi propulsivi, vettori e hardware spaziale.</p>
            </div>
            <div class="stat-card reveal" onclick="window.location.href='ricerca.php?tipo=AZIENDA'">
                <strong>Aziende</strong>
                <p>Agenzie governative e partner dell'industria privata.</p>
            </div>
        </div>

        <div class="quote-section reveal">
            <p class="quote">"Non abbiate paura, sono uno di voi! Sono venuto dallo spazio."</p>
            <cite style="color: var(--accent); font-weight: 700; letter-spacing: 0.1em; font-style: normal;">— YURI GAGARIN, 1961</cite>
        </div>

        <?php if(isset($_SESSION['ruolo'])): ?>
            <div class="reveal" style="text-align: center; margin-top: 100px;">
                <a href="admin.php" style="color: #333; text-decoration: none; font-family: monospace; font-size: 0.7rem; letter-spacing: 0.4em;">[ ADMIN_PANEL_ACCESS ]</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>All rights reserved</p>
        <p id="usage"></p>
        <!-- DA AGGIUNGERE
         all rights reserved
         terms and conditions
         Contacts
         -->
        <i> Credits: <br>
            Refosco Enrico & Munaro Alex
        </i>
    </footer>
</body>
</html>