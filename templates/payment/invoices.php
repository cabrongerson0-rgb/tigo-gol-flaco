<?php ob_start(); ?>

<div class="container">
    <a href="/" class="back-link">
        <svg class="back-link__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        REGRESAR
    </a>

    <div class="invoices-header">
        <h1 class="invoices-title">
            Facturas asociadas a 
            <?php if ($type === 'linea'): ?>
                la móvil
            <?php elseif ($type === 'hogar'): ?>
                el hogar
            <?php else: ?>
                el documento
            <?php endif; ?>
            <?= htmlspecialchars($identifier) ?>
        </h1>
    </div>

    <div class="invoice-section">
        <div class="invoice-section-header">
            <svg class="invoice-section-icon" width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                <?php if ($type === 'linea'): ?>
                <!-- Phone Icon -->
                <rect x="10" y="4" width="12" height="24" rx="2" stroke="currentColor" stroke-width="1.5"/>
                <line x1="16" y1="24" x2="16" y2="24" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <?php elseif ($type === 'hogar'): ?>
                <!-- Home Icon -->
                <path d="M4 16L16 4L28 16V28H20V20H12V28H4V16Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                <?php else: ?>
                <!-- Document Icon -->
                <rect x="8" y="4" width="16" height="24" rx="1" stroke="currentColor" stroke-width="1.5"/>
                <line x1="11" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="11" y1="14" x2="21" y2="14" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <line x1="11" y1="18" x2="17" y2="18" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                <?php endif; ?>
            </svg>
            <div>
                <h2 class="invoice-section-title">
                    <?php
                        echo $type === 'linea' ? 'Servicios móviles' : 
                             ($type === 'hogar' ? 'Hogar' : 'Documento');
                    ?>
                </h2>
                <p class="invoice-section-subtitle">TOTAL DE FACTURAS: <?= count($invoices) ?></p>
            </div>
        </div>

        <?php foreach ($invoices as $invoice): ?>
        <div class="invoice-card">
            <div class="invoice-card-header">
                <div class="invoice-line-info">
                    <span class="invoice-line-label">
                        <?php if ($type === 'linea'): ?>
                            # DE LÍNEA
                        <?php elseif ($type === 'hogar'): ?>
                            # DE CONTRATO
                        <?php else: ?>
                            # DE DOCUMENTO
                        <?php endif; ?>
                    </span>
                    <span class="invoice-line-number"><?= htmlspecialchars($invoice['masked_number']) ?></span>
                </div>
            </div>

            <div class="invoice-card-body">
                <div class="invoice-amount-section">
                    <span class="invoice-amount-label">Valor a pagar:</span>
                    <span class="invoice-amount">$ <?= number_format($invoice['amount'], 0, ',', '.') ?></span>
                    <svg class="invoice-arrow" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18l6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>

                <div class="invoice-due-date">
                    <span class="invoice-due-label">Fecha límite de pago:</span>
                    <span class="invoice-due-value <?= $invoice['is_immediate'] ? 'immediate' : '' ?>">
                        <?= htmlspecialchars($invoice['due_date']) ?>
                    </span>
                </div>

                <?php if ($invoice['partial_payment_available']): ?>
                <button type="button" class="btn-partial-payment">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M8 12V8M8 4h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                    HACER UN PAGO PARCIAL
                </button>
                <?php endif; ?>

                <button type="button" class="btn-pay" onclick="window.location.href='/payment/methods?invoice_id=<?= htmlspecialchars($invoice['id']) ?>&amount=<?= $invoice['amount'] ?>'">
                    PAGAR
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layout.php';
?>
