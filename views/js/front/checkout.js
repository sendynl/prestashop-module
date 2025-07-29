/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */
$(function() {
    $('.sendy-parcel-shop-picker-button').on('click', function(e) {
        if (!window.Sendy || !window.Sendy.parcelShopPicker) {
            alert('Sendy Parcel Shop Picker is not available.')
            return;
        }

        if (!window.prestashop || !window.prestashop.country || !window.prestashop.customer || !window.prestashop.customer.addresses) {
            alert('Checkout data is not available.');
            return;
        }

        const deliveryAddressId = $(this).parent().data('sendy-id-address-delivery')

        if (!deliveryAddressId) {
            alert('No delivery address found.');
            return;
        }

        const deliveryAddress = window.prestashop.customer.addresses[deliveryAddressId];

        if (!deliveryAddress) {
            alert('Delivery address data not found.');
            return;
        }

        const data = {
            address: `${deliveryAddress.address1}, ${deliveryAddress.postcode} ${deliveryAddress.city}`,
            country: window.prestashop.country.iso_code,
            carriers: [$(this).parent().data('sendy-parcel-shop-picker-carrier')]
        };

        window.Sendy.parcelShopPicker.open(data, function(parcelShop) {
            alert('checkout.js: success callback') ;
        }, function(error) {
            alert('checkout.js: failure callback');
        });
    });
});
