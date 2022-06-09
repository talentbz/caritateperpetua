import DataService          from '../../Services/DataService';
import TabService           from '../../Services/TabService';
import Compatibility        from '../Compatibility';

declare let OffAmazonPayments: any;
// eslint-disable-next-line camelcase
declare let amazon_payments_advanced_params: any;

class AmazonPayLegacy extends Compatibility {
    constructor() {
        super( 'AmazonPayLegacy' );
    }

    load(): void {
        /**
         * If the OffAmazonPayments and amazon_payments_advanced_params exist
         * we can then check to see if there is a reference id set.
         *
         * If not we are not logged in. If there is we are logged in.
         */
        try {
            // eslint-disable-next-line max-len,camelcase
            if ( OffAmazonPayments !== undefined && amazon_payments_advanced_params !== undefined && ( amazon_payments_advanced_params.reference_id !== '' || amazon_payments_advanced_params.access_token !== '' ) ) {
                jQuery( window.document ).on( 'wc_amazon_pa_widget_ready', () => {
                    const compatClass = DataService.getCompatibilityClass( 'AmazonPayLegacy' );
                    if ( typeof compatClass.params.cfw_amazon_suppress_shipping_field_validation !== 'undefined' && compatClass.params.cfw_amazon_suppress_shipping_field_validation ) {
                        ( <any>window ).cfw_suppress_js_field_validation = true;
                    }
                } );

                jQuery( window.document ).on( 'wc_amazon_pa_widget_ready updated_checkout', () => {
                    jQuery( '.create-account .cfw-input-wrap' ).addClass( 'cfw-floating-label' );
                } );

                jQuery( window.document ).on( 'wc_amazon_pa_widget_ready', () => {
                    jQuery( '#billing_same_as_shipping_radio' ).remove();
                } );

                if ( DataService.getCheckoutParam( 'option_guest_checkout' ) === 'yes' ) {
                    jQuery( document.body ).on( 'change', 'input#createaccount', this.toggleCreateAccount );
                }

                jQuery( document.body ).on( 'updated_checkout', () => {
                    jQuery( 'input#createaccount' ).trigger( 'change' );
                } );

                jQuery( window ).on( 'load updated_checkout', () => {
                    this.cleanUpExtraStuff();
                } );
            }
        } catch ( error ) {
            // eslint-disable-next-line no-console
            console.log( error );
        }
    }

    toggleCreateAccount() {
        const createAccount = jQuery( 'div.create-account' );
        createAccount.hide();

        if ( jQuery( this ).is( ':checked' ) ) {
            // Ensure password is not pre-populated.
            jQuery( '#account_password' ).val( '' ).trigger( 'change' );
            createAccount.slideDown();
        }
    }

    cleanUpExtraStuff() {
        jQuery( '#payment-info-separator-wrap' ).hide();
        jQuery( '#cfw-shipping-same-billing' ).hide();
        jQuery( '#cfw-billing-methods > h3' ).hide();
        jQuery( '#cfw-customer-info-address > h3' ).hide();
        jQuery( TabService.paymentMethodTabId ).find( 'h3' ).hide();
        jQuery( '.cfw-billing-address-heading' ).hide();
        jQuery( '.cfw-billing-address-description' ).hide();
    }
}

export default AmazonPayLegacy;
