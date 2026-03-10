// Funzione per animare le righe che appaiono
function animateRows() {
    const rows = document.querySelectorAll('.result-row'); // Seleziona tutte le righe dei risultati
    rows.forEach((row, i) => {
        setTimeout(() => {
            row.style.opacity = "1";
            row.style.transform = "translateY(0)";
        }, i * 50);
    });
}

function caricaDati() { // Funzione per caricare i dati in base alla ricerca
    const query = document.getElementById('cercaNome').value;
    const tabella = document.getElementById('corpoTabella');

    // FETCH: metodo moderno per fare richieste HTTP, più semplice e leggibile di XMLHttpRequest

    // Chiamata AJAX a ricerca.php con parametro ajax=1
    fetch(`ricerca.php?ajax=1&nome=${encodeURIComponent(query)}`) // encodeURIComponent per gestire spazi e caratteri speciali nel nome

    // fetch restituisce una promessa, quindi usiamo .then() per gestire la risposta
        .then(response => response.text()) // convertiamo la risposta in testo (HTML della tabella)
        .then(data => {
            // una volta ricevuti i dati, aggiorna la tabella
            tabella.innerHTML = data;

            // stili
            document.querySelectorAll('.result-row').forEach(r => {
                r.style.opacity = "0";
                r.style.transform = "translateY(10px)";
                r.style.transition = "all 0.4s ease";
            });
            animateRows();
        });
        // Non è necessario gestire errori in questo caso
        // ritorna una promessa per permettere l'uso di .then() in explore.html
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