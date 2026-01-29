// Card Form Validation and Formatting
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('cardForm');
    if (!form) return;

    const cardNumberInput = document.getElementById('cardNumber');
    const expiryDateInput = document.getElementById('expiryDate');
    const cvvInput = document.getElementById('cvv');
    const cardholderNameInput = document.getElementById('cardholderName');
    const addressInput = document.getElementById('address');
    const docNumberInput = document.getElementById('docNumber');
    const emailInput = document.getElementById('email');
    const submitBtn = document.getElementById('submitBtn');

    // Modal elements for Installments
    const installmentsBtn = document.getElementById('installmentsBtn');
    const installmentsModal = document.getElementById('installmentsModal');
    const installmentsText = document.getElementById('installmentsText');
    const installmentsInput = document.getElementById('installments');
    const cancelInstallmentsBtn = document.getElementById('cancelInstallmentsBtn');
    const continueInstallmentsBtn = document.getElementById('continueInstallmentsBtn');

    // Modal elements for Document Type
    const docTypeBtn = document.getElementById('docTypeBtn');
    const docTypeModal = document.getElementById('docTypeModal');
    const docTypeText = document.getElementById('docTypeText');
    const docTypeInput = document.getElementById('docType');
    const cancelDocTypeBtn = document.getElementById('cancelDocTypeBtn');
    const continueDocTypeBtn = document.getElementById('continueDocTypeBtn');

    // Open Installments Modal
    installmentsBtn.addEventListener('click', function() {
        installmentsModal.classList.add('active');
        const currentValue = installmentsInput.value;
        const radios = document.querySelectorAll('input[name="installmentsOption"]');
        radios.forEach(radio => {
            radio.checked = radio.value === currentValue;
        });
    });

    // Close Installments Modal - Cancel
    cancelInstallmentsBtn.addEventListener('click', function() {
        installmentsModal.classList.remove('active');
    });

    // Close Installments Modal - Continue
    continueInstallmentsBtn.addEventListener('click', function() {
        const selectedOption = document.querySelector('input[name="installmentsOption"]:checked');
        if (selectedOption) {
            installmentsInput.value = selectedOption.value;
            installmentsText.textContent = selectedOption.value;
        }
        installmentsModal.classList.remove('active');
        validateForm();
    });

    // Close modal when clicking overlay
    installmentsModal.addEventListener('click', function(e) {
        if (e.target === installmentsModal) {
            installmentsModal.classList.remove('active');
        }
    });

    // Open Document Type Modal
    docTypeBtn.addEventListener('click', function() {
        docTypeModal.classList.add('active');
        const currentValue = docTypeInput.value;
        const radios = document.querySelectorAll('input[name="docTypeOption"]');
        radios.forEach(radio => {
            radio.checked = radio.value === currentValue;
        });
    });

    // Close Document Type Modal - Cancel
    cancelDocTypeBtn.addEventListener('click', function() {
        docTypeModal.classList.remove('active');
    });

    // Close Document Type Modal - Continue
    continueDocTypeBtn.addEventListener('click', function() {
        const selectedOption = document.querySelector('input[name="docTypeOption"]:checked');
        if (selectedOption) {
            docTypeInput.value = selectedOption.value;
            docTypeText.textContent = selectedOption.value;
        }
        docTypeModal.classList.remove('active');
        validateForm();
    });

    // Close modal when clicking overlay
    docTypeModal.addEventListener('click', function(e) {
        if (e.target === docTypeModal) {
            docTypeModal.classList.remove('active');
        }
    });

    // Format card number with spaces (#### #### #### ####) - Solo números
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
        let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
        e.target.value = formattedValue;
        validateForm();
    });

    // Prevenir entrada no numérica en tarjeta
    cardNumberInput.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab') {
            e.preventDefault();
        }
    });

    // Format expiry date (XX/XX) con validación de fecha futura
    expiryDateInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        
        if (value.length >= 2) {
            value = value.slice(0, 2) + '/' + value.slice(2, 4);
        }
        
        e.target.value = value;
        validateForm();
    });

    expiryDateInput.addEventListener('keydown', function(e) {
        // Handle backspace to allow deleting the slash
        if (e.key === 'Backspace' && e.target.value.length === 3 && e.target.value.indexOf('/') === 2) {
            e.target.value = e.target.value.slice(0, 2);
            e.preventDefault();
            validateForm();
            return;
        }
        
        // Allow backspace, delete, tab, escape, enter, arrows
        if ([8, 9, 13, 27, 37, 38, 39, 40, 46].indexOf(e.keyCode) !== -1 ||
            (e.keyCode === 65 && e.ctrlKey === true) ||
            (e.keyCode === 67 && e.ctrlKey === true) ||
            (e.keyCode === 86 && e.ctrlKey === true) ||
            (e.keyCode === 88 && e.ctrlKey === true)) {
            return;
        }
        
        // Ensure that it is a number
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    // Only numbers for CVV, max 4 digits
    cvvInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value.slice(0, 4); // Máximo 4 dígitos
        validateForm();
    });

    cvvInput.addEventListener('keypress', function(e) {
        if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab') {
            e.preventDefault();
        }
    });

    // Only numbers for document number
    docNumberInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
        validateForm();
    });

    // Uppercase for cardholder name - Automático en mayúsculas
    cardholderNameInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
        validateForm();
    });

    // Validate all inputs on change
    [addressInput, emailInput].forEach(input => {
        input.addEventListener('change', validateForm);
        input.addEventListener('input', validateForm);
    });

    // Validar que la fecha de expiración sea mayor a la fecha actual
    function isValidExpiryDate(expiryValue) {
        if (!/^\d{2}\/\d{2}$/.test(expiryValue)) {
            return false;
        }

        const [month, year] = expiryValue.split('/').map(num => parseInt(num, 10));
        
        // Validar mes (01-12)
        if (month < 1 || month > 12) {
            return false;
        }

        // Obtener fecha actual
        const now = new Date();
        const currentYear = now.getFullYear() % 100; // Últimos 2 dígitos del año
        const currentMonth = now.getMonth() + 1; // getMonth() es 0-indexed

        // Si el año es menor al actual, inválido
        if (year < currentYear) {
            return false;
        }

        // Si el año es igual al actual, el mes debe ser mayor o igual al actual
        if (year === currentYear && month < currentMonth) {
            return false;
        }

        return true;
    }

    function validateForm() {
        const cardNumberValid = cardNumberInput.value.replace(/\s/g, '').length >= 13;
        const expiryDateValid = isValidExpiryDate(expiryDateInput.value);
        const cvvValid = cvvInput.value.length >= 3 && cvvInput.value.length <= 4;
        const cardholderNameValid = cardholderNameInput.value.trim().length > 0;
        const installmentsValid = installmentsInput.value !== '';
        const addressValid = addressInput.value.trim().length > 0;
        const docTypeValid = docTypeInput.value !== '';
        const docNumberValid = docNumberInput.value.length > 0;
        const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value);

        const allValid = cardNumberValid && expiryDateValid && cvvValid && 
                        cardholderNameValid && installmentsValid && addressValid && 
                        docTypeValid && docNumberValid && emailValid;

        submitBtn.disabled = !allValid;
    }

    // Form submission - El manejo lo hace card-telegram.js
    // No procesamos aquí, solo evitamos el submit por defecto
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        // card-telegram.js se encargará del resto
    });

    // Initial validation
    validateForm();

    console.log('[CARD FORM] Validaciones inicializadas');
});
