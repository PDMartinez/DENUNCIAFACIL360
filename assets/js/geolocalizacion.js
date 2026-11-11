
document.addEventListener('DOMContentLoaded', () => {

    const mapElement = document.getElementById('mapaDenuncia');

    // Verificar que exista el mapa y que Leaflet esté cargado
    if (!mapElement || typeof L === 'undefined') {
        console.error('No se encontró el mapa o Leaflet no está cargado.');
        return;
    }

    // Referencias a los elementos del formulario
    const latInput = document.getElementById('latitud');
    const lngInput = document.getElementById('longitud');
    const status = document.getElementById('geoEstado');

    const locateButton = document.getElementById('btnMiUbicacion');
    const clearButton = document.getElementById('btnLimpiarUbicacion');

    // ==========================================
    // 1. MARCADOR PERSONALIZADO ROSADO
    // ==========================================

    const iconoDenuncia = L.divIcon({

        className: 'df360-marker',

        html: `
            <svg
                width="38"
                height="48"
                viewBox="0 0 38 48"
                xmlns="http://www.w3.org/2000/svg">

                <path
                    d="M19 1C9 1 1 9 1 19c0 13 18 28 18 28s18-15 18-28C37 9 29 1 19 1Z"
                    fill="#BD168C"
                    stroke="#FFFFFF"
                    stroke-width="2"
                />

                <circle
                    cx="19"
                    cy="19"
                    r="7"
                    fill="#FFFFFF"
                />

            </svg>
        `,

        iconSize: [38, 48],

        iconAnchor: [19, 48],

        popupAnchor: [0, -45]

    });


    // ==========================================
    // 2. CONFIGURACIÓN DEL MAPA
    // ==========================================

    const map = L.map(mapElement).setView(
        [23.6345, -102.5528],
        5
    );

    // Cargar mapa de OpenStreetMap
    L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19,

            attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
        }
    ).addTo(map);


    // Variable para almacenar el marcador
    let marker = null;


    // ==========================================
    // 3. FUNCIÓN PARA ESTABLECER UBICACIÓN
    // ==========================================

    function setLocation(lat, lng, sourceText) {

        const latitude = Number(lat);
        const longitude = Number(lng);

        // Validar coordenadas
        if (
            !Number.isFinite(latitude) ||
            !Number.isFinite(longitude) ||
            latitude < -90 ||
            latitude > 90 ||
            longitude < -180 ||
            longitude > 180
        ) {

            status.textContent =
                'No se pudo establecer una coordenada válida.';

            return;
        }

        // Guardar coordenadas en los campos ocultos
        latInput.value = latitude.toFixed(7);
        lngInput.value = longitude.toFixed(7);


        // Crear o actualizar el marcador
        if (!marker) {

            marker = L.marker(
                [latitude, longitude],
                {
                    draggable: true,
                    icon: iconoDenuncia
                }
            ).addTo(map);

            // Evento al arrastrar el marcador
            marker.on('dragend', () => {

                const point = marker.getLatLng();

                setLocation(
                    point.lat,
                    point.lng,
                    'Marcador ajustado manualmente'
                );

            });

        } else {

            marker.setLatLng([latitude, longitude]);

        }


        // Centrar mapa en la ubicación seleccionada
        map.setView(
            [latitude, longitude],
            16
        );

        // Mostrar coordenadas seleccionadas
        status.textContent =
            `${sourceText}. Latitud: ${latitude.toFixed(6)} · Longitud: ${longitude.toFixed(6)}`;

    }


    // ==========================================
    // 4. SELECCIONAR UBICACIÓN CON UN CLIC
    // ==========================================

    map.on('click', (event) => {

        setLocation(
            event.latlng.lat,
            event.latlng.lng,
            'Ubicación seleccionada en el mapa'
        );

    });


    // ==========================================
    // 5. UTILIZAR UBICACIÓN ACTUAL
    // ==========================================

    locateButton.addEventListener('click', () => {

        // Verificar compatibilidad del navegador
        if (!navigator.geolocation) {

            status.textContent =
                'Tu navegador no permite obtener la ubicación. Podés marcar el punto manualmente.';

            return;
        }


        // Deshabilitar botón mientras se obtiene ubicación
        locateButton.disabled = true;

        locateButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Obteniendo ubicación...';


        // Solicitar ubicación al navegador
        navigator.geolocation.getCurrentPosition(

            // Ubicación obtenida correctamente
            (position) => {

                setLocation(
                    position.coords.latitude,
                    position.coords.longitude,
                    'Ubicación del dispositivo seleccionada'
                );

                locateButton.disabled = false;

                locateButton.innerHTML =
                    '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';

            },


            // Error al obtener ubicación
            (error) => {

                const messages = {

                    1: 'No autorizaste el acceso a la ubicación.',

                    2: 'No se pudo determinar la ubicación del dispositivo.',

                    3: 'La solicitud de ubicación tardó demasiado.'

                };

                status.textContent =
                    `${messages[error.code] || 'No se pudo obtener la ubicación.'} Podés marcar el punto manualmente.`;


                locateButton.disabled = false;

                locateButton.innerHTML =
                    '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';

            },


            // Opciones de geolocalización
            {
                enableHighAccuracy: true,
                timeout: 12000,
                maximumAge: 60000
            }

        );

    });


    // ==========================================
    // 6. QUITAR UBICACIÓN SELECCIONADA
    // ==========================================

    clearButton.addEventListener('click', () => {

        // Limpiar campos ocultos
        latInput.value = '';
        lngInput.value = '';


        // Eliminar marcador del mapa
        if (marker) {

            map.removeLayer(marker);

            marker = null;

        }


        // Actualizar mensaje
        status.textContent =
            'Ubicación quitada. Podés continuar sin seleccionar un punto.';

    });


    // ==========================================
    // 7. AJUSTAR TAMAÑO DEL MAPA
    // ==========================================

    window.addEventListener('resize', () => {

        map.invalidateSize();

    });


    // Ajustar mapa al cambiar de paso del formulario
    document
        .getElementById('btnSiguiente')
        ?.addEventListener('click', () => {

            window.setTimeout(() => {

                map.invalidateSize();

            }, 100);

        });


    // Ajuste inicial
    map.whenReady(() => {

        map.invalidateSize();

    });

});
