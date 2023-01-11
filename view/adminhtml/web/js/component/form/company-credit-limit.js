define([
    'jquery',
    'ko',
    'underscore',
    'uiComponent',
    'Magento_Catalog/js/price-utils'
], function ($, ko, _, Component, priceUtils) {
    return Component.extend({
        isCompanyIdAssigned() {
            return !!this.source.data.hokodo.company_id;
        },

        getFormattedAmount(amount) {
            return priceUtils.formatPrice(amount, {pattern:'%s ' + this.source.data.hokodo.credit_limit.currency}, false)
        }
    })
})
