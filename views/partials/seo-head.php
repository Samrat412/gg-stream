<?php
/**
 * SEO Head Partial
 * Renders all meta tags for optimal SEO
 */

$title = $title ?? SITE_NAME;
$description = $description ?? SITE_TAGLINE;
$canonical = $canonical ?? SITE_DOMAIN . $_SERVER['REQUEST_URI'];
$ogImage = $ogImage ?? null;
$ogType = $ogType ?? 'website';
$keywords = $keywords ?? '';
$robots = $robots ?? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
$structuredData = $structuredData ?? null;
$hreflangUrl = $hreflangUrl ?? $canonical;
?>

<!-- Primary Meta Tags -->
<title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
<meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
<meta name="robots" content="<?= $robots ?>">
<meta name="theme-color" content="#0F171E">
<link rel="canonical" href="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

<!-- Geo Targeting for USA + EU -->
<meta name="geo.region" content="US;GB;DE;FR;IT;ES;NL;PL;SE;DK;NO;FI">
<meta name="geo.placename" content="Worldwide">
<meta http-equiv="content-language" content="en">
<meta name="distribution" content="global">
<meta name="target" content="all">

<!-- Open Graph / Facebook -->
<meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:site_name" content="<?= SITE_NAME ?>">
<meta property="og:type" content="<?= $ogType ?>">
<meta property="og:locale" content="en_US">
<meta property="og:locale:alternate" content="en_GB">
<meta property="og:locale:alternate" content="de_DE">
<meta property="og:locale:alternate" content="fr_FR">
<meta property="og:locale:alternate" content="it_IT">
<meta property="og:locale:alternate" content="es_ES">
<meta property="og:locale:alternate" content="nl_NL">
<meta property="og:locale:alternate" content="pl_PL">
<meta property="og:locale:alternate" content="sv_SE">
<meta property="og:locale:alternate" content="da_DK">
<meta property="og:locale:alternate" content="nb_NO">
<meta property="og:locale:alternate" content="fi_FI">
<?php if ($ogImage): ?>
<meta property="og:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
<meta property="og:image:width" content="1280">
<meta property="og:image:height" content="720">
<?php endif; ?>
<meta property="og:url" content="<?= htmlspecialchars($canonical, ENT_QUOTES, 'UTF-8') ?>">

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
<?php if ($ogImage): ?>
<meta name="twitter:image" content="<?= htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Hreflang Tags for USA + EU Countries -->
<link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="en-US" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="en-GB" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="de-DE" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="fr-FR" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="it-IT" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="es-ES" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="nl-NL" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="pl-PL" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="sv-SE" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="da-DK" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="nb-NO" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">
<link rel="alternate" hreflang="fi-FI" href="<?= htmlspecialchars($hreflangUrl, ENT_QUOTES, 'UTF-8') ?>">

<!-- Keywords (if provided) -->
<?php if (!empty($keywords)): ?>
<meta name="keywords" content="<?= htmlspecialchars($keywords, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>

<!-- Structured Data (JSON-LD) -->
<?php if (!empty($structuredData)): ?>
<script type="application/ld+json">
<?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
<?php endif; ?>
