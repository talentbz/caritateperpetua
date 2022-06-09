import CompleteOrderAction from '../Actions/CompleteOrderAction';
import Main                from '../Main';
import DataService         from './DataService';
import LoggingService      from './LoggingService';

class CompleteOrderService {
    constructor() {
        this.setCheckoutErrorHandler();
        this.setCompleteOrderListener();
    }

    setCheckoutErrorHandler(): void {
        jQuery( document.body ).on( 'checkout_error', () => {
            jQuery( document.body ).trigger( 'cfw-remove-overlay' );
            LoggingService.logEvent( 'Fired cfw-remove-overlay event.' );
        } );
    }

    /**
     *
     */
    setCompleteOrderListener(): void {
        DataService.checkoutForm.on( 'submit', this.completeOrderSubmitHandler.bind( this ) );
    }

    /**
     * Kick off complete order
     */
    completeOrderSubmitHandler() {
        // Prevent any update checkout calls from spawning
        Main.instance.updateCheckoutService.resetUpdateCheckoutTimer();

        if ( DataService.checkoutForm.is( '.processing' ) ) {
            return false;
        }

        DataService.checkoutForm.find( '.woocommerce-error' ).remove();

        // If all the payment stuff has finished any ajax calls, run the complete order.
        // eslint-disable-next-line max-len
        if ( DataService.checkoutForm.triggerHandler( 'checkout_place_order' ) !== false && DataService.checkoutForm.triggerHandler( `checkout_place_order_${DataService.checkoutForm.find( 'input[name="payment_method"]:checked' ).val()}` ) !== false ) {
            DataService.checkoutForm.addClass( 'processing' );

            Main.addOverlay();

            new CompleteOrderAction( DataService.checkoutForm.serialize() );
        }

        /**
         * Throwing an error here seems to cause situations where the error
         * briefly appears during a successful order
         */
        return false;
    }
}

export default CompleteOrderService;
