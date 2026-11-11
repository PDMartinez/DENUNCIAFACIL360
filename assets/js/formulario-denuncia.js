document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formDenuncia');
    if (!form) return;

    const steps = [...form.querySelectorAll('.form-step')];
    const indicators = [...form.querySelectorAll('[data-step-indicator]')];
    const previous = document.getElementById('btnAnterior');
    const next = document.getElementById('btnSiguiente');
    const send = document.getElementById('btnEnviar');
    const upload = document.getElementById('evidencias');
    const fileList = document.getElementById('listaArchivos');
    let current = 0;

    function showStep(index) {
        current = index;
        steps.forEach((step, i) => step.classList.toggle('active', i === index));
        indicators.forEach((item, i) => {
            item.classList.toggle('active', i === index);
            item.classList.toggle('done', i < index);
        });
        previous.hidden = index === 0;
        next.hidden = index === steps.length - 1;
        send.hidden = index !== steps.length - 1;
        if (index === steps.length - 1) buildSummary();
        window.scrollTo({ top: form.getBoundingClientRect().top + window.scrollY - 110, behavior: 'smooth' });
    }

    function validateStep(index) {
        const fields = [...steps[index].querySelectorAll('input, select, textarea')];
        let valid = true;
        fields.forEach(field => {
            if (field.type === 'file') return;
            if (!field.checkValidity()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        if (!valid) {
            const first = fields.find(field => field.type !== 'file' && !field.checkValidity());
            if (first) first.focus();
        }
        return valid;
    }

    function buildSummary() {
        const summary = document.getElementById('resumenFormulario');
        const title = document.getElementById('titulo').value.trim();
        const category = document.getElementById('categoria_id').selectedOptions[0]?.text || '';
        const institution = document.getElementById('institucion_id').selectedOptions[0]?.text || '';
        const date = document.getElementById('fecha_hecho').value;
        const person = document.getElementById('persona_nombre').value.trim() || 'No informada';
        const files = upload.files.length;
        summary.innerHTML = `<strong>Resumen de tu denuncia</strong><ul class="mb-0 mt-2">
          <li><b>Asunto:</b> ${escapeHtml(title)}</li>
          <li><b>Categoría:</b> ${escapeHtml(category)}</li>
          <li><b>Institución:</b> ${escapeHtml(institution)}</li>
          <li><b>Fecha de los hechos:</b> ${escapeHtml(date)}</li>
          <li><b>Persona denunciada:</b> ${escapeHtml(person)}</li>
          <li><b>Archivos adjuntos:</b> ${files}</li>
        </ul>`;
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
    }

    next.addEventListener('click', () => {
        if (validateStep(current) && current < steps.length - 1) showStep(current + 1);
    });
    previous.addEventListener('click', () => showStep(Math.max(0, current - 1)));

    upload.addEventListener('change', () => {
        fileList.innerHTML = '';
        const allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        const maxSize = 5 * 1024 * 1024;
        let error = '';
        if (upload.files.length > 5) error = 'Podés adjuntar hasta 5 archivos.';
        [...upload.files].forEach(file => {
            if (!allowed.includes(file.type) || file.size > maxSize) error = 'Revisá los archivos: solo PDF/JPG/PNG de hasta 5 MB cada uno.';
            const item = document.createElement('div');
            item.textContent = `${file.name} — ${(file.size / 1024 / 1024).toFixed(2)} MB`;
            fileList.appendChild(item);
        });
        if (error) {
            alert(error);
            upload.value = '';
            fileList.innerHTML = '';
        }
    });

    form.addEventListener('submit', event => {
        if (!validateStep(3)) {
            event.preventDefault();
            return;
        }
        send.disabled = true;
        send.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    });
});
