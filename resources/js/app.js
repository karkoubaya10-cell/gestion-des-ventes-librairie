import './bootstrap';

const showProductButton = document.querySelector('#show-new-product');
if (showProductButton) {
    showProductButton.addEventListener('click', () => {
        document.querySelector('#new-product-form').hidden = false;
        document.querySelector('#new-product-form input[name="nom"]').focus();
    });
}

const barcodeInput = document.querySelector('#code_barres');
if (barcodeInput) {
    barcodeInput.focus();
}
