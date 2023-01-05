define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Catalog/js/price-utils'
], function ($, ko, _, Component, priceUtils) {
    return Component.extend({
        getFormattedAmount(amount) {
            return priceUtils.formatPrice(amount, {pattern:'%s ' + this.source.data.hokodo.credit_limit.currency}, false)
        }
    })
})
