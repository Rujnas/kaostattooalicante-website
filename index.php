<?php
// ---------------------------------------------------------------------------
// Server-side SEO: resolve page + language from the URL and expose per-page
// metadata (title, description, canonical, hreflang) before any HTML is sent.
// The SPA in script.js still runs and re-applies everything on the client;
// this block only guarantees the served HTML is correct for crawlers/scrapers.
// ---------------------------------------------------------------------------
$BASE = 'https://kaostattooalicante.es';

// Raw path without query string (the URL #hash never reaches the server)
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = '/' . trim(urldecode($path), '/');
if ($path !== '/') { $path .= '/'; }

// Language detection + strip the /en prefix
$lang = 'es';
if ($path === '/en/' || strpos($path, '/en/') === 0) {
    $lang = 'en';
    $path = substr($path, 3);            // drop "/en"
    if ($path === '' || $path === false) { $path = '/'; }
    if ($path[0] !== '/') { $path = '/' . $path; }
}

// Per-page URL slug by language (segment only; '' = home).
// 'fineline' is the /estilos/ (styles) landing page id.
$SLUGS = [
    'home'            => ['es' => '',                'en' => ''],
    'tatuadores'      => ['es' => 'equipo',          'en' => 'team'],
    'anilladora'      => ['es' => 'anilladora',      'en' => 'piercer'],
    'tatuajes'        => ['es' => 'tatuajes',        'en' => 'tattoos'],
    'piercings'       => ['es' => 'piercings',       'en' => 'piercings'],
    'dibujos-cuadros' => ['es' => 'dibujos-cuadros', 'en' => 'art'],
    'contacto'        => ['es' => 'contacto',        'en' => 'contact'],
    'blog'            => ['es' => 'blog',            'en' => 'blog'],
    'fineline'        => ['es' => 'estilos',         'en' => 'styles'],
];

// Resolve the current path (already without /en prefix) to a page id
$pageId = 'home';
foreach ($SLUGS as $pid => $slugs) {
    $seg = $slugs[$lang];
    $candidate = '/' . ($seg !== '' ? $seg . '/' : '');
    if ($candidate === $path) { $pageId = $pid; break; }
}

// Absolute URLs for this page in each language (for canonical / hreflang)
$segEs = $SLUGS[$pageId]['es'];
$segEn = $SLUGS[$pageId]['en'];
$esUrl = $BASE . '/' . ($segEs !== '' ? $segEs . '/' : '');
$enUrl = $BASE . '/en/' . ($segEn !== '' ? $segEn . '/' : '');
$canonical = ($lang === 'en') ? $enUrl : $esUrl;

// Per-page SEO copy (es / en). Description ~150-160 chars.
$SEO = [
    'home' => [
        'es' => ['title' => 'Kaos Tattoo | Tatuajes y Piercings en Alicante',
                 'desc'  => 'Estudio de tatuajes y piercings en Alicante. Equipo profesional especializado en múltiples estilos. Cuéntanos tu idea y te asesoramos.'],
        'en' => ['title' => 'Kaos Tattoo | Tattoo & Piercing Studio in Alicante',
                 'desc'  => 'Tattoo and piercing studio in Alicante. Professional team specialised in many styles. Tell us your idea and we will guide you.'],
    ],
    'tatuajes' => [
        'es' => ['title' => 'Tatuajes en Alicante | Kaos Tattoo',
                 'desc'  => 'Estilos, artistas, proceso y cuidados de tatuaje en Kaos Tattoo Alicante. Diseño personalizado y asesoramiento en cada proyecto.'],
        'en' => ['title' => 'Tattoos in Alicante | Kaos Tattoo',
                 'desc'  => "Kaos Tattoo's artists, styles, process and tattoo aftercare in Alicante. Custom design and guidance for every project."],
    ],
    'piercings' => [
        'es' => ['title' => 'Piercings en Alicante | Kaos Tattoo',
                 'desc'  => 'Piercings profesionales en Alicante con material de calidad e higiene certificada. Asesoramiento, colocación y cuidados en Kaos Tattoo.'],
        'en' => ['title' => 'Piercings in Alicante | Kaos Tattoo',
                 'desc'  => 'Professional piercings in Alicante with quality jewellery and certified hygiene. Advice, placement and aftercare at Kaos Tattoo.'],
    ],
    'tatuadores' => [
        'es' => ['title' => 'Equipo de tatuadores en Alicante | Kaos Tattoo',
                 'desc'  => 'Conoce al equipo de tatuadores de Kaos Tattoo en Alicante: Tailor, Carrie y más artistas especializados en distintos estilos.'],
        'en' => ['title' => 'Our tattoo team in Alicante | Kaos Tattoo',
                 'desc'  => 'Meet the Kaos Tattoo tattoo artists in Alicante: Tailor, Carrie and more, each specialised in different styles.'],
    ],
    'anilladora' => [
        'es' => ['title' => 'Anilladora profesional en Alicante | Kaos Tattoo',
                 'desc'  => 'La Greka, anilladora profesional en Kaos Tattoo Alicante. Piercings seguros con asesoramiento personalizado y máxima higiene.'],
        'en' => ['title' => 'Professional piercer in Alicante | Kaos Tattoo',
                 'desc'  => 'La Greka, professional piercer at Kaos Tattoo Alicante. Safe piercings with personalised advice and the highest hygiene standards.'],
    ],
    'dibujos-cuadros' => [
        'es' => ['title' => 'Dibujos y cuadros personalizados | Kaos Tattoo Alicante',
                 'desc'  => 'Cuadros y dibujos hechos a mano por Kike en Kaos Tattoo Alicante. Piezas únicas y encargos personalizados, incluidos retratos de mascotas.'],
        'en' => ['title' => 'Custom drawings and paintings | Kaos Tattoo Alicante',
                 'desc'  => 'Handmade paintings and drawings by Kike at Kaos Tattoo Alicante. Unique pieces and custom commissions, including pet portraits.'],
    ],
    'contacto' => [
        'es' => ['title' => 'Contacto | Kaos Tattoo Alicante',
                 'desc'  => 'Contacta con Kaos Tattoo en Alicante. Cuéntanos tu idea de tatuaje o piercing y te responderemos lo antes posible.'],
        'en' => ['title' => 'Contact | Kaos Tattoo Alicante',
                 'desc'  => 'Get in touch with Kaos Tattoo in Alicante. Tell us your tattoo or piercing idea and we will get back to you as soon as possible.'],
    ],
    'blog' => [
        'es' => ['title' => 'Blog | Kaos Tattoo Alicante',
                 'desc'  => 'Novedades, consejos y últimas publicaciones de Kaos Tattoo, estudio de tatuajes y piercings en Alicante.'],
        'en' => ['title' => 'Blog | Kaos Tattoo Alicante',
                 'desc'  => 'News, tips and the latest posts from Kaos Tattoo, a tattoo and piercing studio in Alicante.'],
    ],
    'fineline' => [
        'es' => ['title' => 'Estilos de tatuaje | Kaos Tattoo Alicante',
                 'desc'  => 'Descubre los estilos de tatuaje de Kaos Tattoo Alicante: fine line, realismo, japonés, blackwork, lettering y muchos más.'],
        'en' => ['title' => 'Tattoo styles | Kaos Tattoo Alicante',
                 'desc'  => 'Discover the tattoo styles at Kaos Tattoo Alicante: fine line, realism, Japanese, blackwork, lettering and many more.'],
    ],
];

$meta   = $SEO[$pageId][$lang] ?? $SEO['home'][$lang];
$title  = $meta['title'];
$desc   = $meta['desc'];
$htmlLang = $lang;
$ogLocale = ($lang === 'en') ? 'en_US' : 'es_ES';
$ogImage  = $BASE . '/images/logo_perro-nobackground.webp';

// Helper: emit " active" for the page div that matches the current URL
function pageActive($id, $current) {
    return $id === $current ? ' active' : '';
}
?>
<!DOCTYPE html>
<html lang="<?php echo $htmlLang; ?>">
<head>
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-W7X5FVW3');</script>
    <!-- End Google Tag Manager -->

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0LDEKEJD75"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-0LDEKEJD75');
    </script>

    <meta charset="UTF-8">
    <base href="/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Skip the intro overlay if already shown this session (avoids flash) -->
    <script>try{if(sessionStorage.getItem('kaosIntroShown')){document.documentElement.classList.add('intro-seen');}}catch(e){}</script>
    <title><?php echo htmlspecialchars($title, ENT_QUOTES); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($desc, ENT_QUOTES); ?>">
    <link rel="canonical" href="<?php echo $canonical; ?>">

    <!-- Language alternates -->
    <link rel="alternate" hreflang="es" href="<?php echo $esUrl; ?>">
    <link rel="alternate" hreflang="en" href="<?php echo $enUrl; ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo $esUrl; ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?php echo $ogLocale; ?>">
    <meta property="og:url" content="<?php echo $canonical; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($desc, ENT_QUOTES); ?>">
    <meta property="og:image" content="<?php echo $ogImage; ?>">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $canonical; ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($title, ENT_QUOTES); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($desc, ENT_QUOTES); ?>">
    <meta name="twitter:image" content="<?php echo $ogImage; ?>">
    
    <!-- Structured Data for Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "TattooParlor",
        "name": "Kaos Tattoo",
        "url": "https://kaostattooalicante.es",
        "logo": "https://kaostattooalicante.es/images/logo_perro-nobackground.webp",
        "image": "https://kaostattooalicante.es/images/logo_perro-nobackground.webp",
        "description": "Estudio de tatuajes y piercings en Alicante. Equipo profesional especializado en diversos estilos.",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Pintor Velázquez 17",
            "addressLocality": "Alicante",
            "postalCode": "03004",
            "addressCountry": "ES"
        },
        "telephone": "+34618710976",
        "openingHours": "Mo-Sa 11:00-14:00, Mo-Sa 16:30-20:00",
        "priceRange": "$$",
        "sameAs": [
            "https://www.instagram.com/kaos_tattoo_alicante",
            "https://www.tiktok.com/@kaos_tattoo_alicante"
        ]
    }
    </script>
    
    <link rel="preload" href="fonts/Bokor/Bokor-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/Cinzel/Cinzel-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/Cinzel/Cinzel-Medium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/CormorantGaramond/CormorantGaramond-Light.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/CormorantGaramond/CormorantGaramond-Regular.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/CormorantGaramond/CormorantGaramond-Medium.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="fonts/Montserrat/JTUSjIg1_i6t8kCHKm459Wlhyw.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="styles/main.css">
    <link rel="icon" type="image/png" href="images/logo_perro-nobackground.webp">
</head>
<body class="is-loading">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W7X5FVW3"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    
    <div class="loading-overlay" aria-live="polite" aria-label="Cargando Kaos Tattoo">
        <div class="loading-content">
            <div class="loading-logo-wrapper">
                <img src="images/logo_perro-nobackground.webp" alt="Kaos Tattoo Logo" class="loading-logo">
            </div>
            <p class="loading-text"><span lang="es">Entrando al estudio...</span><span lang="en">Entering the studio...</span></p>
        </div>
    </div>
    <nav class="sidebar">
        <div class="sidebar-header">
            <a href="/" class="logo-link">
                <img src="images/logo_nobackground.webp" alt="Kaos Tattoo Logo" class="sidebar-logo">
            </a>
        </div>
        <ul class="nav-menu">
            <li class="has-dropdown">
                <a href="#" class="nav-link dropdown-toggle"><span lang="es">Equipo</span><span lang="en">Team</span></a>
                <ul class="dropdown-menu">
                    <li><a href="/equipo/" data-page="tatuadores"><span lang="es">Tatuadores</span><span lang="en">Tattoo Artists</span></a></li>
                    <li><a href="/anilladora/" data-page="anilladora"><span lang="es">Anilladora</span><span lang="en">Piercer</span></a></li>
                </ul>
            </li>
            <li class="has-dropdown">
                <a href="#" class="nav-link dropdown-toggle"><span lang="es">Estilos</span><span lang="en">Styles</span></a>
                <ul class="dropdown-menu">
                    <li><a href="/estilos/#fineline" data-page="fineline">Fineline</a></li>
                    <li><a href="/estilos/#realismo" data-page="realismo"><span lang="es">Realismo</span><span lang="en">Realism</span></a></li>
                    <li><a href="/estilos/#tradicional" data-page="tradicional"><span lang="es">Tradicional</span><span lang="en">Traditional</span></a></li>
                    <li><a href="/estilos/#anime" data-page="anime">Anime</a></li>
                    <li><a href="/estilos/#blackwork" data-page="blackwork">Blackwork</a></li>
                    <li><a href="/estilos/#cartoon" data-page="cartoon">Cartoon</a></li>
                    <li><a href="/estilos/#geometrico" data-page="geometrico"><span lang="es">Geométrico</span><span lang="en">Geometric</span></a></li>
                    <li><a href="/estilos/#japones" data-page="japones"><span lang="es">Japones</span><span lang="en">Japanese</span></a></li>
                    <li><a href="/estilos/#lettering" data-page="lettering">Lettering</a></li>
                    <li><a href="/estilos/#microrealismo" data-page="microrealismo"><span lang="es">Microrealismo</span><span lang="en">Micro-realism</span></a></li>
                </ul>
            </li>
            <li class="has-dropdown">
                <a href="#" class="nav-link dropdown-toggle"><span lang="es">Servicios</span><span lang="en">Services</span></a>
                <ul class="dropdown-menu">
                    <li><a href="/tatuajes/" data-page="tatuajes"><span lang="es">Tatuajes</span><span lang="en">Tattoos</span></a></li>
                    <li><a href="/piercings/" data-page="piercings">Piercings</a></li>
                    <li><a href="/dibujos-cuadros/" data-page="dibujos-cuadros"><span lang="es">Dibujos y Cuadros</span><span lang="en">Drawings & Paintings</span></a></li>
                </ul>
            </li>
            <li>
                <a href="/blog/" class="nav-link" data-page="blog">Blog</a>
            </li>
            <li class="nav-cta-item">
                <a href="/contacto/" class="nav-link nav-cta" data-page="contacto"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
            </li>
            <li class="mobile-submenu-wrapper" aria-hidden="true">
                <button class="mobile-submenu-back" type="button" aria-label="Volver al menú principal" data-i18n-aria-label="Back to main menu">
                    <span class="back-icon" aria-hidden="true">&#8592;</span>
                    <span lang="es">VOLVER</span><span lang="en">BACK</span>
                </button>
                <p class="mobile-submenu-title"><span lang="es">Menú</span><span lang="en">Menu</span></p>
                <ul class="mobile-submenu-list"></ul>
            </li>
        </ul>
        <div class="lang-switcher" aria-label="Language selector">
            <a href="/" data-lang="es" class="active">ES</a>
            <span class="lang-sep">|</span>
            <a href="/en/" data-lang="en">EN</a>
        </div>
        <div class="mobile-controls">
            <button class="mobile-menu-toggle" type="button" aria-label="Abrir menú" aria-expanded="false">
                <span class="menu-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
                <span class="menu-label"><span lang="es">Menú</span><span lang="en">Menu</span></span>
                <img src="images/logo_perro-nobackground.webp" alt="Kaos Tattoo Logo" class="mobile-menu-logo">
            </button>
        </div>
        <div class="mobile-menu-overlay" aria-hidden="true"></div>
    </nav>

    <main class="main-content">
        <div id="home" class="page<?php echo pageActive('home', $pageId); ?>">
            <section class="hero">
                <video class="hero-video" autoplay muted loop playsinline poster="images/posters/KAOS_logo_video.webp">
                    <source src="videos/kaos-portada-mobile.webm" type="video/webm" media="(max-width: 768px)">
                    <source src="videos/kaos-portada-mobile.mp4" type="video/mp4" media="(max-width: 768px)">
                    <source src="videos/kaos-portada.webm" type="video/webm">
                    <source src="videos/kaos-portada.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>

            <section class="home-about">
                <video class="home-about-video" muted loop playsinline preload="none" poster="images/posters/music_video.webp" data-lazy-video>
                    <source data-src="videos/music_video.MP4" type="video/mp4">
                </video>
                <div class="home-about-overlay"></div>
                <div class="home-about-content">
                    <h1><span lang="es">Estudio de Tatuajes en Alicante</span><span lang="en">Tattoo Studio in Alicante</span></h1>
                    <p><span lang="es">Todo lo que suceda en este estudio de tatuajes en Alicante, se queda en este estudio. No somos Las Vegas pero haremos que te lo pases igual de bien. Nuestro propósito de vida es ayudar a que te sientas más a gusto que en tu casa. Somos gente maja, aunque te hagamos daño. En Kaos Tattoo nos gusta dar la tabarra solo para que quieras repetir y vuelvas a vernos. Si estás leyendo esto es por algo: sonríe y déjate llevar.</span><span lang="en">What happens in this tattoo studio in Alicante, stays in this studio. We're not Las Vegas, but we'll make sure you have just as good a time. Our life purpose is to help you feel more at home than at your own place. We're friendly people, even if we cause you a little pain. At Kaos Tattoo we love to chat just so you'll want to come back and see us again. If you're reading this, it's for a reason: smile and let yourself go.</span></p>
                    <a href="/contacto/" data-page="contacto" class="btn btn-primary btn-book-now" data-scroll-reveal><span lang="es">CUÉNTANOS TU IDEA</span><span lang="en">TELL US YOUR IDEA</span></a>
                </div>
            </section>

            <!-- TO REPLACE IMAGES: Change the src="..." to your own image paths -->
            <!-- Example: <img src="images/your-tattoo-1.webp" alt="Description"> -->
            <section class="home-gallery">
                <div class="home-gallery-bg"></div>
                <h2 class="home-gallery-title">
                    <img src="images/logo_perroseñalando-nobg.webp" alt="Kaos Tattoo Dog Logo" class="gallery-logo">
                    <span lang="es">NUESTRO TRABAJO</span><span lang="en">OUR WORK</span>
                </h2>
                <div class="home-gallery-grid">
                    <div class="home-gallery-item">
                        <img loading="lazy" src="images/STYLES/Realismo/261F6A08-94D1-4490-90D7-27AFEC46B4E2 2.webp" alt="Tattoo work 1">
                        <div class="category-overlay">
                            <h3><span lang="es">Tatuajes</span><span lang="en">Tattoos</span></h3>
                            <a href="/tatuajes/" data-page="tatuajes" class="category-link"><span lang="es">Ver más</span><span lang="en">See more</span></a>
                        </div>
                    </div>
                    <div class="home-gallery-item">
                        <img loading="lazy" src="images/STYLES/Piercings/IMG_3415 2.webp" alt="Tattoo work 5">
                        <div class="category-overlay">
                            <h3>Piercings</h3>
                            <a href="/piercings/" data-page="piercings" class="category-link"><span lang="es">Ver más</span><span lang="en">See more</span></a>
                        </div>
                    </div>
                    <div class="home-gallery-item">
                        <img loading="lazy" src="images/el_boss21.webp" alt="Tattoo work 6">
                        <div class="category-overlay">
                            <h3><span lang="es">Dibujos y Cuadros</span><span lang="en">Drawings & Paintings</span></h3>
                            <a href="/dibujos-cuadros/" data-page="dibujos-cuadros" class="category-link"><span lang="es">Ver más</span><span lang="en">See more</span></a>
                        </div>
                    </div>
                </div>
            </section>

            <section class="home-cta">
                <video class="home-cta-video" muted loop playsinline preload="none" poster="images/posters/musicentrance_video.webp" data-lazy-video>
                    <source data-src="videos/musicentrance_video.mp4" type="video/mp4">
                </video>
                <div class="home-cta-overlay"></div>
                <div class="cta-content">
                    <div class="cta-left">
                        <h2><span lang="es">¡VEN A VISITARNOS!</span><span lang="en">COME VISIT US!</span></h2>
                        <p><span lang="es">¿Quieres preguntarnos algo?</span><span lang="en">Want to ask us something?</span></p>
                        <a href="/contacto/" data-page="contacto" class="btn btn-primary btn-book-now"><span lang="es">¡CONTACTA AHORA!</span><span lang="en">CONTACT US NOW!</span></a>
                    </div>
                    <div class="cta-right">
                        <iframe 
                            data-lazy-iframe
                            data-src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3145.7481267873!2d-0.4735056846816652!3d38.345995979654!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd6236a3b4c5c5c5%3A0x4b5c5c5c5c5c5c5c!2sPintor%20Vel%C3%A1zquez%2017%2C%2003004%20Alicante%2C%20Spain!5e0!3m2!1sen!2ses!4v1234567890"
                            width="100%" 
                            height="300" 
                            style="border:0; border-radius: 8px; aspect-ratio: 16/9; background: #1a1a1a;" 
                            allowfullscreen="" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </section>

            <section class="home-tiktok" data-lazy-tiktok>
                <div class="home-tiktok-overlay"></div>
                <div class="home-tiktok-content">
                    <div class="home-tiktok-widget" style="min-height: 400px;">
                        <blockquote class="tiktok-embed" cite="https://www.tiktok.com/@kaos.tattoo.alicante" data-unique-id="kaos.tattoo.alicante" data-embed-type="creator" style="max-width: 605px; min-width: 325px;">
                            <section>
                                <a target="_blank" href="https://www.tiktok.com/@kaos.tattoo.alicante?refer=creator_embed">@kaos.tattoo.alicante</a>
                            </section>
                        </blockquote>
                    </div>
                </div>
            </section>
        </div>

        <div id="tatuadores" class="page<?php echo pageActive('tatuadores', $pageId); ?>">
            <section class="team-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/tatuadores_video.webp" data-lazy-video>
                    <source data-src="videos/tatuadores_video.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>

            <section class="page-heading">
                <h1><span lang="es">Conoce al equipo de Kaos Tattoo</span><span lang="en">Meet the Kaos Tattoo team</span></h1>
            </section>

            <div class="page-content">
                <div class="team-masonry simple-grid">
                    <div class="team-profile-card"
                         data-member-name="Tailor"
                         data-member-specialty="Tradicional · Color · Responsable del estudio"
                         data-member-specialty-en="Traditional · Color · Studio Manager"
                         data-member-description="Kike, a.k.a. Tailor, responsable del estudio. Con más de 7 años de experiencia como tatuador, especializado en tradicional y color, también destaca por su dominio del realismo, microrealismo y línea fina. Se adapta con precisión a cualquier idea del cliente. Fuera de la piel, su pasión es la pintura y el dibujo, creando arte a través de cuadros realistas o retratos con distintas técnicas artísticas como el óleo."
                         data-member-description-en="Kike, a.k.a. Tailor, studio manager. With over 7 years of experience as a tattoo artist, specialising in traditional and colour, he also stands out for his mastery of realism, micro-realism and fine line. He adapts precisely to any client's idea. Outside of skin, his passion is painting and drawing, creating art through realistic paintings or portraits using various artistic techniques such as oil painting."
                         data-member-image="images/tailor.webp"
                         data-member-portfolio="#portfolio-tailor">
                        <div class="team-member">
                            <img loading="lazy" src="images/tailor.webp" alt="Tailor" class="member-image tailor-image">
                            <span class="tap-hint" aria-hidden="true">👆</span>
                            <div class="artist-overlay">
                                <h3>Tailor</h3>
                                <p class="artist-tagline"><span lang="es">Tradicional · Color</span><span lang="en">Traditional · Colour</span></p>
                                <button class="team-card-cta" type="button">
                                    <span><span lang="es">Conocer más</span><span lang="en">Learn more</span></span>
                                </button>
                            </div>
                        </div>
                        <a href="/equipo/#portfolio-tailor" class="btn btn-primary btn-portfolio" data-page="portfolio-tailor"><span lang="es">Ver Portfolio</span><span lang="en">View Portfolio</span></a>
                    </div>
                    <div class="team-logo-divider">
                        <img src="images/logo_perro-nobackground.webp" alt="Kaos Tattoo Dog Logo" class="team-logo-neon">
                    </div>
                    <div class="team-profile-card"
                         data-member-name="Carrie"
                         data-member-specialty="Anime · Ornamental · Línea fina"
                         data-member-specialty-en="Anime · Ornamental · Fine line"
                         data-member-description="Carrie, alias Da.Needed, es tatuadora desde hace 7 años y forma parte de Kaos Tattoo desde hace año y medio. Su estilo destaca por el anime, el ornamental, la línea fina y el blackwork, adaptando cada diseño a los intereses del cliente sin perder su identidad artística. Su trato cercano y calmado hace que quienes se tatúan con ella se sientan tranquilos y cómodos durante todo el proceso."
                         data-member-description-en="Carrie, a.k.a. Da.Needed, has been tattooing for 7 years and has been part of Kaos Tattoo for a year and a half. Her style stands out for anime, ornamental, fine line and blackwork, adapting each design to the client's interests without losing her artistic identity. Her warm and calm manner makes those who get tattooed by her feel relaxed and comfortable throughout the process."
                         data-member-image="images/carrie1.webp"
                         data-member-portfolio="#portfolio-carrie">
                        <div class="team-member">
                            <img loading="lazy" src="images/carrie1.webp" alt="Carrie" class="member-image">
                            <span class="tap-hint" aria-hidden="true">👆</span>
                            <div class="artist-overlay">
                                <h3>Carrie</h3>
                                <p class="artist-tagline">Anime · Ornamental</p>
                                <button class="team-card-cta" type="button">
                                    <span><span lang="es">Conocer más</span><span lang="en">Learn more</span></span>
                                </button>
                            </div>
                        </div>
                        <a href="/equipo/#portfolio-carrie" class="btn btn-primary btn-portfolio" data-page="portfolio-carrie"><span lang="es">Ver Portfolio</span><span lang="en">View Portfolio</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div id="portfolio-tailor" class="page portfolio-section">
            <div class="page-content portfolio-page">
                <div class="portfolio-header" data-scroll-reveal>
                    <h1><span lang="es">Portfolio de Tailor</span><span lang="en">Tailor's Portfolio</span></h1>
                    <p class="portfolio-intro"><span lang="es">Selección de tatuajes de Tailor.</span><span lang="en">A selection of tattoos by Tailor.</span></p>
                </div>
                <div class="portfolio-gallery-wrap fineline-gallery">
                    <div class="portfolio-bg-overlay"></div>
                    <div class="gallery-masonry">
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_6110 21.webp" alt="Tatuaje Tailor 1">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_2781 21.webp" alt="Tatuaje Tailor 2">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_0898 21.webp" alt="Tatuaje Tailor 3">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_6492 21.webp" alt="Tatuaje Tailor 4">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Cartoon/IMG_05231.webp" alt="Tatuaje Tailor 5">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_3470 21.webp" alt="Tatuaje Tailor 6">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_6943 31.webp" alt="Tatuaje Tailor 7">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_8209.webp" alt="Tatuaje Tailor 8">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_8782.webp" alt="Tatuaje Tailor 9">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_68631.webp" alt="Tatuaje Tailor 10">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_74411.webp" alt="Tatuaje Tailor 11">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_74431.webp" alt="Tatuaje Tailor 12">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_26441.webp" alt="Tatuaje Tailor 13">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_71281.webp" alt="Tatuaje Tailor 14">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_67911.webp" alt="Tatuaje Tailor 15">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_82931.webp" alt="Tatuaje Tailor 16">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_16401.webp" alt="Tatuaje Tailor 17">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_8226 21.webp" alt="Tatuaje Tailor 18">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_50121.webp" alt="Tatuaje Tailor 19">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_68551.webp" alt="Tatuaje Tailor 20">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_09651.webp" alt="Tatuaje Tailor 21">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_7074 21.webp" alt="Tatuaje Tailor 22">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_6837 21.webp" alt="Tatuaje Tailor 23">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_71211.webp" alt="Tatuaje Tailor 24">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_28391.webp" alt="Tatuaje Tailor 25">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_89221.webp" alt="Tatuaje Tailor 26">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_8365.webp" alt="Tatuaje Tailor 27">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="portfolio-carrie" class="page portfolio-section">
            <div class="page-content portfolio-page">
                <div class="portfolio-header" data-scroll-reveal>
                    <h1><span lang="es">Portfolio de Carrie</span><span lang="en">Carrie's Portfolio</span></h1>
                    <p class="portfolio-intro"><span lang="es">Selección de tatuajes de Carrie.</span><span lang="en">A selection of tattoos by Carrie.</span></p>
                </div>
                <div class="portfolio-gallery-wrap fineline-gallery">
                    <div class="portfolio-bg-overlay"></div>
                                        <div class="gallery-masonry">
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000125914.webp" alt="Tatuaje Carrie 1">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000128301.webp" alt="Tatuaje Carrie 2">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000132578.webp" alt="Tatuaje Carrie 3">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000133078.webp" alt="Tatuaje Carrie 4">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000134271.webp" alt="Tatuaje Carrie 5">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000135526.webp" alt="Tatuaje Carrie 6">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000138485.webp" alt="Tatuaje Carrie 7">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000138616.webp" alt="Tatuaje Carrie 8">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000141700.webp" alt="Tatuaje Carrie 9">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000142394.webp" alt="Tatuaje Carrie 10">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000142395.webp" alt="Tatuaje Carrie 11">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000142396.webp" alt="Tatuaje Carrie 12">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000143095.webp" alt="Tatuaje Carrie 13">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000143097.webp" alt="Tatuaje Carrie 14">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000143145.webp" alt="Tatuaje Carrie 15">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144779.webp" alt="Tatuaje Carrie 16">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144780.webp" alt="Tatuaje Carrie 17">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144961.webp" alt="Tatuaje Carrie 18">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144963.webp" alt="Tatuaje Carrie 19">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144971.webp" alt="Tatuaje Carrie 20">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144972.webp" alt="Tatuaje Carrie 21">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000144973.webp" alt="Tatuaje Carrie 22">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000150349.webp" alt="Tatuaje Carrie 23">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000150350.webp" alt="Tatuaje Carrie 24">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000151756.webp" alt="Tatuaje Carrie 25">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000152037.webp" alt="Tatuaje Carrie 26">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000154550.webp" alt="Tatuaje Carrie 27">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000156984.webp" alt="Tatuaje Carrie 28">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000160820.webp" alt="Tatuaje Carrie 29">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000160821.webp" alt="Tatuaje Carrie 30">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000161250.webp" alt="Tatuaje Carrie 31">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000162317.webp" alt="Tatuaje Carrie 32">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000165356.webp" alt="Tatuaje Carrie 33">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000166391.webp" alt="Tatuaje Carrie 34">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000167698.webp" alt="Tatuaje Carrie 35">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168515.webp" alt="Tatuaje Carrie 36">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168517.webp" alt="Tatuaje Carrie 37">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168566.webp" alt="Tatuaje Carrie 38">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168567.webp" alt="Tatuaje Carrie 39">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168568.webp" alt="Tatuaje Carrie 40">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168570.webp" alt="Tatuaje Carrie 41">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000168571.webp" alt="Tatuaje Carrie 42">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000171844.webp" alt="Tatuaje Carrie 43">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000172868.webp" alt="Tatuaje Carrie 44">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/Portfolio Carrie/1000173514.webp" alt="Tatuaje Carrie 45">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="portfolio-greka" class="page portfolio-section">
            <div class="page-content portfolio-page">
                <div class="portfolio-header" data-scroll-reveal>
                    <h1><span lang="es">Portfolio de La Greka</span><span lang="en">La Greka's Portfolio</span></h1>
                    <p class="portfolio-intro"><span lang="es">Selección de piercings de La Greka.</span><span lang="en">A selection of piercings by La Greka.</span></p>
                </div>
                <div class="portfolio-gallery-wrap fineline-gallery">
                    <div class="portfolio-bg-overlay"></div>
                                        <div class="gallery-masonry">
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/00191a57-7b61-4702-ad27-995b16fb220c 2.webp" alt="Piercing La Greka 1">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/2E504F30-5416-45F4-91FC-B00121CB322E.webp" alt="Piercing La Greka 2">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/4347B578-48DF-4DC5-AC1B-BE92FE8E2180 2.webp" alt="Piercing La Greka 3">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/7294B3E0-92BD-4CC2-A368-BC44B177DA31 2.webp" alt="Piercing La Greka 4">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/8affbb1c-effe-4064-8f24-59a0a9d9ecbc 2.webp" alt="Piercing La Greka 5">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_05001.webp" alt="Piercing La Greka 6">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_28211.webp" alt="Piercing La Greka 7">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_2832.webp" alt="Piercing La Greka 8">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_3376 31.webp" alt="Piercing La Greka 9">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_3415 2.webp" alt="Piercing La Greka 10">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_3522 21.webp" alt="Piercing La Greka 11">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_35241.webp" alt="Piercing La Greka 12">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_36081.webp" alt="Piercing La Greka 13">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/8affbb1c-effe-4064-8f24-59a0a9d9ecbc 2.webp" alt="Piercing La Greka 14">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_5399 21.webp" alt="Piercing La Greka 15">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_5406 31.webp" alt="Piercing La Greka 16">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_5564 41.webp" alt="Piercing La Greka 17">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_56421.webp" alt="Piercing La Greka 18">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_56551.webp" alt="Piercing La Greka 19">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_56741.webp" alt="Piercing La Greka 20">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_59151.webp" alt="Piercing La Greka 21">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_6280 31.webp" alt="Piercing La Greka 22">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_6385 21.webp" alt="Piercing La Greka 23">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_6462 2.webp" alt="Piercing La Greka 24">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_6463 3.webp" alt="Piercing La Greka 25">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_7186 4.webp" alt="Piercing La Greka 26">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_7219 41.webp" alt="Piercing La Greka 27">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_72221.webp" alt="Piercing La Greka 28">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_75011.webp" alt="Piercing La Greka 29">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_75031.webp" alt="Piercing La Greka 30">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_77781.webp" alt="Piercing La Greka 31">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_7780 21.webp" alt="Piercing La Greka 32">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_7903 21.webp" alt="Piercing La Greka 33">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_7904 21.webp" alt="Piercing La Greka 34">
                            </div>
                        </div>
                        <div class="masonry-item wide" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_7919 31.webp" alt="Piercing La Greka 35">
                            </div>
                        </div>
                        <div class="masonry-item tall" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_84061.webp" alt="Piercing La Greka 36">
                            </div>
                        </div>
                        <div class="masonry-item" data-scroll-reveal>
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Piercings/IMG_84201.webp" alt="Piercing La Greka 37">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="anilladora" class="page<?php echo pageActive('anilladora', $pageId); ?>">
            <section class="team-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/anilladora_video.webp" data-lazy-video>
                    <source data-src="videos/anilladora_video.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>

            <section class="page-heading">
                <h1><span lang="es">Conoce al equipo de Kaos Tattoo</span><span lang="en">Meet the Kaos Tattoo team</span></h1>
            </section>

            <div class="page-content">
                <div class="team-masonry simple-grid single-column with-side-logos">
                    <div class="team-logo-divider left">
                        <img src="images/logo_perro-nobackground.webp" alt="Kaos Tattoo Dog Logo" class="team-logo-neon">
                    </div>
                    <div class="team-profile-card"
                         data-member-name="La Greka"
                         data-member-specialty="Piercing · Marketing · Social Media"
                         data-member-specialty-en="Piercing · Marketing · Social Media"
                         data-member-description="Laura, alias lagreka, con 4 años de experiencia como piercer. Destaca por su cercanía, rapidez y empatía, siempre adaptándose a las necesidades y anatomía de cada persona. Su servicio incluye un buen seguimiento para una buena cicatrización ante cualquier perforación. También lleva el marketing del estudio: atención al cliente, mantenimiento y organización del estudio, creación de contenido y redes sociales."
                         data-member-description-en="Laura, a.k.a. La Greka, with 4 years of experience as a piercer. She stands out for her friendliness, speed and empathy, always adapting to each person's needs and anatomy. Her service includes thorough follow-up for proper healing after any piercing. She also handles the studio's marketing: customer service, studio maintenance and organisation, content creation and social media."
                         data-member-image="images/lagreka1.webp"
                         data-member-portfolio="#portfolio-greka">
                        <div class="team-member">
                            <img loading="lazy" src="images/lagreka1.webp" alt="La Greka" class="member-image greka-image">
                            <span class="tap-hint" aria-hidden="true">👆</span>
                            <div class="artist-overlay">
                                <h3>La Greka</h3>
                                <p class="artist-tagline">Piercing · Marketing</p>
                                <button class="team-card-cta" type="button">
                                    <span><span lang="es">Conocer más</span><span lang="en">Learn more</span></span>
                                </button>
                            </div>
                        </div>
                        <a href="/anilladora/#portfolio-greka" class="btn btn-primary btn-portfolio" data-page="portfolio-greka"><span lang="es">Ver Portfolio</span><span lang="en">View Portfolio</span></a>
                    </div>
                    <div class="team-logo-divider right">
                        <img src="images/logo_perro-nobackground.webp" alt="Kaos Tattoo Dog Logo" class="team-logo-neon">
                    </div>
                </div>
            </div>
        </div>

        <div id="fineline" class="page<?php echo pageActive('fineline', $pageId); ?>">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/FINELINE.webp" data-lazy-video>
                    <source data-src="videos/headers/FINELINE.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">Si buscas un tatuaje fino, elegante y con clase, el fineline es tu estilo. En Kaos Tattoo cuidamos cada línea al milímetro para que el resultado sea limpio, delicado y con personalidad propia. No se trata solo de hacer líneas finas, sino de hacerlas bien para que el tatuaje se mantenga bonito con el paso del tiempo.</span><span lang="en">If you're looking for a fine, elegant and classy tattoo, fineline is your style. At Kaos Tattoo we take care of every line down to the millimetre so the result is clean, delicate and full of personality. It's not just about making thin lines — it's about making them right so the tattoo stays beautiful over time.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="0">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_1156.webp" alt="Fineline 1">
                        </div>
                    </div>

                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_1299.webp" alt="Fineline 2">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="200">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_2071 2.webp" alt="Fineline 3">
                        </div>
                    </div>

                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="300">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_2809.webp" alt="Fineline 4">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="400">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_2813.webp" alt="Fineline 5">
                        </div>
                    </div>

                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="500">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_2816.webp" alt="Fineline 6">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="600">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/IMG_7378 2.webp" alt="Fineline 7">
                        </div>
                    </div>

                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="700">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/10JUNIO2026/d0fea787-3ee4-4ce9-a51c-fbd0b771dd91.webp" alt="Fineline 8">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="900">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Fine Line/IMG_3445 21.webp" alt="Fineline 9">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="1000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_3470 21.webp" alt="Fineline 10">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_3660 31.webp" alt="Fineline 11">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_37221.webp" alt="Fineline 12">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_4443 21.webp" alt="Fineline 13">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_6370 21.webp" alt="Fineline 14">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_68011.webp" alt="Fineline 15">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_6943 31.webp" alt="Fineline 16">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="1700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_6947 31.webp" alt="Fineline 17">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_6948 21.webp" alt="Fineline 18">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_7188 31.webp" alt="Fineline 19">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_74271.webp" alt="Fineline 20">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_73921.webp" alt="Fineline 21">
                            </div>
                        </div>

                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="2200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_7513 21.webp" alt="Fineline 22">
                            </div>
                        </div>

                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_80061.webp" alt="Fineline 23">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_8007 21.webp" alt="Fineline 24">
                            </div>
                        </div>

                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="1700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_8497 31.webp" alt="Fineline 25">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_8500 21.webp" alt="Fineline 26">
                            </div>
                        </div>

                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_8506 21.webp" alt="Fineline 27">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_4196 2.webp" alt="Fineline 28">
                            </div>
                        </div>

                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="2100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_7378 2.webp" alt="Fineline 29">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="3000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_7379 2.webp" alt="Fineline 30">
                            </div>
                        </div>

                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="3100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_7380 2.webp" alt="Fineline 31">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="3200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Fine Line/IMG_8717 2 copia.webp" alt="Fineline 32">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="3300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Fineline/IMG_0842.webp" alt="Fineline 33">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>
<div id="anime" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/ANIME.webp" data-lazy-video>
                    <source data-src="videos/headers/ANIME.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">Si el anime forma parte de ti, aquí lo llevamos a otro nivel. En Kaos Tattoo entendemos el estilo, las proporciones y la fuerza de cada personaje para que el resultado no sea solo correcto, sino brutal. Tu personaje favorito merece estar bien hecho.</span><span lang="en">If anime is part of you, we take it to the next level here. At Kaos Tattoo we understand the style, proportions and power of each character so the result isn't just correct — it's stunning. Your favourite character deserves to be done right.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Anime/Captura de pantalla 2026-01-07 a las 11.35.21.webp" alt="Anime 1">
                            </div>
                        </div>
                        
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Anime/IMG_8887.webp" alt="Anime 2">
                            </div>
                        </div>
                        

                    </div>
                </div>
                
            </div>

        </div>

<div id="blackwork" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/BLACKWORK.webp" data-lazy-video>
                    <source data-src="videos/headers/BLACKWORK.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El blackwork es actitud. Piezas oscuras, contrastadas y con mucha presencia. En Kaos Tattoo trabajamos el negro con profundidad y precisión para crear tatuajes que impactan desde el primer momento y que siguen haciéndolo con los años.</span><span lang="en">Blackwork is attitude. Dark, high-contrast pieces with a strong presence. At Kaos Tattoo we work black ink with depth and precision to create tattoos that make an impact from the first moment and keep doing so over the years.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Blackwork/Ilustración_sin_título 15.webp" alt="Blackwork 1">
                            </div>
                        </div>
                        
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_0898 21.webp" alt="Blackwork 2">
                            </div>
                        </div>
                        
                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_1585 31.webp" alt="Blackwork 3">
                            </div>
                        </div>
                        
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_2726 21.webp" alt="Blackwork 4">
                            </div>
                        </div>
                        
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_28441.webp" alt="Blackwork 5">
                            </div>
                        </div>
                        
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_63361.webp" alt="Blackwork 6">
                            </div>
                        </div>
                        
                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_6492 21.webp" alt="Blackwork 7">
                            </div>
                        </div>
                        
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_82171.webp" alt="Blackwork 8">
                            </div>
                        </div>
                        
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Blackwork/IMG_8964.webp" alt="Blackwork 9">
                            </div>
                        </div>
                        

                    </div>
                </div>
                
            </div>

        </div>

<div id="cartoon" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/CARTOON.webp" data-lazy-video>
                    <source data-src="videos/headers/CARTOON.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El estilo cartoon es libertad total. Divertido, irreverente o incluso macabro, pero siempre con personalidad. En Kaos Tattoo adaptamos cada idea para que no sea un diseño más, sino algo único que encaje contigo y destaque.</span><span lang="en">Cartoon style is total freedom. Fun, irreverent or even macabre, but always full of personality. At Kaos Tattoo we adapt every idea so it's not just another design — it's something unique that fits you and stands out.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Cartoon/IMG_05231.webp" alt="Cartoon 1">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Cartoon/IMG_5583 21.webp" alt="Cartoon 2">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Cartoon/IMG_6370 21.webp" alt="Cartoon 3">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Cartoon/IMG_8431 21.webp" alt="Cartoon 4">
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>

        </div>

<div id="geometrico" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/GEOMETRICO.webp" data-lazy-video>
                    <source data-src="videos/headers/GEOMETRICO.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El geométrico exige precisión absoluta. Líneas perfectas, simetría y equilibrio. En Kaos Tattoo cuidamos cada detalle para que el resultado sea limpio, armónico y visualmente potente. Si te gusta lo ordenado y lo estético, aquí es donde cobra sentido.</span><span lang="en">Geometric demands absolute precision. Perfect lines, symmetry and balance. At Kaos Tattoo we take care of every detail so the result is clean, harmonious and visually powerful. If you love order and aesthetics, this is where it all makes sense.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Geometrico/81F5B5CB-347A-4B41-97AB-32D01EFAFD34.webp" alt="Geometrico 1">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_0393.webp" alt="Geometrico 2">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_8209.webp" alt="Geometrico 3">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_82741.webp" alt="Geometrico 4">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_8365.webp" alt="Geometrico 5">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Geometrico/IMG_8782.webp" alt="Geometrico 6">
                            </div>
                        </div>
                        
                    </div>
                </div>
                
            </div>

        </div>

<div id="japones" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/JAPONES.webp" data-lazy-video>
                    <source data-src="videos/headers/JAPONES.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El japonés no es solo un estilo, es una forma de entender el tatuaje. Composición, fluidez y simbolismo en cada pieza. En Kaos Tattoo respetamos la tradición y la adaptamos a tu cuerpo para crear tatuajes que encajan y fluyen de verdad.</span><span lang="en">Japanese is not just a style — it's a way of understanding tattooing. Composition, flow and symbolism in every piece. At Kaos Tattoo we respect tradition and adapt it to your body to create tattoos that truly fit and flow.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Japones/IMG_09651.webp" alt="Japones 1">
                            </div>
                        </div>
                        
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_2866 21.webp" alt="Japones 2">
                            </div>
                        </div>
                        
                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_7074 21.webp" alt="Japones 3">
                            </div>
                        </div>
                        
                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_82171.webp" alt="Japones 4">
                            </div>
                        </div>
                        
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Japones/IMG_89221.webp" alt="Japones 5">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Japones/IMG_0463.webp" alt="Japones 6">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Japones/IMG_9832.webp" alt="Japones 7">
                            </div>
                        </div>

                    </div>
                </div>
                
            </div>

        </div>

<div id="lettering" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/LETTERING.webp" data-lazy-video>
                    <source data-src="videos/headers/LETTERING.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">Un buen lettering no es solo escribir, es diseñar. En Kaos Tattoo trabajamos cada trazo, cada curva y cada composición para que tus palabras tengan la fuerza que merecen. Porque si te lo vas a tatuar, tiene que estar perfecto.</span><span lang="en">Good lettering isn't just writing — it's design. At Kaos Tattoo we craft every stroke, every curve and every composition so your words carry the power they deserve. Because if you're going to tattoo it, it has to be perfect.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Lettering/IMG_49381.webp" alt="Lettering 1">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Lettering/IMG_8309 21.webp" alt="Lettering 2">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

<div id="microrealismo" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/MICROREALISMO.webp" data-lazy-video>
                    <source data-src="videos/headers/MICROREALISMO.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El microrrealismo es detalle extremo en tamaño reducido. En Kaos Tattoo llevamos la precisión al límite para conseguir tatuajes pequeños pero con una definición que sorprende. Discreto no significa simple, y aquí lo demostramos.</span><span lang="en">Micro-realism is extreme detail in a small size. At Kaos Tattoo we push precision to the limit to achieve small tattoos with stunning definition. Subtle doesn't mean simple — and we prove it here.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/Captura de pantalla 2026-01-07 a las 11.35.57.webp" alt="Microrealismo 1">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_0832 21.webp" alt="Microrealismo 2">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_08391.webp" alt="Microrealismo 3">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_2601 21.webp" alt="Microrealismo 4">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_28391.webp" alt="Microrealismo 5">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_3660 31.webp" alt="Microrealismo 6">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_4180 31.webp" alt="Microrealismo 7">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_4859 21.webp" alt="Microrealismo 8">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_5583 21.webp" alt="Microrealismo 9">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_5802 21.webp" alt="Microrealismo 10">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_61601.webp" alt="Microrealismo 11">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_6576.webp" alt="Microrealismo 12">
                            </div>
                        </div>
                    

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_68291.webp" alt="Microrealismo 13">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_68631.webp" alt="Microrealismo 14">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_6869 2 21.webp" alt="Microrealismo 15">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_6869 21.webp" alt="Microrealismo 16">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_6942 31.webp" alt="Microrealismo 17">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_71211.webp" alt="Microrealismo 18">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_74411.webp" alt="Microrealismo 19">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_74431.webp" alt="Microrealismo 20">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_7485 21.webp" alt="Microrealismo 21">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_7513 21.webp" alt="Microrealismo 22">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_81561.webp" alt="Microrealismo 23">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_8238 2.webp" alt="Microrealismo 24">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_8389 2.webp" alt="Microrealismo 25">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_8437 21.webp" alt="Microrealismo 26">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_8680 2.webp" alt="Microrealismo 27">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_8681 2.webp" alt="Microrealismo 28">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_8734.webp" alt="Microrealismo 29">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="3000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Microrealismo/IMG_9920.webp" alt="Microrealismo 30">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="3100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Microrrealismo/IMG_1005.webp" alt="Microrealismo 31">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>

        </div>

        <div id="realismo" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/REALISMO.webp" data-lazy-video>
                    <source data-src="videos/headers/REALISMO.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El realismo no perdona errores, y por eso aquí marcamos la diferencia. En Kaos Tattoo trabajamos cada sombra, cada textura y cada detalle para que el resultado sea lo más fiel posible a la imagen que quieres llevar en la piel. Si buscas impacto y un tatuaje que hable por sí solo, estás en el sitio correcto.</span><span lang="en">Realism doesn't forgive mistakes, and that's why we make the difference here. At Kaos Tattoo we work every shadow, every texture and every detail so the result is as faithful as possible to the image you want on your skin. If you're looking for impact and a tattoo that speaks for itself, you're in the right place.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="0">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_1844.webp" alt="Realismo 1">
                        </div>
                    </div>

                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2554.webp" alt="Realismo 2">
                        </div>
                    </div>

                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="200">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2618.webp" alt="Realismo 3">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="300">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2723.webp" alt="Realismo 4">
                        </div>
                    </div>

                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="400">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2798.webp" alt="Realismo 5">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="500">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2975.webp" alt="Realismo 6">
                        </div>
                    </div>

                    <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="600">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2976.webp" alt="Realismo 7">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="700">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2979.webp" alt="Realismo 8">
                        </div>
                    </div>

                    <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="800">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/10junio2026/IMG_2980.webp" alt="Realismo 9">
                        </div>
                    </div>

                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="900">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Realismo/261F6A08-94D1-4490-90D7-27AFEC46B4E2 2.webp" alt="Realismo 10">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="1000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/Ilustración_sin_título 15.webp" alt="Realismo 11">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_26441.webp" alt="Realismo 12">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_2781 21.webp" alt="Realismo 13">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="1300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_3923 21.webp" alt="Realismo 14">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_6110 21.webp" alt="Realismo 15">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_6633 21.webp" alt="Realismo 16">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_6790 31.webp" alt="Realismo 17">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_67911.webp" alt="Realismo 18">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_6837 21.webp" alt="Realismo 19">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_71281.webp" alt="Realismo 20">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="2000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_7477 31.webp" alt="Realismo 21">
                            </div>
                        </div>
                    
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_7770 41.webp" alt="Realismo 22">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_8166 21.webp" alt="Realismo 23">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_82931.webp" alt="Realismo 24">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Realismo/IMG_8450 21.webp" alt="Realismo 25">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Realismo/IMG_1115.webp" alt="Realismo 26">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Realismo/IMG_1116.webp" alt="Realismo 27">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Realismo/IMG_1117.webp" alt="Realismo 28">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Realismo/IMG_1118.webp" alt="Realismo 29">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Realismo/IMG_1120.webp" alt="Realismo 30">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="3000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Realismo/IMG_9774.webp" alt="Realismo 31">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="tradicional" class="page">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/TRADICIONAL.webp" data-lazy-video>
                    <source data-src="videos/headers/TRADICIONAL.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <div class="style-description" data-aos="fade-up">
                <p><span lang="es">El tradicional es para los que saben lo que quieren. Diseños sólidos, líneas contundentes y colores que aguantan el paso del tiempo. En Kaos Tattoo respetamos la esencia clásica pero con una ejecución impecable, para que lleves un tatuaje con historia y con presencia.</span><span lang="en">Traditional is for those who know what they want. Solid designs, bold lines and colours that stand the test of time. At Kaos Tattoo we respect the classic essence but with flawless execution, so you wear a tattoo with history and presence.</span></p>
            </div>
            <div class="fineline-gallery">
                <div class="gallery-masonry">
                    <div class="masonry-item" data-aos="fade-up" data-aos-delay="100">
                        <div class="image-container">
                            <img loading="lazy" src="images/STYLES/Tradicional Old School/2EC11038-1E3D-4F4B-980E-92D45E2D2DCB 2.webp" alt="Tradicional 1">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/FEF812DE-B03E-4665-94A8-83AC27F34439.webp" alt="Tradicional 2">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/Ilustración_sin_título 15.webp" alt="Tradicional 3">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_0630 21.webp" alt="Tradicional 4">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_1287 2.webp" alt="Tradicional 5">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_16171.webp" alt="Tradicional 6">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_16401.webp" alt="Tradicional 7">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_16571.webp" alt="Tradicional 8">
                            </div>
                        </div>
                        
                        <div class="masonry-item tall" data-aos="fade-up" data-aos-delay="900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_24691.webp" alt="Tradicional 9">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_26791.webp" alt="Tradicional 10">
                            </div>
                        </div>
                        
                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_28441.webp" alt="Tradicional 11">
                            </div>
                        </div>
                        
                        <div class="masonry-item wide" data-aos="fade-up" data-aos-delay="1200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_33241.webp" alt="Tradicional 12">
                            </div>
                        </div>
                    

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_37451.webp" alt="Tradicional 13">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_4342 21.webp" alt="Tradicional 14">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_4371 21.webp" alt="Tradicional 15">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_43771.webp" alt="Tradicional 16">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_50121.webp" alt="Tradicional 17">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_50171.webp" alt="Tradicional 18">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="1900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_60911.webp" alt="Tradicional 19">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2000">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_6658 21.webp" alt="Tradicional 20">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2100">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_66681.webp" alt="Tradicional 21">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2200">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_68551.webp" alt="Tradicional 22">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2300">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_68561.webp" alt="Tradicional 23">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2400">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_7387 21.webp" alt="Tradicional 24">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2500">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_8226 21.webp" alt="Tradicional 25">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2600">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Tradicional Old School/IMG_8892.webp" alt="Tradicional 26">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2700">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Tradicional/IMG_0514.webp" alt="Tradicional 27">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2800">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Tradicional/IMG_0950.webp" alt="Tradicional 28">
                            </div>
                        </div>

                        <div class="masonry-item" data-aos="fade-up" data-aos-delay="2900">
                            <div class="image-container">
                                <img loading="lazy" src="images/STYLES/Nuevas_Fotos_11abril2026/Tradicional/IMG_9887.webp" alt="Tradicional 29">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div id="tatuajes" class="page<?php echo pageActive('tatuajes', $pageId); ?>">
            <section class="tattoo-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/TATUAJES.webp" data-lazy-video>
                    <source data-src="videos/headers/TATUAJES.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>

            <section class="page-heading">
                <h1><span lang="es">Estudio de tatuajes en Alicante</span><span lang="en">Tattoo Studio in Alicante</span></h1>
            </section>
            
            <section class="tattoo-intro">
                <div class="tattoo-intro-overlay"></div>
                <div class="tattoo-intro-content">
                    <h2><span lang="es">¿Te gustaría hacerte un tattoo?</span><span lang="en">Thinking about getting a tattoo?</span></h2>
                    <p><span lang="es">Hay quien llega con el diseño clarísimo y quien solo tiene una imagen, una historia o una sensación dando vueltas en la cabeza. Las dos cosas nos sirven.</span><span lang="en">Some people arrive with a crystal-clear design and others just have an image, a story or a feeling going round their head. Both work for us.</span></p>
                    <p><span lang="es">En Kaos Tattoo escuchamos lo que quieres contar, tiramos del hilo contigo y le damos forma sin cargarnos lo que hacía especial tu idea. Tú pones la esencia; nosotros, ocho años de experiencia para convertirla en un tatuaje sólido, bien ejecutado y pensado para seguir funcionando cuando pase el tiempo.</span><span lang="en">At Kaos Tattoo we listen to what you want to say, pull the thread with you and shape it without ruining what made your idea special. You bring the essence; we bring eight years of experience to turn it into a solid, well-executed tattoo, made to keep working as time goes by.</span></p>
                    <p><span lang="es">La meta es sencilla: que salgas pensando «esto es exactamente lo que quería, pero incluso mejor de lo que imaginaba».</span><span lang="en">The goal is simple: that you leave thinking "this is exactly what I wanted, but even better than I imagined".</span></p>
                    <a class="btn btn-primary" href="/contacto/?tipo=tatuaje" data-page="contacto" data-tipo="tatuaje"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Tu idea no sale de una plantilla</span><span lang="en">Your idea doesn't come from a template</span></h2>
                    <p><span lang="es">Todos nuestros tatuajes se trabajan de forma personalizada. Si vienes con una referencia de internet, no la calcamos: la adaptamos a ti, a la zona y al tamaño para que el resultado tenga identidad propia y funcione bien sobre la piel.</span><span lang="en">All our tattoos are worked on in a personalised way. If you come with a reference from the internet, we don't trace it: we adapt it to you, to the area and the size so the result has its own identity and works well on the skin.</span></p>
                    <p><span lang="es">También puedes elegir uno de los diseños disponibles de nuestros artistas, hacerte un flash o plantearnos un cover-up. En los covers preferimos ver el tatuaje anterior en persona: así podemos decirte con honestidad qué se puede hacer y qué opción va a darte un mejor resultado.</span><span lang="en">You can also choose one of our artists' available designs, get a flash or ask us about a cover-up. For cover-ups we prefer to see the previous tattoo in person: that way we can honestly tell you what can be done and which option will give you the best result.</span></p>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">¿Qué estilo buscas?</span><span lang="en">What style are you after?</span></h2>
                    <p class="tattoo-section-lead"><span lang="es">Trabajamos proyectos de muchos estilos, aunque estos son algunos de los que más nos piden en el estudio:</span><span lang="en">We work on projects in many styles, though these are some of the most requested at the studio:</span></p>
                    <div class="tattoo-styles-grid">
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Fineline</span><span lang="en">Fineline</span></h3>
                            <p><span lang="es">Líneas finas, limpias y delicadas. Aquí no se trata solo de hacerlas pequeñas, sino de hacerlas bien para que el tatuaje conserve su fuerza con el tiempo.</span><span lang="en">Thin, clean and delicate lines. It's not just about making them small, but about doing them well so the tattoo keeps its strength over time.</span></p>
                            <a class="tattoo-inline-link" href="/estilos/#fineline" data-page="fineline"><span lang="es">Ver tatuajes fine line</span><span lang="en">See fine line tattoos</span></a>
                        </article>
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Realismo y microrealismo</span><span lang="en">Realism and micro-realism</span></h3>
                            <p><span lang="es">Retratos, objetos y escenas trabajados con profundidad y detalle, tanto en formatos grandes como en composiciones más pequeñas.</span><span lang="en">Portraits, objects and scenes worked with depth and detail, both in large formats and smaller compositions.</span></p>
                            <a class="tattoo-inline-link" href="/estilos/#realismo" data-page="realismo"><span lang="es">Ver tatuajes de realismo</span><span lang="en">See realism tattoos</span></a>
                            <a class="tattoo-inline-link" href="/estilos/#microrealismo" data-page="microrealismo"><span lang="es">Ver microrealismo</span><span lang="en">See micro-realism</span></a>
                        </article>
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Tradicional y old school</span><span lang="en">Traditional and old school</span></h3>
                            <p><span lang="es">Líneas firmes, composiciones directas y diseños con mucha personalidad, en negro o a color.</span><span lang="en">Firm lines, direct compositions and designs with plenty of personality, in black or colour.</span></p>
                            <a class="tattoo-inline-link" href="/estilos/#tradicional" data-page="tradicional"><span lang="es">Ver tatuajes tradicionales</span><span lang="en">See traditional tattoos</span></a>
                        </article>
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Y bastante más</span><span lang="en">And plenty more</span></h3>
                            <p><span lang="es">Blackwork, anime, geométrico, japonés, lettering, cartoon… Si no sabes cómo se llama tu estilo, mándanos la idea y nosotros le ponemos nombre.</span><span lang="en">Blackwork, anime, geometric, Japanese, lettering, cartoon… If you don't know what your style is called, send us the idea and we'll name it for you.</span></p>
                            <a class="tattoo-inline-link" href="/estilos/" data-page="fineline"><span lang="es">Ver todos los estilos</span><span lang="en">See all styles</span></a>
                        </article>
                    </div>
                    <p class="tattoo-section-note"><span lang="es">Valoramos cada proyecto por separado. Lo único que no tatuamos son ideas que hagan apología del odio, la violencia o ideologías que no queremos llevar ni en la piel ni en el estudio.</span><span lang="en">We assess each project individually. The only thing we won't tattoo are ideas that promote hate, violence or ideologies we don't want on our skin or in the studio.</span></p>
                </div>
            </section>

            <section class="tattoo-section">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">Nuestro tatuador: sin piloto automático</span><span lang="en">Our tattoo artist: no autopilot</span></h2>
                    <article class="tattoo-artist-card">
                        <h3>Tailor</h3>
                        <p><span lang="es">Polivalente por naturaleza y bastante obsesivo con estudiar cada proyecto antes de empezar. Sus puntos fuertes son el fine line, el realismo, el microrealismo y el old school, aunque se mueve con soltura entre estilos muy distintos.</span><span lang="en">Versatile by nature and rather obsessive about studying every project before starting. His strong points are fine line, realism, micro-realism and old school, though he moves easily between very different styles.</span></p>
                        <a class="tattoo-inline-link" href="/equipo/#portfolio-tailor" data-page="portfolio-tailor"><span lang="es">Ver el portfolio de Tailor</span><span lang="en">See Tailor's portfolio</span></a>
                    </article>
                    <p class="tattoo-section-note"><span lang="es">No tienes que elegir estilo ni tenerlo todo resuelto antes de escribirnos. Cuéntanos qué quieres hacerte y te orientamos hacia lo que mejor encaje con tu proyecto.</span><span lang="en">You don't have to pick a style or have everything figured out before writing to us. Tell us what you want and we'll guide you towards what best fits your project.</span></p>
                </div>
            </section>

            <section class="tattoo-process has-tattoo-bg">
                <div class="process-container">
                    <h2 class="process-title"><span lang="es">De la idea a la piel, sin dramas</span><span lang="en">From idea to skin, no drama</span></h2>
                    <ol class="tattoo-steps">
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">1</span>
                            <h3><span lang="es">Cuéntanos lo que tienes en mente</span><span lang="en">Tell us what you have in mind</span></h3>
                            <p><span lang="es">Dinos la zona, el tamaño aproximado y todo lo que ya sepas sobre la idea. Puedes añadir referencias, pero no necesitas llegar con el diseño resuelto, elegir tatuador ni proponer una fecha.</span><span lang="en">Tell us the area, the approximate size and everything you already know about the idea. You can add references, but you don't need to arrive with the design resolved, choose an artist or propose a date.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">2</span>
                            <h3><span lang="es">Le damos forma contigo</span><span lang="en">We shape it with you</span></h3>
                            <p><span lang="es">Revisamos la propuesta, resolvemos dudas y te recomendamos el estilo y los ajustes que puedan mejorar la colocación o el resultado final.</span><span lang="en">We review the proposal, answer your questions and recommend the style and adjustments that can improve the placement or final result.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">3</span>
                            <h3><span lang="es">Cerramos precio y fecha</span><span lang="en">We settle price and date</span></h3>
                            <p><span lang="es">Cuando el proyecto está claro, te damos una orientación de precio y buscamos una cita. La reserva se confirma con una señal que se descuenta íntegramente del total.</span><span lang="en">When the project is clear, we give you a price guide and find an appointment. The booking is confirmed with a deposit that is fully deducted from the total.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">4</span>
                            <h3><span lang="es">Preparamos el diseño</span><span lang="en">We prepare the design</span></h3>
                            <p><span lang="es">Con la cita reservada, trabajamos la propuesta y te enseñamos el resultado unos días antes de la sesión. Si algún detalle necesita un ajuste, lo hablamos antes de tatuar.</span><span lang="en">With the appointment booked, we work on the proposal and show you the result a few days before the session. If any detail needs adjusting, we talk it over before tattooing.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">5</span>
                            <h3><span lang="es">Revisamos antes de empezar</span><span lang="en">We check before starting</span></h3>
                            <p><span lang="es">Ya en el estudio comprobamos contigo diseño, tamaño y colocación. Solo empezamos cuando todo está claro y te sientes a gusto con la decisión.</span><span lang="en">Once at the studio we check design, size and placement with you. We only start when everything is clear and you feel comfortable with the decision.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">6</span>
                            <h3><span lang="es">Seguimos ahí cuando sales</span><span lang="en">We're still there when you leave</span></h3>
                            <p><span lang="es">Te llevas los cuidados por escrito y puedes consultarnos por teléfono o WhatsApp durante toda la curación. Cuando haya cicatrizado, lo revisamos y hacemos un pequeño retoque sin coste si realmente lo necesita.</span><span lang="en">You take the aftercare in writing and can reach us by phone or WhatsApp throughout the healing. Once healed, we review it and do a small touch-up at no cost if it really needs it.</span></p>
                        </li>
                    </ol>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">¿Cuánto cuesta un tatuaje?</span><span lang="en">How much does a tattoo cost?</span></h2>
                    <p><span lang="es">Un tatuaje no se cobra al peso. El precio cambia según el tamaño, la zona, el estilo, el detalle y las horas de trabajo. Por eso preferimos ver primero tu idea y darte una cifra que tenga sentido, no soltarte un precio al azar.</span><span lang="en">A tattoo isn't charged by weight. The price changes depending on size, area, style, detail and hours of work. That's why we prefer to see your idea first and give you a figure that makes sense, rather than throwing out a random price.</span></p>
                    <ul class="tattoo-check-list">
                        <li><span lang="es">El precio mínimo de un tatuaje es de 50&nbsp;€.</span><span lang="en">The minimum price for a tattoo is €50.</span></li>
                        <li><span lang="es">Una sesión de unas cuatro o cinco horas suele estar entre 260&nbsp;€ y 300&nbsp;€.</span><span lang="en">A session of about four or five hours is usually between €260 and €300.</span></li>
                        <li><span lang="es">El diseño y los ajustes necesarios están incluidos en el precio acordado.</span><span lang="en">The design and any necessary adjustments are included in the agreed price.</span></li>
                    </ul>
                    <p><span lang="es">Para bloquear la fecha pedimos una señal, que después se resta del total. Puedes entregarla en efectivo, por Bizum o mediante un enlace de pago con tarjeta.</span><span lang="en">To lock in the date we ask for a deposit, which is then subtracted from the total. You can pay it in cash, by Bizum or through a card payment link.</span></p>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Antes de venir: come, duerme y no hagas inventos</span><span lang="en">Before you come: eat, sleep and don't improvise</span></h2>
                    <ul class="tattoo-check-list">
                        <li><span lang="es">Ven descansado, aseado y habiendo comido.</span><span lang="en">Come well-rested, clean and having eaten.</span></li>
                        <li><span lang="es">Hidrátate bien durante las horas previas.</span><span lang="en">Stay well hydrated in the hours beforehand.</span></li>
                        <li><span lang="es">Evita el alcohol y las drogas durante las 24 horas anteriores.</span><span lang="en">Avoid alcohol and drugs during the 24 hours before.</span></li>
                        <li><span lang="es">Lleva ropa cómoda que permita acceder fácilmente a la zona.</span><span lang="en">Wear comfortable clothing that gives easy access to the area.</span></li>
                        <li><span lang="es">Si estás pensando en utilizar crema anestésica, no te la apliques por tu cuenta antes de venir.</span><span lang="en">If you're thinking of using numbing cream, don't apply it on your own before coming.</span></li>
                    </ul>
                    <p><span lang="es">Puedes venir acompañado siempre que haya espacio y no interfiera con el trabajo, la higiene o la comodidad durante la sesión.</span><span lang="en">You can bring someone as long as there's space and it doesn't interfere with the work, hygiene or comfort during the session.</span></p>
                </div>
            </section>

            <aside class="inline-cta has-tattoo-bg" aria-label="Contacto para tatuajes">
                <p><span lang="es">¿Ya estás visualizando el resultado? Nosotros también queremos verlo.</span><span lang="en">Already picturing the result? We want to see it too.</span></p>
                <a class="btn btn-primary" href="/contacto/?tipo=tatuaje" data-page="contacto" data-tipo="tatuaje"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
            </aside>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Durante tu sesión</span><span lang="en">During your session</span></h2>
                    <p><span lang="es">Antes de tocar una aguja repasamos contigo el diseño, el tamaño y la colocación. Ese es el momento de resolver la última duda o mover un detalle: preferimos dedicarle cinco minutos más a la decisión que muchos años a pensar «lo habría puesto un poco más arriba».</span><span lang="en">Before touching a needle we go over the design, size and placement with you. That's the moment to settle the last doubt or move a detail: we'd rather spend five more minutes on the decision than many years thinking "I'd have put it a bit higher".</span></p>
                    <p><span lang="es">Preparamos y protegemos el puesto para cada persona, desinfectamos las superficies y utilizamos agujas y cartuchos estériles y de un solo uso, que se abren delante de ti. También trabajamos con tintas y materiales profesionales que cumplen la normativa europea vigente. Tú puedes centrarte en el tatuaje; del resto nos ocupamos nosotros.</span><span lang="en">We set up and protect the station for each person, disinfect surfaces and use sterile, single-use needles and cartridges that are opened in front of you. We also work with professional inks and materials that comply with current European regulations. You can focus on the tattoo; we take care of the rest.</span></p>
                    <p><span lang="es">Si vienes de fuera, podemos organizar el proyecto en inglés antes de que llegues a Alicante y seguir en contacto por WhatsApp cuando hayas vuelto a casa.</span><span lang="en">If you're coming from abroad, we can organise the project in English before you arrive in Alicante and stay in touch by WhatsApp once you're back home.</span></p>
                </div>
            </section>

            <aside class="reviews-block has-tattoo-bg" aria-label="Reseñas">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">Más de 200 reseñas nos avalan</span><span lang="en">Over 200 reviews back us up</span></h2>
                    <p><span lang="es">La mejor forma de saber cómo se vive Kaos Tattoo es escuchar a quienes ya han pasado por el estudio. Más de 200 reseñas en Google respaldan nuestro trabajo y la manera en la que acompañamos cada tatuaje de principio a fin.</span><span lang="en">The best way to know what Kaos Tattoo feels like is to listen to those who've already been to the studio. Over 200 Google reviews back our work and the way we accompany every tattoo from start to finish.</span></p>
                    <a class="reviews-link" href="https://www.google.com/searchviewer/10?svid=CAwSHRIbCgNwdnESFENnMHZaeTh4TVhCM01YaGlkREV3GAo" target="_blank" rel="noopener noreferrer"><span lang="es">Leer reseñas en Google</span><span lang="en">Read reviews on Google</span></a>
                </div>
            </aside>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Cómo cuidar tu tatuaje</span><span lang="en">How to care for your tattoo</span></h2>
                    <h3 class="tattoo-subheading"><span lang="es">Cura tradicional</span><span lang="en">Traditional healing</span></h3>
                    <ol class="tattoo-care-steps">
                        <li><span lang="es"><strong>Retira el papel film dos horas después de hacerte el tatuaje y no vuelvas a taparlo.</strong> Si hemos utilizado otro tipo de protector, sigue el tiempo concreto que te indiquemos antes de salir del estudio.</span><span lang="en"><strong>Remove the cling film two hours after getting the tattoo and don't cover it again.</strong> If we've used another type of protector, follow the specific time we tell you before leaving the studio.</span></li>
                        <li><span lang="es"><strong>Lávalo con agua y jabón neutro</strong>, siempre con las manos limpias y sin frotar la zona.</span><span lang="en"><strong>Wash it with water and neutral soap</strong>, always with clean hands and without rubbing the area.</span></li>
                        <li><span lang="es"><strong>Sécalo con papel de cocina y toques suaves.</strong> No arrastres el papel sobre la piel y deja después el tatuaje al aire.</span><span lang="en"><strong>Dry it with kitchen paper and gentle dabs.</strong> Don't drag the paper over the skin and then leave the tattoo to air.</span></li>
                        <li><span lang="es"><strong>Repite el lavado tres veces al día</strong>, siempre con agua y jabón neutro.</span><span lang="en"><strong>Repeat the wash three times a day</strong>, always with water and neutral soap.</span></li>
                        <li><span lang="es"><strong>Empieza a utilizar crema específica para tatuajes a partir del tercer día.</strong> Aplica poca cantidad y extiéndela en una capa muy fina.</span><span lang="en"><strong>Start using tattoo-specific cream from the third day.</strong> Apply a small amount and spread it in a very thin layer.</span></li>
                        <li><span lang="es"><strong>No rasques ni arranques las costras.</strong> Evita el sol y no te bañes en playas o piscinas mientras el tatuaje se cura.</span><span lang="en"><strong>Don't scratch or pick the scabs.</strong> Avoid the sun and don't swim in the sea or pools while the tattoo heals.</span></li>
                        <li><span lang="es"><strong>No hagas deporte durante la primera semana</strong>, especialmente si provoca sudor, roce o presión sobre la zona tatuada.</span><span lang="en"><strong>Don't do sport during the first week</strong>, especially if it causes sweat, friction or pressure on the tattooed area.</span></li>
                        <li><span lang="es"><strong>Pregúntanos ante cualquier duda.</strong> Puedes escribirnos o llamarnos durante todo el proceso de curación.</span><span lang="en"><strong>Ask us if you have any doubts.</strong> You can message or call us throughout the healing process.</span></li>
                    </ol>
                    <h3 class="tattoo-subheading"><span lang="es">¿Te tatúas en verano?</span><span lang="en">Getting tattooed in summer?</span></h3>
                    <p><span lang="es">Para algunos tatuajes contamos con un producto de curación resistente al agua que ayuda a proteger la zona y hace más sencillos los cuidados de los primeros días. El día de la sesión te explicaremos si es adecuado para tu tatuaje y cómo debes cuidarlo.</span><span lang="en">For some tattoos we have a water-resistant healing product that helps protect the area and makes the first days' care easier. On the day of the session we'll explain whether it's suitable for your tattoo and how to look after it.</span></p>
                    <p><span lang="es">En cualquier caso, tendrás que evitar el sol directo mientras la piel se recupera.</span><span lang="en">In any case, you'll need to avoid direct sun while the skin recovers.</span></p>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">Preguntas frecuentes sobre tatuajes</span><span lang="en">Frequently asked questions about tattoos</span></h2>
                    <div class="faq-accordion">
                        <details class="faq-details">
                            <summary><span lang="es">¿Duele mucho hacerse un tatuaje?</span><span lang="en">Does getting a tattoo hurt a lot?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">El dolor depende de la zona, el tamaño y la tolerancia de cada persona. Suele sentirse como una molestia continua y soportable. Si es lo que más te preocupa, cuéntanoslo antes de la sesión y te orientamos sin hacerte el valiente.</span><span lang="en">Pain depends on the area, the size and each person's tolerance. It usually feels like a continuous, bearable discomfort. If it's your main worry, tell us before the session and we'll guide you without making you play the hero.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuánto se tarda?</span><span lang="en">How long does it take?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Depende del tamaño, el detalle, el estilo y la zona. Cuando veamos tu idea podremos darte una estimación realista. Como referencia, una sesión completa suele durar entre cuatro y cinco horas.</span><span lang="en">It depends on the size, detail, style and area. Once we see your idea we can give you a realistic estimate. As a reference, a full session usually lasts between four and five hours.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Necesito cita previa?</span><span lang="en">Do I need an appointment?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Sí para los proyectos que necesitan diseño y preparación. También podemos aceptar tatuajes sencillos sin cita cuando hay disponibilidad, pero escribirnos antes te ahorra un paseo en balde.</span><span lang="en">Yes for projects that need design and preparation. We can also take simple tattoos without an appointment when there's availability, but writing first saves you a wasted trip.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Copiáis diseños de internet?</span><span lang="en">Do you copy designs from the internet?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Podemos utilizar una imagen como referencia, pero la adaptamos para crear algo propio y que funcione bien en tu cuerpo. También puedes elegir un diseño disponible de nuestros artistas.</span><span lang="en">We can use an image as a reference, but we adapt it to create something original that works well on your body. You can also choose an available design from our artists.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuándo veré el diseño?</span><span lang="en">When will I see the design?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Preparamos el diseño después de reservar la cita y normalmente lo enseñamos unos días antes de tatuar. Si hay que ajustar algún detalle, lo hablamos antes de empezar.</span><span lang="en">We prepare the design after booking the appointment and usually show it a few days before tattooing. If any detail needs adjusting, we discuss it before starting.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Qué es normal durante los primeros días?</span><span lang="en">What's normal during the first few days?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Puede haber algo de enrojecimiento, sensibilidad, calor o ligera inflamación. En algunos tatuajes también aparece costra: déjala tranquila y no la arranques.</span><span lang="en">There may be some redness, sensitivity, warmth or slight swelling. Some tattoos also form scabs: leave them alone and don't pick them.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuándo debería pedir ayuda?</span><span lang="en">When should I seek help?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Un dolor intenso o que empeora, pus, fiebre, mal olor o un enrojecimiento que se extiende no forman parte de la evolución habitual. Escríbenos para contarnos qué ocurre y busca atención sanitaria si los síntomas son importantes o empeoran.</span><span lang="en">Intense or worsening pain, pus, fever, bad smell or spreading redness are not part of the usual progression. Message us to tell us what's happening and seek medical attention if the symptoms are serious or worsen.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puedo utilizar crema anestésica?</span><span lang="en">Can I use numbing cream?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Si te preocupa el dolor, cuéntanoslo antes de la cita. No te apliques ninguna crema anestésica por tu cuenta: el producto, la cantidad, el tiempo y la zona importan. Te ayudaremos a plantear la sesión para que estés lo más cómodo posible y, si quieres valorar un anestésico, consúltalo antes con un médico o farmacéutico.</span><span lang="en">If pain worries you, tell us before the appointment. Don't apply any numbing cream on your own: the product, amount, timing and area all matter. We'll help you plan the session so you're as comfortable as possible and, if you want to consider a numbing agent, check first with a doctor or pharmacist.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuánto tarda en curarse?</span><span lang="en">How long does it take to heal?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">La curación superficial suele durar entre dos y tres semanas. La piel puede necesitar entre cuatro y seis semanas para completar el proceso.</span><span lang="en">Surface healing usually takes two to three weeks. The skin may need four to six weeks to complete the process.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Me puedo tatuar en verano?</span><span lang="en">Can I get tattooed in summer?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Sí. Tatuarte en verano no es un problema si puedes cuidar bien la zona durante la curación. Tendrás que evitar el sol directo, el sudor excesivo y el roce sobre el tatuaje. Si sigues la cura tradicional, también deberás esperar a que haya cicatrizado antes de bañarte en el mar o la piscina.</span><span lang="en">Yes. Getting tattooed in summer isn't a problem if you can care for the area well during healing. You'll need to avoid direct sun, excessive sweat and friction on the tattoo. If you follow traditional healing, you'll also need to wait until it has healed before swimming in the sea or pool.</span></p><p><span lang="es">Para algunos tatuajes contamos con un producto de curación resistente al agua que puede hacer más cómodos los primeros días. El día de la sesión te explicaremos si es adecuado para tu caso y qué pautas debes seguir. Eso sí: sea cual sea el método de curación, el sol directo sigue quedándose fuera del plan.</span><span lang="en">For some tattoos we have a water-resistant healing product that can make the first days more comfortable. On the day of the session we'll explain whether it suits your case and what guidelines to follow. That said: whatever the healing method, direct sun stays out of the plan.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Me puedo tatuar si tengo lunares?</span><span lang="en">Can I get tattooed if I have moles?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Sí, pero no tatuamos directamente sobre ellos. Adaptamos el diseño para respetarlos y permitir que puedan seguir controlándose.</span><span lang="en">Yes, but we don't tattoo directly over them. We adapt the design to respect them and allow them to keep being monitored.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puedo tatuarme durante el embarazo o la lactancia?</span><span lang="en">Can I get tattooed during pregnancy or breastfeeding?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">No recomendamos tatuarse durante el embarazo. Durante la lactancia valoramos cada caso antes de aceptar la reserva.</span><span lang="en">We don't recommend getting tattooed during pregnancy. During breastfeeding we assess each case before accepting the booking.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puede tatuarse una persona menor de edad?</span><span lang="en">Can a minor get tattooed?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Debe venir presencialmente con su padre, madre o tutor legal y traer la documentación de ambos. El equipo valorará también el proyecto, la zona y si resulta adecuado hacerlo.</span><span lang="en">They must come in person with their parent or legal guardian and bring both parties' ID. The team will also assess the project, the area and whether it's appropriate to do it.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Están incluidos los retoques?</span><span lang="en">Are touch-ups included?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Cuando el tatuaje haya curado, lo revisamos. Si realmente necesita un pequeño retoque, lo hacemos sin coste adicional.</span><span lang="en">Once the tattoo has healed, we review it. If it really needs a small touch-up, we do it at no extra cost.</span></p></div>
                        </details>
                    </div>
                </div>
            </section>

            <section class="final-cta has-tattoo-bg">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">Vale, ¿qué tienes en mente?</span><span lang="en">Alright, what do you have in mind?</span></h2>
                    <p><span lang="es">Una idea cerrada, tres capturas de pantalla o una explicación que todavía no sabes muy bien cómo contar. Mándanos lo que tengas y empezamos desde ahí.</span><span lang="en">A finished idea, three screenshots or an explanation you're not quite sure how to put into words. Send us whatever you have and we'll start from there.</span></p>
                    <a class="btn btn-primary" href="/contacto/?tipo=tatuaje" data-page="contacto" data-tipo="tatuaje"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
                </div>
            </section>
        </div>

        <div id="piercings" class="page<?php echo pageActive('piercings', $pageId); ?>">
            <section class="tattoo-hero piercings-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/PIERCINGS.webp" data-lazy-video>
                    <source data-src="videos/headers/PIERCINGS.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>

            <section class="page-heading">
                <h1><span lang="es">Piercing en Alicante</span><span lang="en">Piercing in Alicante</span></h1>
            </section>

            <section class="piercing-intro">
                <div class="piercing-intro-overlay"></div>
                <div class="piercing-intro-content">
                    <p class="piercing-intro-tagline"><span lang="es">QUE DUELA ES RELATIVO, QUE FAVOREZCA ES UN HECHO</span><span lang="en">PAIN IS RELATIVE, LOOKING GREAT IS A FACT</span></p>
                    <h2><span lang="es">¿Te gustaría hacerte un piercing?</span><span lang="en">Thinking about getting a piercing?</span></h2>
                    <p><span lang="es">Puede que sepas exactamente qué piercing quieres o que solo tengas clara la zona. Da igual: La Greka te ayudará a encontrar una colocación y una joya que encajen contigo y, lo más importante, con tu anatomía.</span><span lang="en">You may know exactly which piercing you want or just have the area in mind. Either way: La Greka will help you find a placement and a piece of jewellery that suit you and, most importantly, your anatomy.</span></p>
                    <p><span lang="es">Antes de perforar miramos, preguntamos y te explicamos lo que vamos a hacer. Nada de elegir una pieza bonita, pinchar y hasta luego. Queremos que salgas con un piercing que te favorezca y sabiendo cómo cuidarlo desde el primer día.</span><span lang="en">Before piercing we look, ask questions and explain what we're going to do. No picking a pretty piece, piercing and goodbye. We want you to leave with a piercing that flatters you and knowing how to care for it from day one.</span></p>
                    <a class="btn btn-primary" href="/contacto/?tipo=piercing" data-page="contacto" data-tipo="piercing"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">¿Dónde te lo quieres hacer?</span><span lang="en">Where do you want it?</span></h2>
                    <p class="tattoo-section-lead"><span lang="es">Trabajamos piercings en distintas zonas del cuerpo. La lista es larga; la decisión final siempre depende de que la colocación resulte adecuada para tu anatomía.</span><span lang="en">We do piercings on different parts of the body. The list is long; the final decision always depends on whether the placement is suitable for your anatomy.</span></p>
                    <div class="tattoo-styles-grid">
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Oreja</span><span lang="en">Ear</span></h3>
                            <p><span lang="es">Lóbulo, upper lobe, transverse lobe, helix, flat, rook, daith, conch, tragus, anti-tragus, snug e industrial.</span><span lang="en">Lobe, upper lobe, transverse lobe, helix, flat, rook, daith, conch, tragus, anti-tragus, snug and industrial.</span></p>
                        </article>
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Nariz, ceja y rostro</span><span lang="en">Nose, eyebrow and face</span></h3>
                            <p><span lang="es">Nostril, high nostril, double nostril, paired nostril, septum, bridge y eyebrow.</span><span lang="en">Nostril, high nostril, double nostril, paired nostril, septum, bridge and eyebrow.</span></p>
                        </article>
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Labios y boca</span><span lang="en">Lips and mouth</span></h3>
                            <p><span lang="es">Labret, vertical labret, Ashley, jestrum, medusa o philtrum, Monroe, Madonna, dahlia, snake bites, spider bites, shark bites, canine bites, angel fangs, cyber bites, dolphin bites, tongue, venom y snake eyes.</span><span lang="en">Labret, vertical labret, Ashley, jestrum, medusa or philtrum, Monroe, Madonna, dahlia, snake bites, spider bites, shark bites, canine bites, angel fangs, cyber bites, dolphin bites, tongue, venom and snake eyes.</span></p>
                        </article>
                        <article class="tattoo-style-card">
                            <h3><span lang="es">Cuerpo</span><span lang="en">Body</span></h3>
                            <p><span lang="es">Nipple horizontal o vertical, navel, floating navel, inverse o reverse navel, double navel y surface.</span><span lang="en">Horizontal or vertical nipple, navel, floating navel, inverse or reverse navel, double navel and surface.</span></p>
                        </article>
                    </div>
                    <p class="tattoo-section-note"><span lang="es">No realizamos perforaciones genitales ni piercings a bebés o menores de 14 años. Entre los 14 y los 17, la persona debe venir con su padre, madre o tutor legal y presentar la documentación de ambos.</span><span lang="en">We do not perform genital piercings or piercings on babies or under-14s. Between 14 and 17, the person must come with a parent or legal guardian and present both parties' ID.</span></p>
                </div>
            </section>

            <section class="tattoo-section">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">La Greka: buen pulso y mejor rollo</span><span lang="en">La Greka: steady hands and great vibes</span></h2>
                    <article class="piercing-artist-card">
                        <h3>La Greka</h3>
                        <p><span lang="es">La Greka lleva alrededor de cinco años dedicada al piercing. Se formó directamente con un profesional con más de veinte años de trayectoria y sigue actualizándose para no quedarse quieta mientras el oficio avanza.</span><span lang="en">La Greka has been dedicated to piercing for around five years. She trained directly under a professional with over twenty years of experience and keeps updating her skills so she doesn't stand still while the craft moves forward.</span></p>
                        <p><span lang="es">Está especializada en piercings de oreja y ombligo, y presta mucha atención a la anatomía, la colocación y la joya inicial. Quienes se ponen en sus manos suelen recordar también el trato y el humor: la aguja dura un momento; el buen ambiente ayuda bastante más.</span><span lang="en">She specialises in ear and navel piercings and pays close attention to anatomy, placement and the initial jewellery. Those who put themselves in her hands also tend to remember the care and the humour: the needle lasts a moment; the good vibes help a lot more.</span></p>
                        <a class="tattoo-inline-link" href="/anilladora/#portfolio-greka" data-page="portfolio-greka"><span lang="es">Ver los trabajos de La Greka</span><span lang="en">See La Greka's work</span></a>
                    </article>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Tu anatomía manda</span><span lang="en">Your anatomy calls the shots</span></h2>
                    <p><span lang="es">Una foto de Pinterest puede quedar increíble en otra persona y no tener sentido en tu oreja, tu ombligo o tu nariz. Antes de hacer nada revisamos la forma y el tejido de la zona para comprobar si esa colocación puede funcionar contigo.</span><span lang="en">A Pinterest photo can look amazing on someone else and make no sense on your ear, navel or nose. Before doing anything we check the shape and tissue of the area to see whether that placement can work for you.</span></p>
                    <p><span lang="es">Si no es adecuada, te explicaremos por qué y buscaremos una alternativa. Y si no hay una opción que nos convenza, preferimos decirte que no. Hacer un piercing porque sí es rápido; hacerlo con criterio es otra historia.</span><span lang="en">If it's not suitable, we'll explain why and look for an alternative. And if there's no option we're convinced by, we'd rather say no. Doing a piercing just because is quick; doing it with good judgement is another story.</span></p>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">La primera joya no se elige solo porque sea bonita</span><span lang="en">The first jewellery isn't chosen just because it's pretty</span></h2>
                    <p><span lang="es">La pieza inicial tiene que encajar con la zona, dejar espacio para la evolución de la perforación y estar preparada para una primera puesta. Por eso, al hacer el piercing solo utilizamos joyería del estudio: sabemos qué material es, de dónde viene y cómo se ha preparado.</span><span lang="en">The initial piece must fit the area, leave room for the piercing to evolve and be ready for a first wear. That's why we only use studio jewellery when performing the piercing: we know what material it is, where it comes from and how it was prepared.</span></p>
                    <ul class="tattoo-check-list">
                        <li><span lang="es">La joya inicial está incluida en el precio.</span><span lang="en">The initial jewellery is included in the price.</span></li>
                        <li><span lang="es">Puedes elegir acero quirúrgico esterilizado o titanio de grado implante.</span><span lang="en">You can choose sterilised surgical steel or implant-grade titanium.</span></li>
                        <li><span lang="es">La opción de titanio tiene un suplemento de 7&nbsp;€.</span><span lang="en">The titanium option has a supplement of €7.</span></li>
                        <li><span lang="es">Te ayudamos a escoger una pieza adecuada para el piercing y para tu anatomía.</span><span lang="en">We help you choose a piece suited to the piercing and your anatomy.</span></li>
                    </ul>
                    <p><span lang="es">Para un cambio posterior puedes traer una joya propia. La revisaremos antes para comprobar si el material, el tamaño y la forma son adecuados.</span><span lang="en">For a later change you can bring your own jewellery. We'll check it first to confirm the material, size and shape are suitable.</span></p>
                </div>
            </section>

            <aside class="inline-cta has-tattoo-bg" aria-label="Contacto para piercing">
                <p><span lang="es">¿Ya tienes clara la zona? El nombre raro del piercing lo ponemos nosotros.</span><span lang="en">Already sure about the area? We'll handle the fancy piercing name.</span></p>
                <a class="btn btn-primary" href="/contacto/?tipo=piercing" data-page="contacto" data-tipo="piercing"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
            </aside>

            <section class="tattoo-process has-tattoo-bg">
                <div class="process-container">
                    <h2 class="process-title"><span lang="es">Así funciona hacerse un piercing en Kaos Tattoo</span><span lang="en">How getting a piercing works at Kaos Tattoo</span></h2>
                    <ol class="tattoo-steps">
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">1</span>
                            <h3><span lang="es">Cuéntanos qué zona tienes en mente</span><span lang="en">Tell us what area you have in mind</span></h3>
                            <p><span lang="es">Abre el formulario con Piercing ya seleccionado. Si no conoces el nombre exacto o dudas entre varias opciones, explícanos dónde te gustaría llevarlo.</span><span lang="en">Open the form with Piercing already selected. If you don't know the exact name or are torn between options, just tell us where you'd like it.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">2</span>
                            <h3><span lang="es">Te orientamos antes de venir</span><span lang="en">We guide you before you come</span></h3>
                            <p><span lang="es">Resolvemos las primeras dudas sobre la colocación, la joya y el material. Si vas a viajar a Alicante, podemos organizarlo contigo en inglés antes de que llegues.</span><span lang="en">We answer your first questions about placement, jewellery and material. If you're travelling to Alicante, we can organise it with you in English before you arrive.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">3</span>
                            <h3><span lang="es">Reserva o pásate por el estudio</span><span lang="en">Book or drop by the studio</span></h3>
                            <p><span lang="es">Trabajamos con cita y también atendemos sin ella cuando hay disponibilidad. Si reservas, la visita queda cerrada cuando recibes nuestra confirmación.</span><span lang="en">We work with appointments and also take walk-ins when there's availability. If you book, the visit is confirmed when you receive our confirmation.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">4</span>
                            <h3><span lang="es">Valoramos la anatomía</span><span lang="en">We assess your anatomy</span></h3>
                            <p><span lang="es">Ya en el estudio, La Greka revisa la zona y confirma la colocación. Si hace falta cambiar el planteamiento, te explica las alternativas antes de empezar.</span><span lang="en">Once at the studio, La Greka checks the area and confirms the placement. If the approach needs changing, she explains the alternatives before starting.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">5</span>
                            <h3><span lang="es">Elegimos la pieza y perforamos</span><span lang="en">We choose the piece and pierce</span></h3>
                            <p><span lang="es">Revisamos contigo la posición y la joya inicial, resolvemos las últimas dudas y preparamos el material. Solo entonces toca aguja.</span><span lang="en">We go over the position and the initial jewellery with you, answer the last questions and prepare the material. Only then does the needle come out.</span></p>
                        </li>
                        <li class="tattoo-step">
                            <span class="tattoo-step-number">6</span>
                            <h3><span lang="es">Te llevas cuidados y seguimiento</span><span lang="en">You leave with aftercare and follow-up</span></h3>
                            <p><span lang="es">Al terminar te explicamos los cuidados y te damos un pequeño kit con las instrucciones y una muestra de suero fisiológico. También recomendamos una revisión aproximadamente un mes después.</span><span lang="en">When we're done we explain the aftercare and give you a small kit with instructions and a saline solution sample. We also recommend a check-up roughly a month later.</span></p>
                        </li>
                    </ol>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Precios claros antes de perforar</span><span lang="en">Clear prices before piercing</span></h2>
                    <p><span lang="es">El precio depende de la zona y de la joya elegida. Si hay alguna particularidad, te la explicamos antes de hacer nada.</span><span lang="en">The price depends on the area and the chosen jewellery. If there's anything unusual, we'll explain it before doing anything.</span></p>
                    <ul class="tattoo-check-list">
                        <li><span lang="es">Piercings de oreja y nostril: desde 20&nbsp;€ el primero.</span><span lang="en">Ear and nostril piercings: from €20 for the first.</span></li>
                        <li><span lang="es">Piercings adicionales en la misma visita: desde 15&nbsp;€ cada uno.</span><span lang="en">Additional piercings in the same visit: from €15 each.</span></li>
                        <li><span lang="es">Otras zonas: hasta 30&nbsp;€, según la perforación.</span><span lang="en">Other areas: up to €30, depending on the piercing.</span></li>
                        <li><span lang="es">Snake eyes: 40&nbsp;€.</span><span lang="en">Snake eyes: €40.</span></li>
                        <li><span lang="es">Titanio de grado implante: suplemento de 7&nbsp;€.</span><span lang="en">Implant-grade titanium: €7 supplement.</span></li>
                    </ul>
                    <p><span lang="es">La joya inicial está incluida. Puedes reservar para asegurar una hora o venir sin cita; en ese caso te atenderemos por orden de llegada y según la disponibilidad de La Greka.</span><span lang="en">The initial jewellery is included. You can book to secure a time or come without an appointment; in that case we'll serve you on a first-come basis depending on La Greka's availability.</span></p>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Durante tu piercing</span><span lang="en">During your piercing</span></h2>
                    <p><span lang="es">Antes de empezar comprobamos de nuevo la zona, marcamos la colocación y la revisamos contigo. No hay prisa por llegar a la aguja: primero tienes que entender qué vamos a hacer y verte bien con la posición.</span><span lang="en">Before starting we check the area again, mark the placement and go over it with you. There's no rush to get to the needle: first you need to understand what we're going to do and feel good about the position.</span></p>
                    <p><span lang="es">Preparamos y desinfectamos el puesto para cada persona y utilizamos agujas y material estéril y de un solo uso. La joya inicial también se esteriliza antes de colocarla. Tú solo tienes que respirar; La Greka se encarga del resto.</span><span lang="en">We prepare and disinfect the station for each person and use sterile, single-use needles and materials. The initial jewellery is also sterilised before placement. You just need to breathe; La Greka takes care of the rest.</span></p>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Sales con un piercing. No te dejamos a tu suerte.</span><span lang="en">You leave with a piercing. We don't leave you on your own.</span></h2>
                    <p><span lang="es">Puedes escribirnos o llamarnos durante toda la cicatrización, mandarnos una foto si algo te genera dudas o pasar por el estudio para que lo revisemos. Aproximadamente al mes recomendamos comprobar cómo evoluciona y, cuando llegue el momento, también podemos ayudarte con el cambio de joya o cualquier ajuste.</span><span lang="en">You can message or call us throughout the healing process, send us a photo if something worries you or stop by the studio for us to check it. Around the one-month mark we recommend checking how it's evolving and, when the time comes, we can also help with the jewellery change or any adjustment.</span></p>
                    <p><span lang="es">Si después de la cita vuelves a tu país, el seguimiento puede continuar por WhatsApp o llamada. La distancia no cura el piercing, pero tampoco nos impide echarle un ojo.</span><span lang="en">If you head back to your country after the appointment, follow-up can continue via WhatsApp or phone. Distance doesn't heal the piercing, but it doesn't stop us keeping an eye on it either.</span></p>
                </div>
            </section>

            <aside class="reviews-block has-tattoo-bg" aria-label="Reseñas">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">Más de 200 reseñas nos avalan</span><span lang="en">Over 200 reviews back us up</span></h2>
                    <p><span lang="es">No lo decimos solo nosotros. Más de 200 reseñas en Google respaldan el trabajo de Kaos Tattoo y una forma de atender que busca que quieras volver, aunque sepamos que la primera visita llevaba aguja.</span><span lang="en">We're not the only ones saying it. Over 200 Google reviews back Kaos Tattoo's work and a way of caring that makes you want to come back, even though we know the first visit involved a needle.</span></p>
                    <a class="reviews-link" href="https://www.google.com/searchviewer/10?svid=CAwSHRIbCgNwdnESFENnMHZaeTh4TVhCM01YaGlkREV3GAo" target="_blank" rel="noopener noreferrer"><span lang="es">Leer reseñas en Google</span><span lang="en">Read reviews on Google</span></a>
                </div>
            </aside>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner tattoo-prose">
                    <h2 class="tattoo-section-title"><span lang="es">Cómo cuidar tu piercing</span><span lang="en">How to care for your piercing</span></h2>
                    <ol class="tattoo-care-steps">
                        <li><span lang="es"><strong>Lávate siempre las manos antes de tocar la zona.</strong> Limpia el piercing dos veces al día: por la mañana y por la noche.</span><span lang="en"><strong>Always wash your hands before touching the area.</strong> Clean the piercing twice a day: morning and evening.</span></li>
                        <li><span lang="es"><strong>Lava primero con agua y jabón neutro.</strong> Después utiliza clorhexidina al 1&nbsp;% o suero fisiológico, según la indicación de La Greka. No utilices los dos productos a la vez.</span><span lang="en"><strong>Wash first with water and neutral soap.</strong> Then use 1% chlorhexidine or saline solution, as indicated by La Greka. Don't use both products at the same time.</span></li>
                        <li><span lang="es"><strong>Seca bien la zona con gasas estériles</strong>, con cuidado y sin arrastrar la gasa sobre la perforación.</span><span lang="en"><strong>Dry the area well with sterile gauze</strong>, carefully and without dragging the gauze over the piercing.</span></li>
                        <li><span lang="es"><strong>Durante las dos primeras semanas, no muevas ni toques la joya salvo durante la limpieza</strong> y siempre con las manos limpias.</span><span lang="en"><strong>During the first two weeks, don't move or touch the jewellery except during cleaning</strong> and always with clean hands.</span></li>
                        <li><span lang="es"><strong>Evita la presión y los golpes</strong> sobre la zona perforada.</span><span lang="en"><strong>Avoid pressure and knocks</strong> on the pierced area.</span></li>
                        <li><span lang="es"><strong>No utilices otros productos</strong> distintos del suero fisiológico o la clorhexidina indicada.</span><span lang="en"><strong>Don't use other products</strong> apart from saline solution or the indicated chlorhexidine.</span></li>
                        <li><span lang="es"><strong>Evita playas, piscinas y deporte durante las dos primeras semanas.</strong> Reduce también la humedad, el sudor y el roce sobre la zona.</span><span lang="en"><strong>Avoid beaches, pools and sport during the first two weeks.</strong> Also reduce moisture, sweat and friction on the area.</span></li>
                        <li><span lang="es"><strong>No cambies la pieza sin una revisión profesional previa.</strong> Que parezca curado por fuera no significa que haya terminado de cicatrizar por dentro.</span><span lang="en"><strong>Don't change the piece without a prior professional check.</strong> Looking healed on the outside doesn't mean it's finished healing on the inside.</span></li>
                        <li><span lang="es"><strong>Pregúntanos ante cualquier duda.</strong> Puedes escribirnos, llamarnos o venir al estudio para que revisemos cómo evoluciona.</span><span lang="en"><strong>Ask us if you have any doubts.</strong> You can message us, call us or visit the studio so we can check how it's evolving.</span></li>
                    </ol>
                </div>
            </section>

            <section class="tattoo-section has-tattoo-bg">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">Preguntas frecuentes sobre piercing</span><span lang="en">Frequently asked questions about piercings</span></h2>
                    <div class="faq-accordion">
                        <details class="faq-details">
                            <summary><span lang="es">¿Duele mucho hacerse un piercing?</span><span lang="en">Does getting a piercing hurt a lot?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">La sensación es rápida y puntual. Depende de la zona y de la sensibilidad de cada persona, pero normalmente dura solo unos segundos. La Greka te explica cada paso antes de empezar para que no haya sustos de última hora.</span><span lang="en">The sensation is quick and brief. It depends on the area and each person's sensitivity, but it usually only lasts a few seconds. La Greka explains every step before starting so there are no last-minute surprises.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Qué material utilizáis para la joya inicial?</span><span lang="en">What material do you use for the initial jewellery?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Puedes elegir entre acero quirúrgico esterilizado y titanio de grado implante. La pieza está incluida y la opción de titanio tiene un suplemento de 7&nbsp;€.</span><span lang="en">You can choose between sterilised surgical steel and implant-grade titanium. The piece is included and the titanium option has a supplement of €7.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puedo traer mi propia joya?</span><span lang="en">Can I bring my own jewellery?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">No para una primera puesta. Cuando el piercing haya cicatrizado, podemos revisar una pieza que traigas y decirte si resulta adecuada para el cambio.</span><span lang="en">Not for an initial piercing. Once the piercing has healed, we can check a piece you bring and tell you if it's suitable for the change.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Qué pasa si mi anatomía no permite el piercing que quiero?</span><span lang="en">What if my anatomy doesn't allow the piercing I want?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Te explicaremos el motivo y buscaremos otra colocación. Si no hay una alternativa que consideremos adecuada, preferimos no hacer la perforación.</span><span lang="en">We'll explain the reason and look for another placement. If there's no alternative we consider suitable, we'd rather not do the piercing.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuánto tarda en curarse un piercing?</span><span lang="en">How long does a piercing take to heal?</span></summary>
                            <div class="faq-details-body">
                                <p><span lang="es">Que parezca curado por fuera no significa que lo esté por dentro. Estos tiempos son orientativos y pueden cambiar según la zona y cada persona.</span><span lang="en">Looking healed on the outside doesn't mean it is on the inside. These times are approximate and can vary depending on the area and each person.</span></p>
                                <div class="piercing-heal-table-wrap">
                                    <table class="piercing-heal-table">
                                        <thead>
                                            <tr>
                                                <th><span lang="es">Zona</span><span lang="en">Area</span></th>
                                                <th><span lang="es">Curación inicial</span><span lang="en">Initial healing</span></th>
                                                <th><span lang="es">Cicatrización completa</span><span lang="en">Full scarring</span></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td><span lang="es">Lóbulo</span><span lang="en">Lobe</span></td><td>6–8 <span lang="es">semanas</span><span lang="en">weeks</span></td><td>3 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td><span lang="es">Helix o tragus</span><span lang="en">Helix or tragus</span></td><td>3–4 <span lang="es">meses</span><span lang="en">months</span></td><td>6–9 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td><span lang="es">Conch, rook o daith</span><span lang="en">Conch, rook or daith</span></td><td>4–6 <span lang="es">meses</span><span lang="en">months</span></td><td>9–12 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td>Industrial</td><td>6–8 <span lang="es">meses</span><span lang="en">months</span></td><td>12–18 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td><span lang="es">Nariz</span><span lang="en">Nose</span></td><td>2–3 <span lang="es">meses</span><span lang="en">months</span></td><td>4–6 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td>Septum</td><td>6–8 <span lang="es">semanas</span><span lang="en">weeks</span></td><td>3–4 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td><span lang="es">Labio o medusa</span><span lang="en">Lip or medusa</span></td><td>6–8 <span lang="es">semanas</span><span lang="en">weeks</span></td><td>3–4 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td><span lang="es">Lengua</span><span lang="en">Tongue</span></td><td>2–4 <span lang="es">semanas</span><span lang="en">weeks</span></td><td>6–8 <span lang="es">semanas</span><span lang="en">weeks</span></td></tr>
                                            <tr><td><span lang="es">Pezón</span><span lang="en">Nipple</span></td><td>4–6 <span lang="es">meses</span><span lang="en">months</span></td><td>9–12 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                            <tr><td><span lang="es">Ombligo</span><span lang="en">Navel</span></td><td>6–9 <span lang="es">meses</span><span lang="en">months</span></td><td>12 <span lang="es">meses</span><span lang="en">months</span></td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Qué debo tener en cuenta antes de hacérmelo?</span><span lang="en">What should I keep in mind before getting it?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Ven descansado, aseado, bien hidratado y habiendo comido. Evita el alcohol y las drogas antes de la perforación y cuéntanos cualquier condición médica relevante.</span><span lang="en">Come well-rested, clean, well-hydrated and having eaten. Avoid alcohol and drugs before the piercing and tell us about any relevant medical condition.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Qué síntomas son normales al principio?</span><span lang="en">What symptoms are normal at the start?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Puede haber algo de enrojecimiento, inflamación, picor, sensibilidad, calor o secreción transparente durante los primeros días. Si algo te genera dudas, escríbenos o ven al estudio para que podamos verlo.</span><span lang="en">There may be some redness, swelling, itching, sensitivity, warmth or clear discharge during the first few days. If anything worries you, message us or visit the studio so we can take a look.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuándo debería pedir ayuda?</span><span lang="en">When should I seek help?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Un dolor intenso, pus espeso o amarillento, mal olor, fiebre, una inflamación exagerada o una joya que se está incrustando necesitan atención. No retires la pieza por tu cuenta: contacta con nosotros y busca atención sanitaria si los síntomas son importantes o empeoran.</span><span lang="en">Intense pain, thick or yellowish pus, bad smell, fever, excessive swelling or jewellery embedding need attention. Don't remove the piece yourself: contact us and seek medical attention if the symptoms are serious or worsen.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puedo bañarme en playas o piscinas?</span><span lang="en">Can I swim at the beach or pool?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Evítalo durante las dos primeras semanas y hasta que La Greka confirme que la evolución permite hacerlo.</span><span lang="en">Avoid it during the first two weeks and until La Greka confirms the progress allows it.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puedo hacer deporte después?</span><span lang="en">Can I exercise afterwards?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Evita el deporte durante las dos primeras semanas, sobre todo si implica sudor, golpes, presión o roce sobre la zona.</span><span lang="en">Avoid sport during the first two weeks, especially if it involves sweat, knocks, pressure or friction on the area.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Cuándo puedo cambiar la joya?</span><span lang="en">When can I change the jewellery?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">No la cambies solo porque el piercing parezca curado por fuera. Pide una revisión para comprobar la cicatrización y elegir una pieza adecuada.</span><span lang="en">Don't change it just because the piercing looks healed on the outside. Ask for a check-up to verify healing and choose a suitable piece.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Necesito cita previa?</span><span lang="en">Do I need an appointment?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">No es obligatorio. Trabajamos con cita y también atendemos sin ella cuando hay disponibilidad. Si reservas, espera nuestra confirmación; si vienes directamente, te atenderemos por orden de llegada.</span><span lang="en">It's not mandatory. We work with appointments and also take walk-ins when there's availability. If you book, wait for our confirmation; if you come directly, we'll serve you on a first-come basis.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Hacéis revisiones después?</span><span lang="en">Do you do follow-up check-ups?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">Sí. Recomendamos una revisión aproximadamente un mes después y también podemos ayudarte con cambios de joyería o ajustes. Escríbenos, llámanos o pásate por el estudio.</span><span lang="en">Yes. We recommend a check-up roughly a month later and can also help with jewellery changes or adjustments. Message us, call us or stop by the studio.</span></p></div>
                        </details>
                        <details class="faq-details">
                            <summary><span lang="es">¿Puede hacerse un piercing una persona menor de edad?</span><span lang="en">Can a minor get a piercing?</span></summary>
                            <div class="faq-details-body"><p><span lang="es">No realizamos piercings a bebés ni a menores de 14 años. Entre los 14 y los 17, la persona debe acudir con su padre, madre o tutor legal y presentar la documentación de ambos. La Greka valorará también la zona y si resulta apropiado hacerlo.</span><span lang="en">We don't do piercings on babies or under-14s. Between 14 and 17, the person must come with a parent or legal guardian and present both parties' ID. La Greka will also assess the area and whether it's appropriate to do it.</span></p></div>
                        </details>
                    </div>
                </div>
            </section>

            <section class="final-cta has-tattoo-bg">
                <div class="tattoo-section-inner">
                    <h2 class="tattoo-section-title"><span lang="es">¿Aguja? Sí. Decisiones a ciegas, no.</span><span lang="en">Needle? Yes. Blind decisions? No.</span></h2>
                    <p><span lang="es">Cuéntanos la zona que tienes en mente y La Greka te ayudará a elegir una colocación y una joya que tengan sentido para ti.</span><span lang="en">Tell us the area you have in mind and La Greka will help you choose a placement and a piece of jewellery that make sense for you.</span></p>
                    <a class="btn btn-primary" href="/contacto/?tipo=piercing" data-page="contacto" data-tipo="piercing"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></a>
                </div>
            </section>
        </div>

        <div id="dibujos-cuadros" class="page<?php echo pageActive('dibujos-cuadros', $pageId); ?>">
            <section class="tattoo-hero dibujos-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/DIBUJOSYCUADROS2.webp" data-lazy-video>
                    <source data-src="videos/headers/DIBUJOSYCUADROS2.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="dibujos-cuadros-hero-content hero-content">
                </div>
            </section>

            <section class="page-heading">
                <h1><span lang="es">Dibujos y cuadros personalizados en Alicante</span><span lang="en">Custom drawings and paintings in Alicante</span></h1>
            </section>

            <section class="dibujos-intro">
                <div class="dibujos-intro-overlay"></div>
                <div class="dibujos-intro-content">
                    <p class="dibujos-intro-tagline"><span lang="es">"No todo acaba en la piel. Aquí el arte se cuelga, se regala o se encarga. Piezas únicas, hechas a mano"</span><span lang="en">"Not everything ends on skin. Here art is hung, gifted or commissioned. Unique pieces, handmade"</span></p>
                    <p><span lang="es">En esta sección encontrarás cuadros y lienzos hechos completamente a mano por Kike, piezas únicas que pueden verse expuestas en el estudio o adquirirse directamente. También realizamos encargos personalizados, desde dibujos originales hasta retratos detallados, con una especial dedicación a los retratos de mascotas.</span><span lang="en">In this section you'll find paintings and canvases entirely handmade by Kike — unique pieces that can be seen on display at the studio or purchased directly. We also take custom commissions, from original drawings to detailed portraits, with a special dedication to pet portraits.</span></p>
                </div>
            </section>

            <section class="dibujos-secondary">
                <div class="dibujos-secondary-content">
                    <p><span lang="es">Cada obra se crea de forma artesanal, utilizando distintas técnicas artísticas como lápiz, acrílico, óleo o pastel, según el estilo y la sensibilidad que mejor encaje con cada proyecto. Ya sea para regalar, decorar un espacio o conservar un recuerdo especial, aquí el arte no se imprime: se pinta, se dibuja y se cuida.</span><span lang="en">Each piece is handcrafted using various artistic techniques such as pencil, acrylic, oil or pastel, depending on the style and sensibility that best suits each project. Whether as a gift, to decorate a space or to preserve a special memory — here art isn't printed: it's painted, drawn and cared for.</span></p>
                </div>
            </section>

            <section class="dibujos-gallery">
                <div class="dibujos-gallery-grid">
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_0587.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_0587.webp" alt="Obra de arte 1">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_0869 21.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_0869 21.webp" alt="Obra de arte 2">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_0870 21.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_0870 21.webp" alt="Obra de arte 3">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_3509 21.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_3509 21.webp" alt="Obra de arte 4">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_35511.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_35511.webp" alt="Obra de arte 5">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8121 31.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8121 31.webp" alt="Obra de arte 6">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8545.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8545.webp" alt="Obra de arte 7">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8601 2.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8601 2.webp" alt="Obra de arte 8">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8646 5.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8646 5.webp" alt="Obra de arte 9">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8656 5.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8656 5.webp" alt="Obra de arte 10">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8658.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8658.webp" alt="Obra de arte 11">
                    </figure>
                    <figure class="dibujos-gallery-item" data-image="images/STYLES/Dibujos y cuadros/IMG_8661.webp">
                        <img loading="lazy" src="images/STYLES/Dibujos y cuadros/IMG_8661.webp" alt="Obra de arte 12">
                    </figure>
                </div>
            </section>

        </div>

        <div id="contacto" class="page<?php echo pageActive('contacto', $pageId); ?>">
            <section class="tattoo-hero contacto-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/gracias_video.webp" data-lazy-video>
                    <source data-src="videos/gracias_video.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="contacto-hero-content hero-content">
                </div>
            </section>

            <section class="contacto-form-section">
                <div class="contacto-form-container">
                    <h1 class="contacto-form-title"><span lang="es">Cuéntanos tu idea</span><span lang="en">Tell us your idea</span></h1>
                    <p class="contacto-form-subtitle"><span lang="es">Te responderemos lo antes posible</span><span lang="en">We'll get back to you as soon as possible</span></p>

                    <div class="form-progress">
                        <div class="form-progress-step active" data-step="1"></div>
                        <div class="form-progress-step" data-step="2"></div>
                        <div class="form-progress-step" data-step="3"></div>
                    </div>

                    <form id="contacto-form" novalidate>
                        <input type="hidden" name="_captcha" value="false">
                        <input type="hidden" name="_template" value="table">
                        <input type="hidden" name="_subject" value="Nueva consulta desde la web">
                        <!-- Honeypot anti-spam: hidden from humans, bots fill it -->
                        <div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden">
                            <label for="_hp_website">Website</label>
                            <input type="text" id="_hp_website" name="website" tabindex="-1" autocomplete="off">
                        </div>
                        <input type="hidden" name="_form_loaded_at" value="">
                        <!-- Step 1: Type -->
                        <div class="form-step active" data-form-step="1">
                            <div class="form-type-grid">
                                <button type="button" class="form-type-btn" data-type="tatuaje">
                                    <span class="form-type-icon">🖋️</span>
                                    <span lang="es">Tatuaje</span><span lang="en">Tattoo</span>
                                </button>
                                <button type="button" class="form-type-btn" data-type="piercing">
                                    <span class="form-type-icon">💎</span>
                                    Piercing
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Details -->
                        <div class="form-step" data-form-step="2">
                            <!-- Tattoo fields -->
                            <div id="fields-tatuaje" style="display:none">
                                <div class="form-field">
                                    <label class="form-label" for="zona-tatuaje"><span lang="es">Zona del cuerpo</span><span lang="en">Body area</span></label>
                                    <select class="form-select" id="zona-tatuaje" name="zona" data-i18n-select>
                                        <option value="" disabled selected data-en="Select an area">Selecciona una zona</option>
                                        <option value="Brazo" data-en="Arm">Brazo</option>
                                        <option value="Antebrazo" data-en="Forearm">Antebrazo</option>
                                        <option value="Muñeca" data-en="Wrist">Muñeca</option>
                                        <option value="Mano/Dedos" data-en="Hand / Fingers">Mano / Dedos</option>
                                        <option value="Hombro" data-en="Shoulder">Hombro</option>
                                        <option value="Pecho" data-en="Chest">Pecho</option>
                                        <option value="Espalda" data-en="Back">Espalda</option>
                                        <option value="Costillas" data-en="Ribs">Costillas</option>
                                        <option value="Cuello" data-en="Neck">Cuello</option>
                                        <option value="Pierna/Muslo" data-en="Leg / Thigh">Pierna / Muslo</option>
                                        <option value="Gemelo" data-en="Calf">Gemelo</option>
                                        <option value="Tobillo/Pie" data-en="Ankle / Foot">Tobillo / Pie</option>
                                        <option value="Otra" data-en="Other">Otra</option>
                                        <option value="No lo tengo claro" data-en="Not sure yet">No lo tengo claro</option>
                                    </select>
                                    <div class="form-field-other" id="otra-zona-tatuaje">
                                        <input type="text" class="form-input" placeholder="Indica la zona" data-i18n-placeholder="Specify the area" name="zona_otra">
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="idea-tatuaje"><span lang="es">Idea y estilo</span><span lang="en">Idea and style</span></label>
                                    <textarea class="form-textarea" id="idea-tatuaje" name="idea" placeholder="Describe tu idea: diseño, estilo, tamaño, referencias..." data-i18n-placeholder="Describe your idea: design, style, size, references..." rows="4"></textarea>
                                </div>
                            </div>

                            <!-- Piercing fields -->
                            <div id="fields-piercing" style="display:none">
                                <div class="form-field">
                                    <label class="form-label" for="zona-piercing"><span lang="es">Zona del cuerpo</span><span lang="en">Body area</span></label>
                                    <select class="form-select" id="zona-piercing" name="zona_piercing" data-i18n-select>
                                        <option value="" disabled selected data-en="Select an area">Selecciona una zona</option>
                                        <option value="Lóbulo" data-en="Lobe">Lóbulo</option>
                                        <option value="Hélix" data-en="Helix">Hélix</option>
                                        <option value="Tragus">Tragus</option>
                                        <option value="Conch">Conch</option>
                                        <option value="Daith">Daith</option>
                                        <option value="Industrial">Industrial</option>
                                        <option value="Septum">Septum</option>
                                        <option value="Nostril (nariz)" data-en="Nostril (nose)">Nostril (nariz)</option>
                                        <option value="Labio" data-en="Lip">Labio</option>
                                        <option value="Ceja" data-en="Eyebrow">Ceja</option>
                                        <option value="Lengua" data-en="Tongue">Lengua</option>
                                        <option value="Ombligo" data-en="Navel">Ombligo</option>
                                        <option value="Pezón" data-en="Nipple">Pezón</option>
                                        <option value="Otra" data-en="Other">Otra</option>
                                        <option value="No lo tengo claro" data-en="Not sure yet">No lo tengo claro</option>
                                    </select>
                                    <div class="form-field-other" id="otra-zona-piercing">
                                        <input type="text" class="form-input" placeholder="Indica la zona" data-i18n-placeholder="Specify the area" name="zona_piercing_otra">
                                    </div>
                                </div>
                            </div>

                            <!-- Image upload (shared for tattoo & piercing) -->
                            <div class="form-field form-upload-field" id="form-upload-field" style="display:none">
                                <label class="form-label"><span lang="es">Imágenes de referencia <span class="form-label-optional">(opcional)</span></span><span lang="en">Reference images <span class="form-label-optional">(optional)</span></span></label>
                                <p class="form-upload-hint"><span lang="es">Capturas de pantalla, ideas, bocetos... todo lo que nos ayude a entender tu idea.</span><span lang="en">Screenshots, ideas, sketches... anything that helps us understand your idea.</span></p>
                                <div class="form-dropzone" id="form-dropzone">
                                    <input type="file" id="form-file-input" multiple accept="image/*" class="form-file-hidden">
                                    <div class="form-dropzone-content">
                                        <svg class="form-dropzone-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        <span class="form-dropzone-text form-dropzone-text--desktop"><span lang="es">Arrastra imágenes aquí o <strong>haz clic para seleccionar</strong></span><span lang="en">Drag images here or <strong>click to select</strong></span></span>
                                        <span class="form-dropzone-text form-dropzone-text--mobile"><span lang="es"><strong>Toca para seleccionar imágenes</strong></span><span lang="en"><strong>Tap to select images</strong></span></span>
                                        <span class="form-dropzone-formats"><span lang="es">JPG, PNG, WebP — máx. 5 MB por imagen, hasta 4 imágenes</span><span lang="en">JPG, PNG, WebP — max 5 MB per image, up to 4 images</span></span>
                                    </div>
                                </div>
                                <div class="form-previews" id="form-previews"></div>
                            </div>

                            <div class="form-nav">
                                <button type="button" class="form-btn-back" data-back><span lang="es">Atrás</span><span lang="en">Back</span></button>
                                <button type="button" class="form-btn-next" data-next disabled><span lang="es">Siguiente</span><span lang="en">Next</span></button>
                            </div>
                        </div>

                        <!-- Step 3: Contact info -->
                        <div class="form-step" data-form-step="3">
                            <div class="form-row">
                                <div class="form-field">
                                    <label class="form-label" for="form-nombre"><span lang="es">Nombre</span><span lang="en">Name</span></label>
                                    <input type="text" class="form-input" id="form-nombre" name="nombre" required placeholder="Tu nombre" data-i18n-placeholder="Your name" autocomplete="name">
                                </div>
                                <div class="form-field">
                                    <label class="form-label" for="form-email"><span lang="es">Correo electrónico</span><span lang="en">Email</span></label>
                                    <input type="email" class="form-input" id="form-email" name="email" required placeholder="tu@email.com" data-i18n-placeholder="you@email.com" autocomplete="email">
                                    <span class="form-error" id="error-email"><span lang="es">Introduce un email válido (ej: tu@email.com)</span><span lang="en">Enter a valid email (e.g. you@email.com)</span></span>
                                </div>
                            </div>
                            <div class="form-field">
                                <label class="form-label" for="form-telefono"><span lang="es">Teléfono</span><span lang="en">Phone</span></label>
                                <input type="tel" class="form-input" id="form-telefono" name="telefono" required placeholder="600 000 000" autocomplete="tel">
                                <span class="form-error" id="error-telefono"><span lang="es">Introduce un teléfono válido (mín. 9 dígitos)</span><span lang="en">Enter a valid phone number (min. 9 digits)</span></span>
                            </div>

                            <label class="form-checkbox">
                                <input type="checkbox" id="form-privacy" name="privacy" required>
                                <span class="form-checkbox-label"><span lang="es">Acepto la <a href="#" target="_blank">política de privacidad</a> y el tratamiento de mis datos para gestionar esta consulta.</span><span lang="en">I accept the <a href="#" target="_blank">privacy policy</a> and the processing of my data to manage this enquiry.</span></span>
                            </label>

                            <div class="form-nav">
                                <button type="button" class="form-btn-back" data-back><span lang="es">Atrás</span><span lang="en">Back</span></button>
                                <button type="submit" class="form-btn-submit" disabled><span lang="es">Enviar mi consulta</span><span lang="en">Send my enquiry</span></button>
                            </div>
                        </div>
                    </form>

                    <div class="form-success" id="form-success">
                        <div class="form-success-icon">✅</div>
                        <h3><span lang="es">¡Consulta enviada!</span><span lang="en">Enquiry sent!</span></h3>
                        <p><span lang="es">Te responderemos lo antes posible a tu correo electrónico.</span><span lang="en">We'll reply to your email as soon as possible.</span></p>
                    </div>
                </div>
            </section>

            <section class="contacto-social">
                <h2 class="contacto-title"><span lang="es">Contacta con Kaos Tattoo en Alicante</span><span lang="en">Contact Kaos Tattoo in Alicante</span></h2>
                <div class="contacto-social-grid">
                    <a href="https://wa.me/34618710976" target="_blank" class="social-icon-link" aria-label="WhatsApp">
                        <div class="social-icon-wrapper">
                            <img src="images/logotipos_contacts/whatsapp.webp" alt="WhatsApp">
                        </div>
                        <span class="social-icon-name">WhatsApp</span>
                        <div class="social-tooltip"><span lang="es">¡Preguntas rápidas o seguimiento de una conversación ya iniciada!</span><span lang="en">Quick questions or follow-up on an existing conversation!</span></div>
                    </a>
                    <a href="tel:+34618710976" class="social-icon-link" aria-label="Teléfono">
                        <div class="social-icon-wrapper">
                            <img src="images/logotipos_contacts/phone.svg" alt="Teléfono">
                        </div>
                        <span class="social-icon-name"><span lang="es">Teléfono</span><span lang="en">Phone</span></span>
                        <div class="social-tooltip"><span lang="es">¡Consultas que necesiten respuesta dentro del horario del estudio!</span><span lang="en">Enquiries that need a response during studio hours!</span></div>
                    </a>
                    <a href="https://instagram.com/kaos.tattoostudio" target="_blank" class="social-icon-link" aria-label="Instagram">
                        <div class="social-icon-wrapper">
                            <img src="images/logotipos_contacts/instagram.webp" alt="Instagram">
                        </div>
                        <span class="social-icon-name">Instagram</span>
                        <div class="social-tooltip"><span lang="es">¡Mira nuestros trabajos y novedades! Para consultas, usa otro canal.</span><span lang="en">Check out our work and news! For enquiries, use another channel.</span></div>
                    </a>
                    <a href="mailto:kaostattooalc@gmail.com" target="_blank" class="social-icon-link" aria-label="Email">
                        <div class="social-icon-wrapper">
                            <img src="images/logotipos_contacts/mail.webp" alt="Email">
                        </div>
                        <span class="social-icon-name">Mail</span>
                        <div class="social-tooltip"><span lang="es">¡Documentación, colaboraciones o consultas sin urgencia!</span><span lang="en">Documentation, collaborations or non-urgent enquiries!</span></div>
                    </a>
                    <a href="https://facebook.com/KaosTattoo" target="_blank" class="social-icon-link" aria-label="Facebook">
                        <div class="social-icon-wrapper">
                            <img src="images/logotipos_contacts/facebook.webp" alt="Facebook">
                        </div>
                        <span class="social-icon-name">Facebook</span>
                        <div class="social-tooltip"><span lang="es">¡Mira nuestros trabajos y novedades! Para consultas, usa otro canal.</span><span lang="en">Check out our work and news! For enquiries, use another channel.</span></div>
                    </a>
                    <a href="https://www.tiktok.com/@kaos.tattoo.alicante" target="_blank" class="social-icon-link" aria-label="TikTok">
                        <div class="social-icon-wrapper">
                            <img src="images/logotipos_contacts/tik-tok.webp" alt="TikTok">
                        </div>
                        <span class="social-icon-name">TikTok</span>
                        <div class="social-tooltip"><span lang="es">¡Mira nuestros trabajos y novedades! Para consultas, usa otro canal.</span><span lang="en">Check out our work and news! For enquiries, use another channel.</span></div>
                    </a>
                </div>
            </section>
        </div>

        <div id="blog" class="page<?php echo pageActive('blog', $pageId); ?>">
            <section class="fineline-hero">
                <video class="hero-video" muted loop playsinline preload="none" poster="images/posters/headers/BLOG.webp" data-lazy-video>
                    <source data-src="videos/headers/BLOG.mp4" type="video/mp4">
                </video>
                <div class="hero-overlay"></div>
                <div class="hero-content">
                </div>
            </section>
            <section class="page-heading">
                <h1><span lang="es">Blog de Kaos Tattoo Alicante</span><span lang="en">Kaos Tattoo Alicante Blog</span></h1>
            </section>
            <div class="page-content">
                <p class="blog-subtitle"><span lang="es">Últimas publicaciones de Kaos Tattoo.</span><span lang="en">Latest posts from Kaos Tattoo.</span></p>
                <div class="blog-feed" data-scroll-reveal>
                    <p class="blog-status" aria-live="polite"><span lang="es">Cargando publicaciones...</span><span lang="en">Loading posts...</span></p>
                    <div class="blog-grid" aria-live="polite"></div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <div class="footer-content">
                <div class="footer-powered">
                    <span class="powered-label"><span lang="es">Desarrollado por:</span><span lang="en">Powered by:</span></span>
                    <a href="https://www.instagram.com/laemestudios?utm_source=qr&igsh=YWg5d2s5azRxNW5j" target="_blank" rel="noopener noreferrer" class="powered-brand">
                        <img src="images/logo_laM_nobg.webp" alt="La M Studios logo" class="powered-logo">
                        <span class="powered-name">La M Studios</span>
                    </a>
                </div>
                <p class="footer-copy">
                    &copy; <span id="footer-year">2026</span> Kaos Tattoo.<br>
                    <span class="footer-copy-secondary"><span lang="es">Todos los derechos reservados.</span><span lang="en">All rights reserved.</span></span>
                    <span class="footer-address">C/ Pintor Velazquez, 17, 03004 Alacant, Alicante, Spain &middot; <a href="tel:+34618710976">+34 618 710 976</a></span>
                </p>
                <div class="footer-social">
                    <a href="https://wa.me/34618710976" target="_blank" class="footer-social-link">
                        <img src="images/logotipos_contacts/whatsapp_bw.webp" alt="WhatsApp" class="footer-social-icon">
                    </a>
                    <a href="https://instagram.com/kaos.tattoostudio" target="_blank" class="footer-social-link">
                        <img src="images/logotipos_contacts/instagram_bw.webp" alt="Instagram" class="footer-social-icon">
                    </a>
                    <a href="mailto:kaostattooalc@gmail.com" target="_blank" class="footer-social-link">
                        <img src="images/logotipos_contacts/mail_bw.webp" alt="Email" class="footer-social-icon">
                    </a>
                    <a href="https://facebook.com/KaosTattoo" target="_blank" class="footer-social-link">
                        <img src="images/logotipos_contacts/facebook_bw.webp" alt="Facebook" class="footer-social-icon">
                    </a>
                    <a href="https://www.tiktok.com/@kaos.tattoo.alicante" target="_blank" class="footer-social-link">
                        <img src="images/logotipos_contacts/tik-tok_bw.webp" alt="TikTok" class="footer-social-icon">
                    </a>
                    <a href="/contacto/" class="footer-social-link footer-contact-link" data-page="contacto">
                        <span lang="es">Contacto</span><span lang="en">Contact</span>
                    </a>
                </div>
            </div>
        </footer>
    </main>

    <!-- Floating WhatsApp Button + Chat Widget -->
    <style>
    .whatsapp-container{position:fixed;bottom:35px;right:35px;z-index:9999}
    .whatsapp-float{width:75px;height:75px;background:#25D366;border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 0 15px rgba(255,255,255,.5),0 0 30px rgba(255,255,255,.3),0 0 45px rgba(255,255,255,.2),0 4px 15px rgba(37,211,102,.4);transition:transform .3s ease,box-shadow .3s ease;opacity:0;visibility:hidden}
    .whatsapp-float.is-visible{opacity:1;visibility:visible;animation:whatsappFadeIn .6s ease forwards}
    @keyframes whatsappFadeIn{from{opacity:0;transform:scale(.5) translateY(20px)}to{opacity:1;transform:scale(1) translateY(0)}}
    .whatsapp-float:hover{transform:scale(1.1);box-shadow:0 0 20px rgba(255,255,255,.7),0 0 40px rgba(255,255,255,.5),0 0 60px rgba(255,255,255,.3),0 6px 20px rgba(37,211,102,.6)}
    .whatsapp-float img{width:45px;height:45px;object-fit:contain}
    .whatsapp-chat{position:absolute;bottom:90px;right:0;width:360px;background:#fff;border-radius:16px;box-shadow:0 8px 32px rgba(0,0,0,.3);overflow:hidden;opacity:0;visibility:hidden;transform:translateY(20px) scale(.95);transition:opacity .3s ease,transform .3s ease,visibility .3s ease}
    .whatsapp-chat.is-open{opacity:1;visibility:visible;transform:translateY(0) scale(1)}
    .whatsapp-chat-header{background:#075E54;color:#fff;padding:16px;display:flex;align-items:center;gap:12px}
    .whatsapp-chat-avatar{width:40px;height:40px;border-radius:50%;object-fit:contain;background:#25D366;padding:6px}
    .whatsapp-chat-header-info{flex:1;display:flex;flex-direction:column}
    .whatsapp-chat-name{font-weight:700;font-size:15px}
    .whatsapp-chat-status{font-size:12px;opacity:.8}
    .whatsapp-chat-close{background:none;border:none;color:#fff;font-size:28px;cursor:pointer;line-height:1;padding:0 4px;opacity:.8;transition:opacity .2s ease}
    .whatsapp-chat-close:hover{opacity:1}
    .whatsapp-chat-body{padding:20px 16px;background:#E5DDD5}
    .whatsapp-chat-bubble{background:#fff;padding:12px 16px;border-radius:0 12px 12px 12px;font-size:14px;line-height:1.5;color:#333;box-shadow:0 1px 2px rgba(0,0,0,.1);max-width:90%}
    .whatsapp-chat-footer{display:flex;align-items:flex-end;gap:8px;padding:12px;background:#f0f0f0}
    .whatsapp-chat-input{flex:1;border:2px solid transparent;border-radius:20px;padding:10px 16px;font-size:14px;font-family:inherit;resize:none;outline:none;background:#fff;max-height:100px;line-height:1.4}
    .whatsapp-chat-input.input-error{border-color:#e74c3c;animation:waShake .3s ease}
    @keyframes waShake{0%,100%{transform:translateX(0)}25%{transform:translateX(-4px)}75%{transform:translateX(4px)}}
    .whatsapp-chat-send{width:44px;height:44px;background:#25D366;border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;flex-shrink:0;transition:background .2s ease}
    .whatsapp-chat-send:hover{background:#128C7E}
    @media(max-width:768px){.whatsapp-container{bottom:25px;right:25px}.whatsapp-float{width:65px;height:65px}.whatsapp-float img{width:38px;height:38px}.whatsapp-chat{width:calc(100vw - 50px);right:0;bottom:80px}}
    </style>
    <div id="whatsapp-container" class="whatsapp-container">
        <div id="whatsapp-chat" class="whatsapp-chat" style="display:none">
            <div class="whatsapp-chat-header">
                <img src="images/logotipos_contacts/whatsapp.webp" alt="WhatsApp" class="whatsapp-chat-avatar">
                <div class="whatsapp-chat-header-info">
                    <span class="whatsapp-chat-name">Kaos Tattoo Alicante</span>
                    <span class="whatsapp-chat-status"><span lang="es">Normalmente responde en menos de 1 hora</span><span lang="en">Usually replies within 1 hour</span></span>
                </div>
                <button id="whatsapp-chat-close" class="whatsapp-chat-close" aria-label="Cerrar chat">&times;</button>
            </div>
            <div class="whatsapp-chat-body">
                <div class="whatsapp-chat-bubble">
                    <span lang="es">¡Hola! 👋 ¿En qué podemos ayudarte? Escríbenos tu mensaje y te responderemos por WhatsApp.</span><span lang="en">Hi! 👋 How can we help? Write your message and we'll reply via WhatsApp.</span>
                </div>
            </div>
            <div class="whatsapp-chat-footer">
                <textarea id="whatsapp-message" class="whatsapp-chat-input" placeholder="Escribe tu mensaje..." data-i18n-placeholder="Write your message..." rows="2"></textarea>
                <button id="whatsapp-send" class="whatsapp-chat-send" aria-label="Enviar mensaje">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                </button>
            </div>
        </div>
        <button id="whatsapp-float-btn" class="whatsapp-float" style="display:none" aria-label="Contactar por WhatsApp">
            <img src="images/logotipos_contacts/whatsapp.webp" alt="WhatsApp">
        </button>
    </div>

    <script>
    function getLang() {
        return (document.documentElement.lang || 'es').substring(0, 2);
    }
    </script>

    <script>
    (function() {
        var waBtn = document.getElementById('whatsapp-float-btn');
        var waChat = document.getElementById('whatsapp-chat');
        var waClose = document.getElementById('whatsapp-chat-close');
        var waInput = document.getElementById('whatsapp-message');
        var waSend = document.getElementById('whatsapp-send');
        var waPhone = '34618710976';
        var DELAY_MS = 20000;
        var elapsed = 0;
        var timerInterval = null;
        var btnShown = false;
        var widgetEventFired = false;
        var deeplinkEventFired = false;

        function isContactPage() {
            var pathname = window.location.pathname.replace(/\/+$/, '');
            if (pathname === '/contacto') return true;
            // Fallback for old hash URLs during migration
            var hash = window.location.hash.replace('#', '');
            return hash === 'contacto';
        }

        // The floating button is hidden on the contacto page (WhatsApp is
        // already listed among the contact options there) and shown elsewhere
        // once "earned" (after the reveal timer).
        function refreshButton() {
            var visible = btnShown && !isContactPage();
            if (visible) {
                waBtn.style.display = 'flex';
                waBtn.classList.add('is-visible');
            } else {
                waBtn.classList.remove('is-visible');
                waBtn.style.display = 'none';
                // Close the chat if it was open
                if (waChat.style.display === 'block') {
                    waChat.classList.remove('is-open');
                    waChat.style.display = 'none';
                }
            }
        }

        function earnButton() {
            btnShown = true;
            if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
            refreshButton();
        }

        function startTimer() {
            if (btnShown || timerInterval || isContactPage()) return;
            timerInterval = setInterval(function() {
                elapsed += 250;
                if (elapsed >= DELAY_MS) earnButton();
            }, 250);
        }

        function pauseTimer() {
            if (timerInterval) { clearInterval(timerInterval); timerInterval = null; }
        }

        // Start the reveal timer only when NOT on the contacto page
        startTimer();

        // Pause/resume timer when tab visibility changes
        document.addEventListener('visibilitychange', function() {
            if (btnShown) return;
            if (document.hidden) {
                pauseTimer();
            } else {
                startTimer();
            }
        });

        // On SPA navigation: hide on contacto, (re)show/earn elsewhere
        function onNav() {
            refreshButton();
            startTimer();
        }
        window.addEventListener('pagechange', onNav);
        window.addEventListener('popstate', onNav);

        // Toggle chat widget
        waBtn.addEventListener('click', function() {
            if (waChat.style.display === 'block') {
                waChat.classList.remove('is-open');
                setTimeout(function() { waChat.style.display = 'none'; }, 300);
            } else {
                waChat.style.display = 'block';
                requestAnimationFrame(function() { waChat.classList.add('is-open'); });
                waInput.focus();
                // GTM: widget opened
                if (!widgetEventFired) {
                    widgetEventFired = true;
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        event: 'whatsapp_widget',
                        button_location: 'floating',
                        page_language: getLang()
                    });
                }
            }
        });

        waClose.addEventListener('click', function() {
            waChat.classList.remove('is-open');
            setTimeout(function() { waChat.style.display = 'none'; }, 300);
        });

        waSend.addEventListener('click', function() {
            var message = waInput.value.trim();
            // Require a written message before opening WhatsApp or registering
            if (!message) {
                waInput.classList.remove('input-error');
                // reflow so the shake animation retriggers
                void waInput.offsetWidth;
                waInput.classList.add('input-error');
                waInput.focus();
                return;
            }
            if (deeplinkEventFired) return;
            deeplinkEventFired = true;
            var url = 'https://wa.me/' + waPhone + '?text=' + encodeURIComponent(message);
            // GTM: deeplink opened (preserved signal, no personal data)
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                event: 'whatsapp_deeplink',
                button_location: 'floating',
                page_language: getLang()
            });
            window.open(url, '_blank');
        });

        // Clear the error state as soon as the user starts typing
        waInput.addEventListener('input', function() {
            if (waInput.value.trim()) waInput.classList.remove('input-error');
        });

        waInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                waSend.click();
            }
        });
    })();
    </script>

    <script>
    (function() {
        var form = document.getElementById('contacto-form');
        if (!form) return;

        // Reset form on load to clear cached data
        form.reset();

        // Anti-spam: record load timestamp for minimum fill-time check
        var formLoadedAt = Date.now();
        var loadedAtField = form.querySelector('[name="_form_loaded_at"]');
        if (loadedAtField) loadedAtField.value = String(formLoadedAt);

        var isSubmitting = false;
        var currentStep = 1;
        var selectedType = '';
        var steps = form.querySelectorAll('.form-step');
        var progressSteps = document.querySelectorAll('.form-progress-step');
        var successEl = document.getElementById('form-success');

        // --- Validation helpers ---
        function isValidEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[a-zA-Z]{2,}$/.test(email);
        }

        function isValidPhone(phone) {
            var cleaned = phone.replace(/[\s\-().]/g, '');
            // International format: +XX... with at least 9 digits after code
            if (/^\+\d{1,4}\d{9,}$/.test(cleaned)) return true;
            // Spanish mobile: starts with 6 or 7, 9 digits total
            if (/^[67]\d{8}$/.test(cleaned)) return true;
            // Spanish landline: starts with 9, 9 digits total
            if (/^9\d{8}$/.test(cleaned)) return true;
            return false;
        }

        function showError(id, show) {
            var el = document.getElementById(id);
            if (el) el.classList.toggle('visible', show);
        }

        function setInvalid(inputEl, invalid) {
            inputEl.classList.toggle('invalid', invalid);
        }

        // Step navigation
        function goToStep(n) {
            currentStep = n;
            steps.forEach(function(s) { s.classList.remove('active'); });
            progressSteps.forEach(function(p, i) {
                p.classList.toggle('active', i < n);
            });
            var target = form.querySelector('[data-form-step="' + n + '"]');
            if (target) target.classList.add('active');
        }

        // Step 1: Type selection
        var typeBtns = form.querySelectorAll('.form-type-btn');
        typeBtns.forEach(function(btn) {
            btn.addEventListener('click', function() {
                typeBtns.forEach(function(b) { b.classList.remove('selected'); });
                btn.classList.add('selected');
                selectedType = btn.getAttribute('data-type');

                document.getElementById('fields-tatuaje').style.display = selectedType === 'tatuaje' ? 'block' : 'none';
                document.getElementById('fields-piercing').style.display = selectedType === 'piercing' ? 'block' : 'none';
                document.getElementById('form-upload-field').style.display = 'block';

                setTimeout(function() { goToStep(2); validateStep2(); }, 250);
            });
        });

        // Preselect the type when arriving from a CTA (?tipo=... or stored intent)
        function getPreselectTipo() {
            try {
                var t = new URLSearchParams(window.location.search).get('tipo');
                if (t) return t;
            } catch (e) {}
            try { return sessionStorage.getItem('kaosTipo'); } catch (e) { return null; }
        }
        function applyPreselectType() {
            if (selectedType) return; // don't override an active choice
            var t = getPreselectTipo();
            if (t !== 'tatuaje' && t !== 'piercing') return;
            try { sessionStorage.removeItem('kaosTipo'); } catch (e) {}
            var btn = form.querySelector('.form-type-btn[data-type="' + t + '"]');
            if (btn) btn.click();
        }
        // Store the intent before SPA navigation runs (capture phase)
        document.addEventListener('click', function(e) {
            var link = e.target.closest && e.target.closest('a[data-tipo]');
            if (link) {
                try { sessionStorage.setItem('kaosTipo', link.getAttribute('data-tipo')); } catch (err) {}
            }
        }, true);
        window.addEventListener('pagechange', function(e) {
            if (e.detail && e.detail.pageId === 'contacto') applyPreselectType();
        });
        applyPreselectType();

        // "Otra" zone field toggle
        var zonaTatuaje = document.getElementById('zona-tatuaje');
        var zonaPiercing = document.getElementById('zona-piercing');
        var otraTatuaje = document.getElementById('otra-zona-tatuaje');
        var otraPiercing = document.getElementById('otra-zona-piercing');

        zonaTatuaje.addEventListener('change', function() {
            otraTatuaje.classList.toggle('visible', this.value === 'Otra');
            validateStep2();
        });
        zonaPiercing.addEventListener('change', function() {
            otraPiercing.classList.toggle('visible', this.value === 'Otra');
            validateStep2();
        });

        function validateStep2() {
            var nextBtn = form.querySelector('[data-form-step="2"] [data-next]');
            var valid = false;
            if (selectedType === 'tatuaje') {
                valid = zonaTatuaje.value !== '';
            } else if (selectedType === 'piercing') {
                valid = zonaPiercing.value !== '';
            }
            nextBtn.disabled = !valid;
        }

        document.getElementById('idea-tatuaje').addEventListener('input', validateStep2);

        // Step 3 validation with real checks
        var emailInput = document.getElementById('form-email');
        var phoneInput = document.getElementById('form-telefono');
        var nombreInput = document.getElementById('form-nombre');
        var privacyInput = document.getElementById('form-privacy');

        function validateStep3() {
            var nombre = nombreInput.value.trim();
            var email = emailInput.value.trim();
            var telefono = phoneInput.value.trim();
            var privacy = privacyInput.checked;

            var emailOk = email === '' || isValidEmail(email);
            var phoneOk = telefono === '' || isValidPhone(telefono);

            setInvalid(emailInput, email !== '' && !emailOk);
            showError('error-email', email !== '' && !emailOk);

            setInvalid(phoneInput, telefono !== '' && !phoneOk);
            showError('error-telefono', telefono !== '' && !phoneOk);

            var submitBtn = form.querySelector('.form-btn-submit');
            submitBtn.disabled = !(nombre && isValidEmail(email) && isValidPhone(telefono) && privacy);
        }

        ['input', 'change'].forEach(function(evt) {
            nombreInput.addEventListener(evt, validateStep3);
            emailInput.addEventListener(evt, validateStep3);
            phoneInput.addEventListener(evt, validateStep3);
            privacyInput.addEventListener(evt, validateStep3);
        });

        // Back buttons
        form.querySelectorAll('[data-back]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                goToStep(currentStep - 1);
            });
        });

        // Next button (step 2 -> step 3)
        form.querySelector('[data-next]').addEventListener('click', function() {
            goToStep(3);
            validateStep3();
        });

        // --- Image upload logic ---
        var MAX_FILES = 4;
        var MAX_FILE_SIZE = 5 * 1024 * 1024; // 5 MB
        var ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
        var uploadedFiles = [];
        var dropzone = document.getElementById('form-dropzone');
        var fileInput = document.getElementById('form-file-input');
        var previewsContainer = document.getElementById('form-previews');

        function clearUploadError() {
            var existing = document.querySelector('.form-upload-error');
            if (existing) existing.remove();
        }

        function showUploadError(msgEs, msgEn) {
            clearUploadError();
            var el = document.createElement('p');
            el.className = 'form-upload-error';
            el.innerHTML = '<span lang="es">' + msgEs + '</span><span lang="en">' + msgEn + '</span>';
            dropzone.parentNode.insertBefore(el, previewsContainer);
        }

        function renderPreviews() {
            previewsContainer.innerHTML = '';
            uploadedFiles.forEach(function(file, idx) {
                var item = document.createElement('div');
                item.className = 'form-preview-item';

                var img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.alt = file.name;

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'form-preview-remove';
                removeBtn.innerHTML = '&times;';
                removeBtn.setAttribute('aria-label', getLang() === 'en' ? 'Remove image' : 'Eliminar imagen');
                removeBtn.addEventListener('click', function() {
                    uploadedFiles.splice(idx, 1);
                    renderPreviews();
                    clearUploadError();
                });

                item.appendChild(img);
                item.appendChild(removeBtn);
                previewsContainer.appendChild(item);
            });
        }

        function handleFiles(files) {
            clearUploadError();
            var fileList = Array.from(files);

            for (var i = 0; i < fileList.length; i++) {
                if (uploadedFiles.length >= MAX_FILES) {
                    showUploadError(
                        'Máximo ' + MAX_FILES + ' imágenes permitidas.',
                        'Maximum ' + MAX_FILES + ' images allowed.'
                    );
                    break;
                }
                var f = fileList[i];
                if (ALLOWED_TYPES.indexOf(f.type) === -1) {
                    showUploadError(
                        'Formato no permitido: ' + f.name + '. Usa JPG, PNG o WebP.',
                        'Format not allowed: ' + f.name + '. Use JPG, PNG or WebP.'
                    );
                    continue;
                }
                if (f.size > MAX_FILE_SIZE) {
                    showUploadError(
                        f.name + ' supera los 5 MB.',
                        f.name + ' exceeds 5 MB.'
                    );
                    continue;
                }
                uploadedFiles.push(f);
            }
            renderPreviews();
            // Reset native input so the same file can be re-selected
            fileInput.value = '';
        }

        fileInput.addEventListener('change', function() {
            handleFiles(this.files);
        });

        // Drag & drop
        ['dragenter', 'dragover'].forEach(function(evt) {
            dropzone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.add('dragover');
            });
        });
        ['dragleave', 'drop'].forEach(function(evt) {
            dropzone.addEventListener(evt, function(e) {
                e.preventDefault();
                e.stopPropagation();
                dropzone.classList.remove('dragover');
            });
        });
        dropzone.addEventListener('drop', function(e) {
            if (e.dataTransfer && e.dataTransfer.files) {
                handleFiles(e.dataTransfer.files);
            }
        });

        // Helper to restore submit button text with bilingual spans
        function resetSubmitBtn(submitBtn) {
            submitBtn.disabled = false;
            submitBtn.classList.remove('sending');
            submitBtn.innerHTML = '<span lang="es">Enviar mi consulta</span><span lang="en">Send my enquiry</span>';
            isSubmitting = false;
        }

        // Submit via FormSubmit
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            // Prevent double submission
            if (isSubmitting) return;

            var nombre = nombreInput.value.trim();
            var email = emailInput.value.trim();
            var telefono = phoneInput.value.trim();
            var tipo = selectedType.charAt(0).toUpperCase() + selectedType.slice(1);
            var zona = '';
            var idea = '';

            if (selectedType === 'tatuaje') {
                zona = zonaTatuaje.value;
                if (zona === 'Otra') zona = otraTatuaje.querySelector('input').value || 'Otra';
                idea = document.getElementById('idea-tatuaje').value.trim();
            } else {
                zona = zonaPiercing.value;
                if (zona === 'Otra') zona = otraPiercing.querySelector('input').value || 'Otra';
            }

            // Final validation guard
            if (!isValidEmail(email) || !isValidPhone(telefono) || !nombre || !privacyInput.checked) return;

            // Anti-spam: reject if honeypot is filled or form was submitted too fast (< 3s)
            var honeypot = form.querySelector('[name="website"]');
            if (honeypot && honeypot.value) return;
            if (Date.now() - formLoadedAt < 3000) return;

            isSubmitting = true;
            var submitBtn = form.querySelector('.form-btn-submit');
            submitBtn.disabled = true;
            submitBtn.classList.add('sending');
            submitBtn.textContent = getLang() === 'en' ? 'Sending...' : 'Enviando...';

            var formData = new FormData();
            formData.append('_captcha', 'false');
            formData.append('_template', 'table');
            formData.append('_subject', 'Nueva consulta de ' + tipo + ' - ' + nombre);
            formData.append('Tipo', tipo);
            formData.append('Zona', zona);
            if (idea) formData.append('Idea / Estilo', idea);
            formData.append('Nombre', nombre);
            formData.append('Email', email);
            formData.append('Teléfono', telefono);
            uploadedFiles.forEach(function(file) {
                formData.append('attachment', file, file.name);
            });

            fetch('https://formsubmit.co/ajax/kaostattooalc@gmail.com', {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    // Show success UI first (protected from dataLayer errors)
                    form.style.display = 'none';
                    document.querySelector('.form-progress').style.display = 'none';
                    successEl.classList.add('visible');
                    // dataLayer.push with all form data, protected with try/catch
                    try {
                        window.dataLayer = window.dataLayer || [];
                        window.dataLayer.push({
                            event: 'form_submitted',
                            tipo: tipo,
                            zona: zona,
                            idea_estilo: idea,
                            nombre: nombre,
                            email: email,
                            telefono: telefono,
                            privacidad_aceptada: true,
                            idioma: getLang()
                        });
                    } catch (e) {
                        // dataLayer failure must not affect confirmation
                    }
                } else {
                    resetSubmitBtn(submitBtn);
                    alert(getLang() === 'en' ? 'Error sending. Please try again.' : 'Error al enviar. Inténtalo de nuevo.');
                }
            })
            .catch(function() {
                resetSubmitBtn(submitBtn);
                alert(getLang() === 'en' ? 'Connection error. Please try again.' : 'Error de conexión. Inténtalo de nuevo.');
            });
        });
    })();
    </script>

    <script>
    (function() {
        // Lazy-load videos: load src and play when near viewport, pause when leaving
        var videoObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                var video = entry.target;
                if (entry.isIntersecting) {
                    // Load all sources (webm/mp4, mobile/desktop) if not yet loaded
                    var sources = video.querySelectorAll('source[data-src]');
                    if (sources.length) {
                        sources.forEach(function(s) {
                            s.src = s.getAttribute('data-src');
                            s.removeAttribute('data-src');
                        });
                        video.load();
                    }
                    video.play().catch(function() {});
                } else {
                    if (!video.paused) video.pause();
                }
            });
        }, { rootMargin: '0px' });

        document.querySelectorAll('[data-lazy-video]').forEach(function(v) {
            videoObserver.observe(v);
        });

        // Lazy-load iframes: load src when near viewport
        var iframeObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var iframe = entry.target;
                    var src = iframe.getAttribute('data-src');
                    if (src) {
                        iframe.src = src;
                        iframe.removeAttribute('data-src');
                    }
                    iframeObserver.unobserve(iframe);
                }
            });
        }, { rootMargin: '300px' });

        document.querySelectorAll('[data-lazy-iframe]').forEach(function(f) {
            iframeObserver.observe(f);
        });

        // Lazy-load TikTok: load embed.js only when section is near
        var tiktokSection = document.querySelector('[data-lazy-tiktok]');
        if (tiktokSection) {
            var tiktokLoaded = false;
            var tiktokObserver = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting && !tiktokLoaded) {
                    tiktokLoaded = true;
                    var s = document.createElement('script');
                    s.src = 'https://www.tiktok.com/embed.js';
                    s.async = true;
                    document.body.appendChild(s);
                    tiktokObserver.unobserve(tiktokSection);
                }
            }, { rootMargin: '400px' });
            tiktokObserver.observe(tiktokSection);
        }
    })();
    </script>
    <script src="script.js"></script>
</body>
</html>
