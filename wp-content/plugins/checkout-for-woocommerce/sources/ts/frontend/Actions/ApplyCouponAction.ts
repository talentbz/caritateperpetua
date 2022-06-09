import Alert                from '../Components/Alert';
import Main                 from '../Main';
import DataService          from '../Services/DataService';
import LoggingService       from '../Services/LoggingService';
import Action               from './Action';

class ApplyCouponAction extends Action {
    private code: string;

    /**
     * @param {string} code
     */
    constructor( code: string ) {
        const data = {
            security: DataService.getCheckoutParam( 'apply_coupon_nonce' ),
            coupon_code: code,
        };

        super( 'apply_coupon', data );

        this.code = code;
    }

    load(): void {
        const currentTime = new Date();
        const n = currentTime.getTime();

        jQuery.ajax( {
            type: 'POST',
            url: `${this.url}&nocache=${n}`,
            data: this.data,
            dataFilter: this.dataFilter.bind( this ),
            success: this.response.bind( this ),
            error: this.error.bind( this ),
            dataType: 'html',
            cache: false,
        } );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        let success = false;

        // Wrapping the response in a <div /> is required for correct parsing
        const messages = jQuery( jQuery.parseHTML( `<div>${resp}</div>` ) );

        // Errors
        const woocommerceErrorMessages = messages.find( '.woocommerce-error li' ).length ? messages.find( '.woocommerce-error li' ) : messages.find( '.woocommerce-error' );

        jQuery.each( woocommerceErrorMessages, ( i, el ) => {
            const alert: Alert = new Alert( 'error', jQuery.trim( jQuery( el ).text() ), 'cfw-alert-error' );
            alert.addAlert( true );
        } );

        // Info
        const wooCommerceInfoMessages = messages.find( '.woocommerce-info li' ).length ? messages.find( '.woocommerce-info li' ) : messages.find( '.woocommerce-info' );

        jQuery.each( wooCommerceInfoMessages, ( i, el ) => {
            const alert: Alert = new Alert( 'notice', jQuery.trim( jQuery( el ).text() ), 'cfw-alert-info' );
            alert.addAlert( true );
        } );

        // Messages
        const wooCommerceMessages = messages.find( '.woocommerce-message li' ).length ? messages.find( '.woocommerce-message li' ) : messages.find( '.woocommerce-message' );

        jQuery.each( wooCommerceMessages, ( i, el ) => {
            success = true;

            const alert: Alert = new Alert( 'success', jQuery.trim( jQuery( el ).text() ), 'cfw-alert-success' );
            alert.addAlert( true );
        } );

        if ( success ) {
            jQuery( document.body ).trigger( 'cfw-apply-coupon-success' );
            LoggingService.logEvent( 'Fired cfw-apply-coupon-success event.' );
        } else {
            jQuery( document.body ).trigger( 'cfw-apply-coupon-failure' );
            LoggingService.logEvent( 'Fired cfw-apply-coupon-failure event.' );
        }

        jQuery( document.body ).trigger( 'cfw-apply-coupon-complete' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-complete event.' );

        Main.instance.preserveAlerts = true;

        // The following simulates exactly what happens at the end a coupon request through native Woo
        jQuery( document.body ).trigger( 'applied_coupon_in_checkout', [ this.code ] );
        Main.instance.updateCheckoutService.queueUpdateCheckout( {}, { update_shipping_method: false } );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        jQuery( document.body ).trigger( 'cfw-apply-coupon-error' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-error event.' );

        const alert: Alert = new Alert( 'error', `Failed to apply coupon. Error: ${errorThrown} (${textStatus})`, 'cfw-alert-error' );
        alert.addAlert();
    }
}

export default ApplyCouponAction;
