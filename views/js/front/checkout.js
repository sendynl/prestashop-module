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

        if (!window.prestashop || !window.prestashop.customer || !window.prestashop.customer.addresses) {
            alert('Address data is not available.');
            return;
        }

        const parent = $(this).parent();
        const parcelShopUrl = parent.data('sendy-parcel-shop-url');

        if (!parcelShopUrl) {
            alert('No Parcel Shop URL found.');
            return;
        }

        const deliveryAddressId = parent.data('sendy-id-address-delivery')

        if (!deliveryAddressId) {
            alert('No delivery address found.');
            return;
        }

        const deliveryAddress = window.prestashop.customer.addresses[deliveryAddressId];

        if (!deliveryAddress) {
            alert('Delivery address data not found.');
            return;
        }

        const countryCode = deliveryAddress.country_iso || window.prestashop.country?.iso_code;

        if (!countryCode) {
            alert('Country code not found in delivery address.');
            return;
        }

        window.Sendy.parcelShopPicker.open(
            {
                address: `${deliveryAddress.address1}, ${deliveryAddress.postcode} ${deliveryAddress.city}`,
                country: countryCode,
                carriers: [parent.data('sendy-parcel-shop-picker-carrier')]
            },
            async function(parcelShop) {
                const response = await fetch(parcelShopUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        parcel_shop_id: parcelShop.id,
                        parcel_shop_name: parcelShop.name,
                        parcel_shop_address: `${parcelShop.street} ${parcelShop.number}, ${parcelShop.postal_code} ${parcelShop.city}`,
                    }),
                    headers: {'content-type': 'application/json'}
                });

                const responseData = await response.json();

                parent.find('.sendy-parcel-shop-picker-name').text(responseData.parcel_shop_name);
                parent.find('.sendy-parcel-shop-picker-address').text(responseData.parcel_shop_address);
            },
            function() {
                alert('An error occurred while selecting a parcel shop.');
            }
        );
    });
});
