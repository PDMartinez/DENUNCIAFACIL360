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
    let geoDialog = null;

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

    function closeGeoDialog() {
        if (geoDialog) {
            geoDialog.remove();
            geoDialog = null;
        }
    }

    function showGeoDialog() {
        if (geoDialog) return;

        geoDialog = document.createElement('div');
        geoDialog.className = 'geo-permission-overlay';
        geoDialog.setAttribute('role', 'presentation');
        geoDialog.innerHTML = `
            <section class="geo-permission-dialog" role="dialog"
                     aria-modal="true" aria-labelledby="geoDialogTitle"
                     aria-describedby="geoDialogDescription">
                <button type="button" class="geo-dialog-close"
                        aria-label="Cerrar ventana">&times;</button>
                <div class="geo-dialog-icon"><i class="bi bi-geo-alt-fill"></i></div>
                <h2 id="geoDialogTitle">¿Querés compartir tu ubicación?</h2>
                <p id="geoDialogDescription">
                    Podemos marcar automáticamente en el mapa el lugar donde ocurrieron los hechos.
                    Tu ubicación es opcional y podés mover el marcador o elegir otro punto manualmente.
                </p>
                <div class="geo-dialog-note">
                    <i class="bi bi-shield-check"></i>
                    <span>No necesitamos conocer tu identidad. La ubicación se utiliza únicamente como referencia para esta denuncia.</span>
                </div>
                <button type="button" class="geo-dialog-allow">
                    <i class="bi bi-crosshair"></i> Permitir ubicación
                </button>
                <button type="button" class="geo-dialog-cancel">Ahora no</button>
                <p class="geo-dialog-help">
                    Al continuar, tu navegador mostrará su propia solicitud de permiso.
                </p>
            </section>
        `;

        if (!document.getElementById('geoPermissionStyles')) {
            const style = document.createElement('style');
            style.id = 'geoPermissionStyles';
            style.textContent = `
                .geo-permission-overlay {
                    position: fixed; inset: 0; z-index: 10000;
                    display: flex; align-items: center; justify-content: center;
                    padding: 18px; background: rgba(28, 15, 30, .58);
                }
                .geo-permission-dialog {
                    position: relative; width: 100%; max-width: 430px;
                    padding: 30px 24px 22px; border-radius: 20px;
                    background: #fff; color: #35213b; text-align: center;
                    box-shadow: 0 18px 55px rgba(0,0,0,.24);
                    font-family: inherit;
                }
                .geo-dialog-close {
                    position: absolute; top: 10px; right: 14px; border: 0;
                    background: transparent; color: #806d83; font-size: 28px;
                    line-height: 1; cursor: pointer;
                }
                .geo-dialog-icon {
                    width: 64px; height: 64px; margin: 0 auto 15px;
                    display: grid; place-items: center; border-radius: 50%;
                    background: #f9e8f4; color: #c21891; font-size: 29px;
                }
                .geo-permission-dialog h2 {
                    margin: 0 0 10px; font-size: 21px; font-weight: 700;
                }
                .geo-permission-dialog p {
                    color: #76677a; font-size: 14px; line-height: 1.55;
                }
                .geo-dialog-note {
                    display: flex; gap: 10px; align-items: flex-start;
                    margin: 18px 0; padding: 12px; border-radius: 12px;
                    background: #fdf2fa; color: #66536a; text-align: left;
                    font-size: 13px; line-height: 1.45;
                }
                .geo-dialog-note i { color: #c21891; font-size: 18px; }
                .geo-dialog-allow, .geo-dialog-cancel {
                    width: 100%; border-radius: 10px; padding: 12px 16px;
                    font: inherit; font-size: 14px; font-weight: 600; cursor: pointer;
                }
                .geo-dialog-allow {
                    border: 1px solid #c21891; background: #c21891; color: #fff;
                }
                .geo-dialog-allow:hover { background: #a9157e; }
                .geo-dialog-cancel {
                    margin-top: 8px; border: 1px solid #ead9e7;
                    background: #fff; color: #66536a;
                }
                .geo-dialog-help { margin: 14px 0 0 !important; font-size: 11px !important; }
                @media (max-width: 480px) {
                    .geo-permission-dialog { padding: 28px 19px 19px; }
                    .geo-permission-dialog h2 { font-size: 19px; }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(geoDialog);

        const allowButton = geoDialog.querySelector('.geo-dialog-allow');
        const cancelButton = geoDialog.querySelector('.geo-dialog-cancel');
        const closeButton = geoDialog.querySelector('.geo-dialog-close');

        allowButton.addEventListener('click', () => {
            closeGeoDialog();
            requestDeviceLocation();
        });
        cancelButton.addEventListener('click', closeGeoDialog);
        closeButton.addEventListener('click', closeGeoDialog);
        geoDialog.addEventListener('click', event => {
            if (event.target === geoDialog) closeGeoDialog();
        });

        const escapeHandler = event => {
            if (event.key === 'Escape' && geoDialog) {
                closeGeoDialog();
                document.removeEventListener('keydown', escapeHandler);
            }
        };
        document.addEventListener('keydown', escapeHandler);
        closeButton.focus();
    }

    function requestDeviceLocation() {
        if (!window.isSecureContext) {
            status.textContent = 'El navegador bloquea la ubicación porque esta página no utiliza una conexión segura (HTTPS). En localhost suele estar permitido; desde otro dispositivo necesitás acceder mediante HTTPS.';
            return;
        }

        if (!navigator.geolocation) {
            status.textContent = 'Tu navegador no permite obtener la ubicación. Podés marcar el punto manualmente.';
            return;
        }

        locateButton.disabled = true;
        locateButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Obteniendo ubicación...';

        navigator.geolocation.getCurrentPosition(
            position => {
                setLocation(
                    position.coords.latitude,
                    position.coords.longitude,
                    'Ubicación del dispositivo seleccionada'
                );
                locateButton.disabled = false;
                locateButton.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
            },
            error => {
                const messages = {
                    1: 'El permiso de ubicación fue denegado o está bloqueado en el navegador.',
                    2: 'El dispositivo no pudo determinar la ubicación. Verificá que la ubicación/GPS esté activada.',
                    3: 'La solicitud de ubicación tardó demasiado.'
                };
                let extra = ' Podés marcar el punto manualmente.';
                if (error.code === 1) {
                    extra = ' Revisá los permisos de ubicación del sitio en el navegador y volvé a intentarlo. También podés marcar el punto manualmente.';
                }
                status.textContent = `${messages[error.code] || 'No se pudo obtener la ubicación.'}${extra}`;
                locateButton.disabled = false;
                locateButton.innerHTML = '<i class="bi bi-crosshair"></i> Usar mi ubicación actual';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
        );
    }

    map.on('click', event =>
        setLocation(event.latlng.lat, event.latlng.lng, 'Ubicación seleccionada en el mapa')
    );

    // El botón existente abre primero una explicación sencilla; la autorización
    // real sigue siendo solicitada por el navegador al pulsar "Permitir ubicación".
    locateButton.addEventListener('click', showGeoDialog);

    clearButton.addEventListener('click', () => {
        latInput.value = '';
        lngInput.value = '';
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        status.textContent = 'Ubicación quitada. Podés continuar sin seleccionar un punto.';
    });

    window.addEventListener('resize', () => map.invalidateSize());
    document.getElementById('btnSiguiente')?.addEventListener('click', () => {
        window.setTimeout(() => map.invalidateSize(), 100);
    });
    map.whenReady(() => map.invalidateSize());
});
