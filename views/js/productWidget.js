$(document).ready(function () {
    creditkey.price = Number(creditkey.price);
    creditkey.minimum_product_price = Number(creditkey.minimum_product_price);

    if (creditkey.product_widget_selector && creditkey.price >= creditkey.minimum_product_price && creditkey.show_promo_on_product == 1) {
        let client = new ck.Client(creditkey.public_key, creditkey.mode);
        let charges = new ck.Charges(creditkey.price, 0, 0, 0, creditkey.price);
        $(creditkey.product_widget_selector).append(client.get_pdp_display(charges));
    }
});