<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$id = (int) ($_GET['id'] ?? 0);

$blog = null;
$contenidoHtml = '';
if ($id > 0) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, titulo, autor, resumen, contenido, imagen, created_at FROM blogs WHERE id = :id AND estado = 'P' LIMIT 1");
    $stmt->execute([':id' => $id]);
    $blog = $stmt->fetch();
    if ($blog) {
        $contenidoHtml = sanitizeRichHtml((string) $blog['contenido']);
    }
}
?>
<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Detalle Blog | LUMA</title>
    <meta name="description" content="Detalle de blog de LUMA">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="56x56" href="assets/images/fav-icon/fav.png">
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/animate.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/animated-text.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/flaticon.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/meanmenu.min.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/owl.transitions.css" type="text/css" media="all">
    <link rel="stylesheet" href="venobox/venobox.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/all.min.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/theme-default.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css" type="text/css" media="all">
    <link rel="stylesheet" href="style.css" type="text/css" media="all">
    <link rel="stylesheet" href="assets/css/responsive.css" type="text/css" media="all">
    <script src="assets/js/vendor/modernizr-3.5.0.min.js"></script>
    <style>
        .header-menu ul li { position: relative; }
        .header-menu ul li .sub-menu { position: absolute; top: 100%; left: 0; min-width: 260px; display: none; background: #fff; z-index: 99; }
        .header-menu ul li:hover > .sub-menu { display: block; }
        .header-menu ul li .sub-menu li .sub-menu { top: 0; left: 100%; }
        .header-menu ul li .sub-menu li:hover > .sub-menu { display: block; }
        .blog-body { max-width: 900px; margin: 40px auto; }
        .blog-cover { width: 100%; border-radius: 10px; margin-bottom: 18px; object-fit: cover; max-height: 420px; }
        .blog-text { font-size: 17px; line-height: 1.75; color: #343434; }
    </style>
</head>
<body>
<div class="loader-wrapper">
    <div class="loader"></div>
    <div class="loder-section left-section"></div>
    <div class="loder-section right-section"></div>
</div>

<div class="header-area header_bg" id="sticky-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3">
                <div class="logo">
                    <a href="index.html"><img src="assets/images/logo-luma-1.png" alt="logo luma" style="width: 200px;"></a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="header-menu">
                    <ul>
                        <li><a href="index.html">Inicio</a></li>
                        <li><a href="#">Nosotros +</a>
                            <ul class="sub-menu">
                                <li><a href="identidad-institucional.html">• Identidad Institucional</a></li>
                                <li><a href="cultura-luma.html">• Cultura LUMA</a></li>
                            </ul>
                        </li>
                        <li><a href="#">Ecosistema de Servicios +</a>
                            <ul class="sub-menu">
                                <li><a href="experiencia.html">• Nuestra experiencia</a></li>
                                <li><a href="#">• Servicios +</a>
                                    <ul class="sub-menu">
                                        <li><a href="servicio-consultoria-competencias.html">• Consultoría en competencias laborales</a></li>
                                        <li><a href="servicio-formacion-abp.html">• Formación basada en ABP</a></li>
                                        <li><a href="servicio-mentoring.html">• Mentoring y acompañamiento profesional</a></li>
                                        <li><a href="servicio-psicoeducativa.html">• Formación psicoeducativa</a></li>
                                        <li><a href="servicio-investigacion.html">• Investigación aplicada</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <li><a href="blog.php">Blog</a></li>
                    </ul>
                    <div class="header-menu-button">
                        <a href="contact.html">Contáctenos<i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mobile-menu-area sticky d-sm-block d-md-block d-lg-none ">
    <div class="mobile-menu">
        <nav class="header-menu">
            <ul class="nav_scroll">
                <li><a href="index.html">Inicio</a></li>
                <li><a href="#">Nosotros +</a>
                    <ul class="sub-menu">
                        <li><a href="identidad-institucional.html">• Identidad Institucional</a></li>
                        <li><a href="cultura-luma.html">• Cultura LUMA</a></li>
                    </ul>
                </li>
                <li><a href="#">Ecosistema de Servicios +</a>
                    <ul class="sub-menu">
                        <li><a href="experiencia.html">• Nuestra experiencia</a></li>
                        <li><a href="servicio-consultoria-competencias.html">Consultoría en competencias laborales</a></li>
                        <li><a href="servicio-formacion-abp.html">Formación basada en ABP</a></li>
                        <li><a href="servicio-mentoring.html">Mentoring y acompañamiento profesional</a></li>
                        <li><a href="servicio-psicoeducativa.html">Formación psicoeducativa</a></li>
                        <li><a href="servicio-investigacion.html">Investigación aplicada</a></li>
                    </ul>
                </li>
                <li><a href="blog.php">Blog</a></li>
                <li><a href="contact.html">Contacto</a></li>
            </ul>
        </nav>
    </div>
</div>

<div class="breadcumb-area d-flex align-items-center">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="breacumb-content">
                    <div class="breadcumb-title"><h1>Detalle Blog</h1></div>
                    <div class="breadcumb-content-text"><a href="index.html">HOME</a><i class="fas fa-angle-right"></i><a href="blog.php">Blog</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container blog-body">
    <?php if (!$blog): ?>
        <h2>Artículo no disponible</h2>
        <p>Este artículo no existe o no está publicado.</p>
        <p><a href="blog.php">Volver al blog</a></p>
    <?php else: ?>
        <p><strong>Fecha:</strong> <?php echo h(formatFechaEs((string) $blog['created_at'])); ?> | <strong>Autor:</strong> <?php echo h((string) ($blog['autor'] ?: 'LUMA')); ?></p>
        <h1><?php echo h((string) $blog['titulo']); ?></h1>
        <?php if (!empty($blog['imagen'])): ?>
            <img class="blog-cover" src="<?php echo h((string) $blog['imagen']); ?>" alt="Imagen blog">
        <?php endif; ?>
        <?php if (!empty($blog['resumen'])): ?>
            <p><em><?php echo h((string) $blog['resumen']); ?></em></p>
        <?php endif; ?>
        <div class="blog-text"><?php echo $contenidoHtml; ?></div>
    <?php endif; ?>
</div>

<div class="footer-area">
    <div class="container">
        <div class="row footer_bg">
            <div class="col-lg-5 col-md-6">
                <div class="footer-widget-item">
                    <div class="footer-logo">
                        <a href="index.html"><img src="assets/images/logo-luma-footer-200px.png" alt="logo" style="margin-left: -10px;"></a>
                    </div>
                    <div class="footer-widget-description">
                        <p>En LUMA Gestión Humana y Mentoring trabajamos como una red profesional articulada por un núcleo especializado en gestión humana y mentoring.</p>
                    </div>
                    <div class="footer-widget-follow">
                        <ul>
                            <li><a target="_bank" href="https://www.linkedin.com/in/lucia-mar%C3%ADa-mora-goyes-a5466734/"><i class="fab fa-linkedin-in"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="footer-widget-item">
                    <div class="footer-widget-title">
                        <h2>Enlaces de interés</h2>
                    </div>
                    <div class="company-widget-info">
                        <ul>
                            <li><a href="identidad-institucional.html">Identidad Institucional</a></li>
                            <li><a href="cultura-luma.html">Cultura LUMA</a></li>
                            <li><a href="experiencia.html">Nuestra experiencia</a></li>
                            <li><a href="contact.html">Contacto</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="footer-widget-item">
                    <div class="footer-widget-title">
                        <h2>Contacto</h2>
                    </div>
                    <div class="company-widget-address">
                        <div class="widget-address-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div class="comapny-widget-desc">
                            <p>gh.mentoring@lumagestion.com</p>
                        </div>
                    </div>
                    <div class="company-widget-address">
                        <div class="widget-address-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <div class="comapny-widget-desc">
                            <p>Guayaquil - Ecuador</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="footer-bottom-content">
                <p>© 2026 · <span>LUMA</span> · All Rights Reserved. <a target="_bank" href="https://crisalidadigital.es/">Powered by Crisalida</a></p>
            </div>
        </div>
    </div>
</div>

<div class="scroll-area">
    <div class="top-wrap">
        <div class="go-top-btn-wraper">
            <div class="go-top go-top-button">
                <i class="bi bi-chevron-double-up"></i>
                <i class="bi bi-chevron-double-up"></i>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/vendor/jquery-3.6.2.min.js"></script>
<script src="assets/js/popper.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script src="assets/js/owl.carousel.min.js"></script>
<script src="assets/js/jquery.counterup.min.js"></script>
<script src="assets/js/waypoints.min.js"></script>
<script src="assets/js/wow.js"></script>
<script src="assets/js/imagesloaded.pkgd.min.js"></script>
<script src="venobox/venobox.js"></script>
<script src="assets/js/animated-text.js"></script>
<script src="venobox/venobox.min.js"></script>
<script src="assets/js/isotope.pkgd.min.js"></script>
<script src="assets/js/jquery.meanmenu.js"></script>
<script src="assets/js/jquery.scrollUp.js"></script>
<script src="assets/js/jquery.barfiller.js"></script>
<script src="assets/js/theme.js"></script>
</body>
</html>
