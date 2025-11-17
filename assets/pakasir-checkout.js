// Example for a simple payment method (like Cash on Delivery)
window.addEventListener("fluent_cart_load_payments_pakasir", function (e) {
    const submitButton = window.fluentcart_checkout_vars?.submit_button;
    const gatewayContainer = document.querySelector('.fluent-cart-checkout_embed_payment_container_pakasir');
    
    // Simple implementation
    if (gatewayContainer) {
        gatewayContainer.innerHTML = '<p>Bayar dengan QRIS, Virtual Account, dll</p>';
    }

    // Enable the checkout button
    e.detail.paymentLoader.enableCheckoutButton(submitButton.text);
});