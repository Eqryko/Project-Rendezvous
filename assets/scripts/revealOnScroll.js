// revealOnScroll.js
document.addEventListener('DOMContentLoaded', () => {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: "0px 0px -50px 0px"
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
            }
        });
    }, observerOptions);

    const elements = document.querySelectorAll('.reveal');
    elements.forEach((el, index) => {
        // Se sono card della grid, aggiunge un ritardo "staggered"
        if (el.classList.contains('stat-card')) {
            el.style.transitionDelay = `${(index % 4) * 0.15}s`;
        }
        observer.observe(el);
    });
});