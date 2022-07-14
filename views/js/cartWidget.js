$(document).ready(function () {
    creditkey.total = Number(creditkey.total);
    creditkey.minimum_cart_total = Number(creditkey.minimum_cart_total);

    if (creditkey.cart_css_selector && (creditkey.total >= creditkey.minimum_cart_total) && creditkey.show_promo_on_cart == 1) {
        let client = new ck.Client(creditkey.public_key, creditkey.mode);
        let charges = new ck.Charges(creditkey.total, 0, 0, 0, creditkey.total);
        $(creditkey.cart_css_selector).append(client.get_cart_display(charges, creditkey.alignment_for_desktop, creditkey.alignment_for_mobile));
    }
});
