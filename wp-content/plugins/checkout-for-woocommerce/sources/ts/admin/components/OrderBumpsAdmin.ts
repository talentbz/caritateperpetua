class OrderBumpsAdmin {
    constructor() {
        jQuery( document.body ).on( 'change', '#cfw_ob_display_for', ( event ) => {
            const element = jQuery( event.currentTarget );
            const specificProductsTargetElements = jQuery( '#cfw_ob_products, #cfw_ob_any_product, #cfw_ob_upsell' ).parents( 'tr' );
            const specificCategoriesTargetElements = jQuery( '#cfw_ob_categories' ).parents( 'tr' );

            if ( element.val() === 'specific_products' ) {
                specificProductsTargetElements.show();
            } else {
                specificProductsTargetElements.hide();
            }

            if ( element.val() !== 'specific_categories' ) {
                specificCategoriesTargetElements.hide();
            } else {
                specificCategoriesTargetElements.show();
            }
        } );

        jQuery( '#cfw_ob_display_for' ).trigger( 'change' );
    }
}

export default OrderBumpsAdmin;
