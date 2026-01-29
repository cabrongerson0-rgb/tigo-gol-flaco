<?php ob_start(); ?>

<div class="container">
    <div class="error-box">
        <h1 class="error-title error-404">404</h1>
        <h2><?= htmlspecialchars($title) ?></h2>
        <p class="error-message"><?= htmlspecialchars($message) ?></p>
        <a href="/" class="btn-primary">Volver al inicio</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
