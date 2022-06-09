import Alert          from '../Components/Alert';
import Main           from '../Main';
import DataService    from '../Services/DataService';
import LoggingService from '../Services/LoggingService';
import Action         from './Action';

/**
 *
 */
class RemoveCouponAction extends Action {
    private couponCode: string;

    /**
     * @param {string} code
     */
    constructor( code: string ) {
        const data = {
            security: DataService.getCheckoutParam( 'remove_coupon_nonce' ),
            coupon_code: code,
        };

        super( 'cfw_remove_coupon', data );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            resp = JSON.parse( resp );
        }

        jQuery( 'form.woocommerce-checkout' ).before( resp.html );

        jQuery( document.body ).trigger( 'removed_coupon_in_checkout', [ resp.coupon ] );

        Main.instance.updateCheckoutService.queueUpdateCheckout( {}, { update_shipping_method: false } );

        // Remove coupon code from coupon field
        DataService.checkoutForm.find( '#cfw-promo-code' ).val( '' );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        jQuery( document.body ).trigger( 'cfw-apply-coupon-error' );
        LoggingService.logEvent( 'Fired cfw-apply-coupon-error event.' );

        const alert: Alert = new Alert( 'error', `Failed to remove coupon. Error: ${errorThrown} (${textStatus})`, 'cfw-alert-error' );
        alert.addAlert();
    }
}

export default RemoveCouponAction;
