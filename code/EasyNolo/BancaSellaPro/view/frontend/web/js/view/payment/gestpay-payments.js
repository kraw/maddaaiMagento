define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'easynolo_bancasellapro',
                component: 'EasyNolo_BancaSellaPro/js/view/payment/method-renderer/gestpay-method'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);