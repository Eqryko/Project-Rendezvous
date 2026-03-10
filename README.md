# 🚀 Project Rendezvous — Archive System v2.0

**Project Rendezvous** è un'applicazione web dinamica per la gestione e la consultazione di un archivio astronautico. Il sistema permette di esplorare dati su missioni, astronauti, aziende e vettori spaziali attraverso un'interfaccia fluida, moderna e reattiva.

---

## 🛠️ Tech Stack

Il progetto è costruito utilizzando tecnologie standard del settore per garantire prestazioni, scalabilità e sicurezza:

* **Frontend:** HTML5, CSS3 (Custom Animations), JavaScript (ES6+).
* **Backend:** PHP 8.x.
* **Database:** MySQL / MariaDB.
* **Comunicazione:** AJAX tramite **Fetch API** con gestione asincrona (**Promises**).
* **Sicurezza:** Prepared Statements con **PDO** (PHP Data Objects).

---

## ✨ Funzionalità Principali

### 🔍 Explore Mode (Ricerca AJAX)
L'archivio utilizza la tecnica **AJAX** per filtrare i risultati in tempo reale. Grazie alla **Fetch API**, l'utente può digitare termini di ricerca e vedere la tabella aggiornarsi istantaneamente senza ricaricare la pagina.
* **Smart Filtering:** Il sistema riconosce automaticamente categorie (es. "Missione", "Astronauta") all'interno della stringa di ricerca tramite parsing lato server.
* **Smooth UI:** Sistema di animazioni CSS coordinate con l'iniezione dei dati nel DOM tramite JavaScript.

### 🔐 Sistema di Autenticazione & Sessioni
* Gestione granulare delle sessioni utente (`session_start`).
* Protezione delle aree riservate (es. `profilo.php`, `crea_voce.php`).
* **Data Integrity:** Recupero dati "freschi" dal database a ogni caricamento pagina per prevenire inconsistenze tra sessione e DB.

### 🗄️ Architettura del Database
Il database `astronauticadb` implementa relazioni relazionali classiche:
* **1:N:** Tra categorie e voci.
* **N:M:** Gestite tramite tabelle di join (es. Astronauti <-> Missioni).
* **Vincoli:** Utilizzo di Foreign Keys e indici `UNIQUE` per garantire la cardinalità delle relazioni (specialmente nelle 1:1).

---

## 📂 Struttura del Progetto

```text
/Project-Rendezvous
├── /assets
│   ├── /fonts
│   ├── /images
│   ├── /styles        # Fogli di stile (rstyle.css, nav_style.css)
│   ├── /images
│   └── /scripts       # Logica JS (exploreScript.js, animazioni)
├── /src
│   ├── /services
│   └── /components    # Core (config.php con connessione PDO)
├── /auth              # Moduli Login e Registrazione
├── /infos
├── ricerca.php        # Pagina principale con logica Dual-Mode (HTML/AJAX)
├── profilo.php        # Dashboard utente e controllo sessione
└── voce.php           # Visualizzazione dettagliata della singola entry