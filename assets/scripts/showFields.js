function mostraCampi() {
    document.querySelectorAll('.dynamic-fields').forEach(div => div.style.display = 'none');
    const tipo = document.getElementById('tipoSelect').value;
    if (tipo) {
        const target = document.getElementById('fields_' + tipo);
        if (target) target.style.display = 'block';
    }
}