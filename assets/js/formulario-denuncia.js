document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formDenuncia');
    if (!form) return;

    const steps = [...form.querySelectorAll('.form-step')];
    const indicators = [...form.querySelectorAll('[data-step-indicator]')];
    const previous = document.getElementById('btnAnterior');
    const next = document.getElementById('btnSiguiente');
    const send = document.getElementById('btnEnviar');
    const upload = document.getElementById('evidencias');
    const cameraInput = document.getElementById('evidenciaCamara');
    const cameraButton = document.getElementById('btnCamara');
    const fileList = document.getElementById('listaArchivos');
    let current = 0;

    // Mantiene en una sola colección los archivos elegidos y las fotos tomadas.
    // El campo enviado al servidor sigue siendo evidencias[].
    let selectedFiles = [];

    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    const maxSize = 5 * 1024 * 1024;
    const maxFiles = 5;

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
        window.scrollTo({
            top: form.getBoundingClientRect().top + window.scrollY - 110,
            behavior: 'smooth'
        });
    }

    function validateStep(index) {
        const fields = [...steps[index].querySelectorAll('input, select, textarea')];
        let valid = true;

        fields.forEach(field => {
            if (field.type === 'file' || field.type === 'hidden') return;
            if (!field.checkValidity()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        if (!valid) {
            const first = fields.find(field =>
                field.type !== 'file' &&
                field.type !== 'hidden' &&
                !field.checkValidity()
            );
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
        const files = selectedFiles.length;
        const lat = document.getElementById('latitud')?.value || '';
        const lng = document.getElementById('longitud')?.value || '';
        const locationText = lat && lng ? `${lat}, ${lng}` : 'No seleccionada';

        summary.innerHTML = `<strong>Resumen de tu denuncia</strong><ul class="mb-0 mt-2">
          <li><b>Asunto:</b> ${escapeHtml(title)}</li>
          <li><b>Categoría:</b> ${escapeHtml(category)}</li>
          <li><b>Institución:</b> ${escapeHtml(institution)}</li>
          <li><b>Fecha de los hechos:</b> ${escapeHtml(date)}</li>
          <li><b>Persona denunciada:</b> ${escapeHtml(person)}</li>
          <li><b>Geolocalización:</b> ${escapeHtml(locationText)}</li>
          <li><b>Archivos adjuntos:</b> ${files}</li>
        </ul>`;
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        }[char]));
    }

    function syncFilesToForm() {
        // DataTransfer permite enviar en evidencias[] tanto archivos elegidos
        // como fotografías obtenidas desde el selector de cámara.
        const transfer = new DataTransfer();
        selectedFiles.forEach(file => transfer.items.add(file));
        upload.files = transfer.files;
        renderFileList();
    }

    function renderFileList() {
        fileList.innerHTML = '';

        selectedFiles.forEach((file, index) => {
            const item = document.createElement('div');
            item.className = 'd-flex align-items-center justify-content-between gap-2';

            const description = document.createElement('span');
            description.textContent =
                `${file.name} — ${(file.size / 1024 / 1024).toFixed(2)} MB`;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger';
            remove.textContent = 'Quitar';
            remove.setAttribute('aria-label', `Quitar ${file.name}`);
            remove.addEventListener('click', () => {
                selectedFiles.splice(index, 1);
                syncFilesToForm();
            });

            item.append(description, remove);
            fileList.appendChild(item);
        });
    }

    function addFiles(files) {
        const incoming = [...files];
        if (selectedFiles.length + incoming.length > maxFiles) {
            alert(`Podés adjuntar hasta ${maxFiles} archivos en total.`);
            return false;
        }

        for (const file of incoming) {
            const validType = allowedTypes.includes(file.type);
            if (!validType || file.size > maxSize) {
                alert('Revisá los archivos: solo PDF, JPG o PNG de hasta 5 MB cada uno.');
                return false;
            }
        }

        selectedFiles.push(...incoming);
        syncFilesToForm();
        return true;
    }

    next.addEventListener('click', () => {
        if (validateStep(current) && current < steps.length - 1) {
            showStep(current + 1);
        }
    });

    previous.addEventListener('click', () => {
        showStep(Math.max(0, current - 1));
    });

    upload.addEventListener('change', () => {
        const chosen = [...upload.files];
        // Evita volver a duplicar los archivos al sincronizar el input.
        upload.value = '';
        addFiles(chosen);
        syncFilesToForm();
    });

    cameraButton.addEventListener('click', () => {
        if (selectedFiles.length >= maxFiles) {
            alert(`Podés adjuntar hasta ${maxFiles} archivos en total.`);
            return;
        }
        cameraInput.click();
    });

    cameraInput.addEventListener('change', () => {
        if (cameraInput.files.length) {
            const photo = cameraInput.files[0];
            // Algunos dispositivos entregan image/heic. El servidor actual
            // acepta JPG/PNG, por eso se valida el tipo antes de incorporarla.
            if (!['image/jpeg', 'image/png'].includes(photo.type)) {
                alert('La cámara debe proporcionar una fotografía JPG o PNG. Si tu teléfono genera HEIC, convertí la imagen a JPG antes de adjuntarla.');
            } else {
                addFiles([photo]);
            }
        }
        cameraInput.value = '';
    });

    form.addEventListener('submit', event => {
        if (!validateStep(3)) {
            event.preventDefault();
            return;
        }

        // Garantiza que el input enviado contenga la colección combinada.
        syncFilesToForm();
        send.disabled = true;
        send.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enviando...';
    });
});
