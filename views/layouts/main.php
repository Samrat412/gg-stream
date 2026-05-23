<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <?php include __DIR__ . '/partials/seo-head.php'; ?>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/components.css">
    <link rel="stylesheet" href="/css/animations.css">
    
    <!-- Preload hero image if available -->
    <?php if (!empty($ogImage)): ?>
    <link rel="preload" as="image" href="<?= e($ogImage) ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    
    <!-- Main Content -->
    <main id="main-content">
        <?php include __DIR__ . "/../pages/{$view}.php"; ?>
    </main>
    
    <!-- Footer -->
    <?php include __DIR__ . '/partials/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="/js/app.js" defer></script>
    <script src="/js/store.js" defer></script>
    <script src="/js/comments.js" defer></script>
    <script src="/js/share.js" defer></script>
    <script src="/js/visitor-tracker.js" defer></script>
</body>
</html>
