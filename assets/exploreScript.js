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

    fetch(`ricerca.php?ajax=1&nome=${encodeURIComponent(query)}`)
        .then(response => response.text())
        .then(data => {
            tabella.innerHTML = data;
            // Reset stile per animazione entry
            document.querySelectorAll('.result-row').forEach(r => {
                r.style.opacity = "0";
                r.style.transform = "translateY(10px)";
                r.style.transition = "all 0.4s ease";
            });
            animateRows();
        });
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