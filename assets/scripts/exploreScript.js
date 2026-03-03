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

    // Mostra un indicatore di caricamento
    fetch(`ricerca.php?ajax=1&nome=${encodeURIComponent(query)}`) // Chiamata AJAX a ricerca.php con parametro ajax=1
    // richiesta GET a ricerca.php, passando il nome come parametro (per default)
        .then(response => response.text())
        .then(data => {
            // una volta ricevuti i dati, aggiorna la tabella
            tabella.innerHTML = data;
            // Reset stile per animazione entry
            document.querySelectorAll('.result-row').forEach(r => {
                r.style.opacity = "0";
                r.style.transform = "translateY(10px)";
                r.style.transition = "all 0.4s ease";
            });
            animateRows();
        });
        // ritorna una promessa per permettere l'uso di .then() in explore.html
        // Richiesto in sede d'esame
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