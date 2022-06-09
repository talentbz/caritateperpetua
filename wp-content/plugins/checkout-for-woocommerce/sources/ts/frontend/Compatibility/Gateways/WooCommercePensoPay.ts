import Compatibility from '../Compatibility';

class WooCommercePensoPay extends Compatibility {
    constructor() {
        super( 'WooCommercePensoPay' );
    }

    load(): void {
        jQuery( window ).on( 'load', () => {
            jQuery( '.mobilepay-checkout--force' ).firstOn( 'click', () => {
                ( <any>window ).cfw_suppress_js_field_validation = true;
            } );

            jQuery( document.body ).on( 'change', '[name="payment_method"]', () => {
                if ( jQuery( '[name="payment_method"]:checked' ).val().toString() !== 'mobilepay_checkout' ) {
                    ( <any>window ).cfw_suppress_js_field_validation = false;
                } else {
                    ( <any>window ).cfw_suppress_js_field_validation = true;
                }
            } );
        } );
    }
}

export default WooCommercePensoPay;
