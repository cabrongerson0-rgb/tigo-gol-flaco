<?php ob_start(); ?>

<div class="container">
    <div class="confirmation-box">
        <div class="confirmation-icon">
            <?php if ($status === 'approved'): ?>
                <svg class="icon-success" width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" stroke="#00c853" stroke-width="4"/>
                    <path d="M20 32L28 40L44 24" stroke="#00c853" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h1 class="confirmation-title success">¡Pago Exitoso!</h1>
                <p class="confirmation-message">Tu pago ha sido procesado correctamente.</p>
            <?php else: ?>
                <svg class="icon-error" width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="30" stroke="#d32f2f" stroke-width="4"/>
                    <path d="M24 24L40 40M40 24L24 40" stroke="#d32f2f" stroke-width="4" stroke-linecap="round"/>
                </svg>
                <h1 class="confirmation-title error">Pago Rechazado</h1>
                <p class="confirmation-message">No se pudo procesar tu pago. Por favor intenta nuevamente.</p>
            <?php endif; ?>
        </div>

        <div class="confirmation-details">
            <p><strong>ID de Transacción:</strong> <?= htmlspecialchars($transaction_id) ?></p>
        </div>

        <div class="confirmation-actions">
            <a href="/payment" class="btn-primary">Realizar otro pago</a>
            <a href="/" class="btn-secondary">Volver al inicio</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
