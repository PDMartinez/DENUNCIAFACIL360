document.addEventListener('DOMContentLoaded', () => {
    const mapElement = document.getElementById('mapaDenuncia');
    if (!mapElement || typeof L === 'undefined') return;

    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');
    const status = document.getElementById('geoEstado');
    const locateButton = document.getElementById('btnMiUbicacion');
    const clearButton = document.getElementById('btnLimpiarUbicacion');

    // Vista inicial general; el ciudadano puede seleccionar cualquier punto.
    const map = L.map(mapElement).setView([23.6345, -102.5528], 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
    }).addTo(map);

    let marker = null;

    function setLocation(lat, lng, sourceText) {
        const latitude = Number(lat);
        const longitude = Number(lng);
        if (!Number.isFinite(latitude) || !Number.isFinite(longitude) ||
            latitude < -90 || latitude > 90 || longitude < -180 || longitude > 180) {
            status.textContent = 'No se pudo establecer una coordenada válida.';
            return;
        }

        latInput.value = latitude.toFixed(7);
        lngInput.value = longitude.toFixed(7);

        if (!marker) {
            marker = L.marker([latitude, longitude], { draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const point = marker.getLatLng();
                setLocation(point.lat, point.lng, 'Marcador ajustado manualmente');
            });
        } else {
            marker.setLatLng([latitude, longitude]);
        }

        map.setView([latitude, longitude], 16);
        status.textContent = `${sourceText}. Latitud: ${latitude.toFixed(6)} · Longitud: ${longitude.toFixed(6)}`;
    }

    map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng, 'Ubicación seleccionada en el mapa'));

    locateButton.addEventListener('click', () => {
        if (!navigator.geolocation) {
            status.textContent = 'Tu navegador no permite obtener la ubicación. Podés marcar el punto manualmente.';
            return;
        }
        locateButton.disabled = true;
        locateButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Obteniendo ubicación...';
        navigator.geolocation.getCurrentPosition(
            position => {
                setLocation(position.coords.latitude, position.coords.longitude, 'Ubicación del dispositivo seleccionada');
                locateButton.disabled = false;
                locateButton.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
            },
            error => {
                const messages = {
                    1: 'No autorizaste el acceso a la ubicación.',
                    2: 'No se pudo determinar la ubicación del dispositivo.',
                    3: 'La solicitud de ubicación tardó demasiado.'
                };
                status.textContent = `${messages[error.code] || 'No se pudo obtener la ubicación.'} Podés marcar el punto manualmente.`;
                locateButton.disabled = false;
                locateButton.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
            },
            { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 }
        );
    });

    clearButton.addEventListener('click', () => {
        latInput.value = '';
        lngInput.value = '';
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        status.textContent = 'Ubicación quitada. Podés continuar sin seleccionar un punto.';
    });

    // Leaflet puede necesitar recalcular dimensiones al cambiar de paso del formulario.
    window.addEventListener('resize', () => map.invalidateSize());
    document.getElementById('btnSiguiente')?.addEventListener('click', () => {
        window.setTimeout(() => map.invalidateSize(), 100);
    });
    map.whenReady(() => map.invalidateSize());
});
