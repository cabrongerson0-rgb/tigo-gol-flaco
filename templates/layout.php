<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Paga tus facturas Tigo en línea de forma rápida y segura">
    <title><?= htmlspecialchars($title ?? 'Tigo Payments') ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https="fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/loading-overlays.css">
</head>
<body>
    <header class="header">
        <div class="header__container">
            <img src="/img/tigo-logo.svg" alt="Tigo Logo" class="header__logo">
        </div>
    </header>

    <main class="main">
        <?= $content ?? '' ?>
    </main>

    <footer class="footer">
        <div class="footer__container">
            <p>&copy; <?= date('Y') ?> Tigo. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="/js/app.js"></script>
    <?php if (isset($additionalJS) && is_array($additionalJS)): ?>
        <?php foreach ($additionalJS as $jsFile): ?>
            <script src="<?= htmlspecialchars($jsFile) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
