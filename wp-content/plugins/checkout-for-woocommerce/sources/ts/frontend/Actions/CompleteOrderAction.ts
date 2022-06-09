import Alert                 from '../Components/Alert';
import Main                  from '../Main';
import DataService           from '../Services/DataService';
import LoggingService        from '../Services/LoggingService';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';
import Action                from './Action';

class CompleteOrderAction extends Action {
    /**
     * @param checkoutData
     */
    constructor( checkoutData: any ) {
        super( 'complete_order', checkoutData );

        DataService.checkoutForm.off( 'form:validate' );

        this.load();
    }

    /**
     * @param resp
     */
    public response( resp: any ): void {
        try {
            if ( resp.result === 'success' && DataService.checkoutForm.triggerHandler( 'checkout_place_order_success', resp ) !== false ) {
                // Fire events that need to run before redirect
                jQuery( document.body ).trigger( 'cfw-order-complete-before-redirect', [ DataService.checkoutForm, resp ] );
                LoggingService.logEvent( 'Fired cfw-order-complete-before-redirect event.' );

                Main.instance.parsleyService.destroy();

                if ( resp.redirect.indexOf( 'https://' ) === -1 || resp.redirect.indexOf( 'http://' ) === -1 ) {
                    ( <any>window ).location = resp.redirect;
                } else {
                    ( <any>window ).location = decodeURI( resp.redirect );
                }
            } else if ( resp.result === 'failure' ) {
                throw new Error( 'Result failure' );
            } else {
                throw new Error( 'Invalid response' );
            }
        } catch ( err ) {
            // Amazon Pay triggers update_checkout after a failed submission
            // This prevents the generated alerts from being immediately scrubbed.
            if ( Main.instance ) {
                Main.instance.preserveAlerts = true;
            }

            // Reload page
            if ( resp.reload === true ) {
                ( <any>window ).location.reload();
                return;
            }

            // Trigger update in case we need a fresh nonce
            if ( resp.refresh === true ) {
                jQuery( document.body ).trigger( 'update_checkout' );
            }

            ( <any>window ).dispatchEvent( new CustomEvent( 'cfw-checkout-failed-before-error-message', { detail: { response: resp } } ) );

            Alert.removeAlerts( DataService.getElement( 'alertContainerId' ) );

            if ( typeof resp.messages === 'string' && resp.messages.length ) {
                // Wrapping the response in a <div /> is required for correct parsing
                const messages = jQuery( jQuery.parseHTML( `<div>${resp.messages}</div>` ) );

                // Errors
                const woocommerceErrorMessages = messages.find( '.woocommerce-error li' ).length ? messages.find( '.woocommerce-error li' ) : messages.find( '.woocommerce-error' );

                jQuery.each( woocommerceErrorMessages, ( i, el ) => {
                    const alert: Alert = new Alert( 'error', jQuery.trim( jQuery( el ).text() ), 'cfw-alert-error' );
                    alert.addAlert();
                } );

                // Info
                const wooCommerceInfoMessages = messages.find( '.woocommerce-info li' ).length ? messages.find( '.woocommerce-info li' ) : messages.find( '.woocommerce-info' );

                jQuery.each( wooCommerceInfoMessages, ( i, el ) => {
                    const alert: Alert = new Alert( 'notice', jQuery.trim( jQuery( el ).text() ), 'cfw-alert-info' );
                    alert.addAlert();
                } );

                // Messages
                const wooCommerceMessages = messages.find( '.woocommerce-message li' ).length ? messages.find( '.woocommerce-message li' ) : messages.find( '.woocommerce-message' );

                jQuery.each( wooCommerceMessages, ( i, el ) => {
                    const alert: Alert = new Alert( 'success', jQuery.trim( jQuery( el ).text() ), 'cfw-alert-success' );
                    alert.addAlert();
                } );

                // EveryPay doesn't understand WooCommerce, so fix it for them
                if ( resp.messages.indexOf( '<script' ) !== -1 ) {
                    jQuery( document.body ).prepend( `<div style="display:none">${resp.messages}</div>` );
                }
            } else {
                /**
                 * If the payment gateway comes back with no message, show a generic error.
                 */
                const message = DataService.getCheckoutParam( 'i18n_checkout_error' );
                const alert: Alert = new Alert(
                    'error',
                    message,
                    'cfw-alert-error',
                );
                alert.addAlert();

                // Console log the error + raw response
                LoggingService.logError( message );
            }

            // Trigger update in case we need a fresh nonce
            if ( resp.refresh === true ) {
                UpdateCheckoutService.triggerUpdatedCheckout();
            }

            this.submitError();
        }
    }

    /**
     * Try to fix invalid JSON
     *
     * @param rawResponse
     * @param dataType
     */
    public dataFilter( rawResponse: string, dataType: string ): any {
        // We only want to work with JSON
        if ( dataType !== 'json' ) {
            return rawResponse;
        }

        if ( this.isValidJSON( rawResponse ) ) {
            return rawResponse;
        }
        // Attempt to fix the malformed JSON
        const maybeValidJSON = rawResponse.match( /{"result.*}/ );

        if ( maybeValidJSON === null ) {
            console.log( 'Unable to fix malformed JSON' );
        } else if ( this.isValidJSON( maybeValidJSON[ 0 ] ) ) {
            console.log( 'Fixed malformed JSON. Original:' );
            console.log( rawResponse );
            // eslint-disable-next-line no-param-reassign,prefer-destructuring
            rawResponse = maybeValidJSON[ 0 ];
        } else {
            console.log( 'Unable to fix malformed JSON' );
        }

        return rawResponse;
    }

    public isValidJSON( rawJSON ) {
        try {
            const json = JSON.parse( rawJSON );

            return ( json && typeof json === 'object' );
        } catch ( e ) {
            return false;
        }
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        let message: string;

        if ( xhr.status === 0 ) {
            message = 'Could not connect to server. Please refresh and try again or contact site administrator.';
        } else if ( xhr.status === 404 ) {
            message = 'Requested resource could not be found. Please contact site administrator. (404)';
        } else if ( xhr.status === 500 ) {
            message = 'An internal server error occurred. Please contact site administrator. (500)';
        } else if ( textStatus === 'parsererror' ) {
            message = 'Server response could not be parsed. Please contact site administrator.';
        } else if ( textStatus === 'timeout' || xhr.status === 504 ) {
            message = 'The server timed out while processing your request. Please refresh and try again or contact site administrator.';
        } else if ( textStatus === 'abort' ) {
            message = 'Request was aborted. Please contact site administrator.';
        } else {
            message = `Uncaught Error: ${xhr.responseText}`;
        }

        console.log( `CheckoutWC XHR response: ${xhr.response}` );
        console.log( `CheckoutWC XHR responseText: ${xhr.responseText}` );
        console.log( `CheckoutWC XHR status: ${xhr.status}` );
        console.log( `CheckoutWC XHR errorThrown: ${errorThrown}` );

        const alert: Alert = new Alert( 'error', message, 'cfw-alert-error' );
        alert.addAlert();

        this.submitError();
    }

    submitError() {
        // Remove processing / unblock it
        DataService.checkoutForm.removeClass( 'processing' ).unblock();

        jQuery( document.body ).trigger( 'checkout_error' );
        LoggingService.logEvent( 'Fired checkout_error event.' );
    }
}

export default CompleteOrderAction;
