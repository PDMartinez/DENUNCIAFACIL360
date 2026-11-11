<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Plataforma ciudadana para registrar y consultar denuncias.">
    <title>DenunciaFácil 360 | Plataforma ciudadana</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="assets/css/estilos.css" rel="stylesheet">
</head>

<body>
    <header class="site-header">
        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand brand" href="index.php">
                    <span class="brand-mark"><i class="bi bi-shield-check"></i></span>
                    <span><strong>DenunciaFácil <b>360</b></strong><small>Plataforma ciudadana de denuncias</small></span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu"><span class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="menu">
                    <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                        <li class="nav-item"><a class="nav-link active" href="index.php"><i class="bi bi-house-door"></i> Inicio</a></li>
                        <li class="nav-item"><a class="nav-link" href="#como-funciona"><i class="bi bi-info-circle"></i> ¿Cómo funciona?</a></li>
                        <li class="nav-item"><a class="nav-link" href="#caracteristicas"><i class="bi bi-bar-chart"></i> Características</a></li>
                        <li class="nav-item"><a class="nav-link" href="#ayuda"><i class="bi bi-question-circle"></i> Ayuda</a></li>
                        <li class="nav-item"><a class="btn btn-admin" href="admin/login.php"><i class="bi bi-person-lock"></i> Acceso administrativo</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7 text-center text-lg-start">
                        <span class="eyebrow"><i class="bi bi-megaphone"></i> Tu participación hace la diferencia</span>
                        <h1>Una ciudadanía informada construye un <span>futuro transparente.</span></h1>
                        <p class="hero-text">Un espacio para comunicar posibles hechos de corrupción o faltas administrativas y consultar el avance de tu denuncia de manera sencilla.</p>
                        <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start">
                            <a class="btn btn-primary-custom btn-lg" href="denuncias/nueva.php"><i class="bi bi-file-earmark-plus"></i> Realizar una denuncia</a>
                            <a class="btn btn-outline-custom btn-lg" href="seguimiento/consultar.php"><i class="bi bi-search"></i> Consultar seguimiento</a>
                        </div>
                        <p class="hero-note"><i class="bi bi-shield-lock-fill"></i> No necesitás crear una cuenta para presentar una denuncia.</p>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-art">
                            <div class="art-circle"></div>
                            <div class="document-card">
                                <div class="doc-top"><span></span><span></span><span></span><small>Formulario ciudadano</small></div>
                                <div class="doc-icon"><i class="bi bi-file-earmark-text"></i></div>
                                <div class="doc-line wide"></div>
                                <div class="doc-line"></div>
                                <div class="doc-line short"></div>
                                <div class="doc-check"><i class="bi bi-check-lg"></i></div>
                                <div class="doc-caption"><strong>Tu voz cuenta</strong><small>Participá por una gestión transparente</small></div>
                            </div>
                            <div class="floating-note"><i class="bi bi-lock-fill"></i> Seguimiento privado</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="search-strip">
            <div class="container">
                <form id="formBusqueda" class="search-form">
                    <strong><i class="bi bi-compass"></i> ¿Qué necesitás hacer?</strong>
                    <input id="buscarSeccion" class="form-control" type="search" placeholder="Buscar información en la plataforma..." aria-label="Buscar">
                    <button aria-label="Buscar"><i class="bi bi-arrow-right"></i></button>
                </form>
                <p id="mensajeBusqueda" class="search-message" aria-live="polite"></p>
            </div>
        </section>

        <section class="section-space">
            <div class="container">
                <div class="section-heading text-center">
                    <span class="kicker">PLATAFORMA CIUDADANA</span>
                    <h2>¿Cómo podemos ayudarte?</h2>
                    <p>Elegí una opción para comenzar.</p>
                </div>
                <div class="row g-4 mt-2">
                    <div class="col-md-4"><a class="action-card featured" href="denuncias/nueva.php">
                            <div class="action-icon"><i class="bi bi-file-earmark-plus"></i></div>
                            <h3>Realizar una denuncia</h3>
                            <p>Informá sobre posibles hechos de corrupción o faltas administrativas.</p><span>Comenzar <i class="bi bi-arrow-right"></i></span>
                        </a></div>
                    <div class="col-md-4"><a class="action-card" href="seguimiento/consultar.php">
                            <div class="action-icon"><i class="bi bi-search"></i></div>
                            <h3>Consultar seguimiento</h3>
                            <p>Conocé el estado de tu denuncia utilizando tu código y clave privada.</p><span>Consultar <i class="bi bi-arrow-right"></i></span>
                        </a></div>
                    <div class="col-md-4"><a class="action-card" href="#como-funciona">
                            <div class="action-icon"><i class="bi bi-chat-square-text"></i></div>
                            <h3>¿Cómo funciona?</h3>
                            <p>Conocé las etapas del proceso y cómo se protege la información.</p><span>Conocer más <i class="bi bi-arrow-right"></i></span>
                        </a></div>
                </div>
            </div>
        </section>

        <section class="features section-space" id="caracteristicas">
            <div class="container">
                <div class="section-heading text-center"><span class="kicker">NUESTRO COMPROMISO</span>
                    <h2>Una plataforma pensada para la ciudadanía</h2>
                    <p>Herramientas para facilitar la comunicación y la gestión responsable de la información.</p>
                </div>
                <div class="row g-4 mt-2">
                    <div class="col-sm-6 col-lg-3">
                        <article class="feature"><i class="bi bi-incognito"></i>
                            <h3>Denuncia sin cuenta</h3>
                            <p>Podés presentar una denuncia sin crear un usuario ni iniciar sesión.</p>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <article class="feature"><i class="bi bi-shield-lock"></i>
                            <h3>Información protegida</h3>
                            <p>Se aplicarán medidas de seguridad para resguardar denuncias y evidencias.</p>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <article class="feature"><i class="bi bi-clipboard2-check"></i>
                            <h3>Seguimiento privado</h3>
                            <p>Consultá las actualizaciones con el código y la clave entregados al registrar.</p>
                        </article>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <article class="feature"><i class="bi bi-graph-up-arrow"></i>
                            <h3>Análisis de información</h3>
                            <p>La plataforma contempla futuras herramientas de análisis de datos públicos.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-space" id="como-funciona">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-5"><span class="kicker">UN PROCESO SENCILLO</span>
                        <h2 class="section-title">Presentar una denuncia es fácil</h2>
                        <p class="muted">El sistema te guiará paso a paso. No necesitás registrarte ni proporcionar datos personales para comenzar.</p>
                        <a href="denuncias/nueva.php" class="btn btn-primary-custom mt-3">Iniciar una denuncia <i class="bi bi-arrow-right"></i></a>
                    </div>
                    <div class="col-lg-7">
                        <div class="steps">
                            <div class="step"><b>01</b>
                                <div>
                                    <h3>Completá el formulario</h3>
                                    <p>Describí los hechos, la institución involucrada y, si los conocés, los datos de la persona denunciada.</p>
                                </div>
                            </div>
                            <div class="step"><b>02</b>
                                <div>
                                    <h3>Adjuntá evidencias</h3>
                                    <p>Podés incorporar documentos o imágenes que ayuden a contextualizar la información.</p>
                                </div>
                            </div>
                            <div class="step"><b>03</b>
                                <div>
                                    <h3>Guardá tus credenciales</h3>
                                    <p>Al finalizar recibirás un código y una clave privada para consultar el seguimiento.</p>
                                </div>
                            </div>
                            <div class="step"><b>04</b>
                                <div>
                                    <h3>Consultá las actualizaciones</h3>
                                    <p>Ingresá tus credenciales para revisar la información disponible sobre el estado.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="help-section" id="ayuda">
            <div class="container">
                <div class="help-box">
                    <div><span class="kicker">¿NECESITÁS ORIENTACIÓN?</span>
                        <h2>Estamos para ayudarte</h2>
                        <p>Conocé cómo utilizar la plataforma y qué información conviene incluir en una denuncia.</p>
                    </div>
                    <a href="#como-funciona" class="btn btn-light">Ver cómo funciona <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <a class="navbar-brand brand footer-brand" href="index.php"><span class="brand-mark"><i class="bi bi-shield-check"></i></span><span><strong>DenunciaFácil <b>360</b></strong><small>Plataforma ciudadana de denuncias</small></span></a>
                <div class="footer-links"><a href="#como-funciona">Cómo funciona</a><a href="#caracteristicas">Características</a><a href="#ayuda">Ayuda</a></div>
            </div>
            <hr>
            <div class="footer-bottom"><span>© <?php echo date('Y'); ?> DenunciaFácil 360. Proyecto de plataforma ciudadana.</span><span>La recepción de información no implica una determinación sobre la veracidad de los hechos.</span></div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>

</html>