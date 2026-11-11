document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formBusqueda');
    const input = document.getElementById('buscarSeccion');
    const message = document.getElementById('mensajeBusqueda');
    if (!form || !input || !message) return;

    const sections = [
        { words: ['denuncia', 'denunciar', 'formulario', 'presentar'], url: 'denuncias/nueva.php', label: 'Realizar una denuncia' },
        { words: ['seguimiento', 'estado', 'codigo', 'clave', 'consultar'], url: 'seguimiento/consultar.php', label: 'Consultar seguimiento' },
        { words: ['funciona', 'proceso', 'pasos'], url: '#como-funciona', label: 'Cómo funciona' },
        { words: ['seguridad', 'anonima', 'anonimato', 'caracteristicas'], url: '#caracteristicas', label: 'Características' },
        { words: ['ayuda', 'orientacion'], url: '#ayuda', label: 'Ayuda' }
    ];

    form.addEventListener('submit', event => {
        event.preventDefault();
        const query = input.value.trim().toLocaleLowerCase('es');
        if (!query) {
            message.textContent = 'Escribí una palabra para buscar una sección.';
            input.focus();
            return;
        }
        const result = sections.find(item => item.words.some(word => query.includes(word) || word.includes(query)));
        if (result) {
            message.textContent = 'Sección encontrada: ' + result.label;
            window.location.href = result.url;
        } else {
            message.textContent = 'No encontramos esa sección. Probá con “denuncia”, “seguimiento” o “ayuda”.';
        }
    });
});
