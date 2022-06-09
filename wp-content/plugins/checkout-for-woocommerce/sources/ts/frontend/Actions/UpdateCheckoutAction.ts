import Alert                 from '../Components/Alert';
import Main                  from '../Main';
import DataService           from '../Services/DataService';
import LoggingService        from '../Services/LoggingService';
import TabService            from '../Services/TabService';
import UpdateCheckoutService from '../Services/UpdateCheckoutService';
import Action                from './Action';

class UpdateCheckoutAction extends Action {
    /**
     *
     */
    private static _underlyingRequest: any = null;

    private static _fragments: any = [];

    /**
     *
     */
    // eslint-disable-next-line max-len
    private static _blockUISelector;

    /**
     * @param fields
     */
    constructor( fields: any ) {
        super( 'update_checkout', fields );

        UpdateCheckoutAction.blockUISelector = '#cfw-billing-methods, .cfw-review-pane, #cfw-cart-summary, #cfw-place-order, #cfw-payment-request-buttons, #cfw-mobile-total, .cfw-order-bumps, #cfw-shipping-methods';
    }

    public load(): void {
        this.blockUI();

        if ( UpdateCheckoutAction.underlyingRequest !== null ) {
            UpdateCheckoutAction.underlyingRequest.abort();
        }

        const currentTime = new Date();
        const n = currentTime.getTime();

        UpdateCheckoutAction.underlyingRequest = jQuery.post( `${this.url}&nocache=${n}`, this.data, this.response.bind( this ) );
    }

    public blockUI(): void {
        jQuery( UpdateCheckoutAction.blockUISelector ).not( '.cfw-blocked' ).block( {
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0,
            },
        } ).addClass( 'cfw-blocked' );
    }

    public unblockUI(): void {
        jQuery( UpdateCheckoutAction.blockUISelector ).unblock().removeClass( 'cfw-blocked' );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
        // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        if ( resp.redirect !== false ) {
            window.location = resp.redirect;
        }

        /**
         * Save payment details to a temporary object
         */
        const paymentBoxInputsSelector = '.payment_box :input';
        const paymentBoxInputs =  jQuery( paymentBoxInputsSelector );
        const paymentDetails = {};
        paymentBoxInputs.each( function () {
            const ID = jQuery( this ).attr( 'id' );

            if ( ID ) {
                if ( jQuery.inArray( jQuery( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                    paymentDetails[ ID ] = jQuery( this ).prop( 'checked' );
                } else {
                    paymentDetails[ ID ] = jQuery( this ).val();
                }
            }
        } );

        /**
         * Update Fragments
         *
         * For our elements as well as those from other plugins
         */
        if ( resp.fragments ) {
            jQuery.each( resp.fragments, ( key: any, value ) => {
                // eslint-disable-next-line max-len
                if ( !Object.keys( UpdateCheckoutAction._fragments ).length || UpdateCheckoutAction.cleanseFragments( UpdateCheckoutAction._fragments[ key ] ) !== UpdateCheckoutAction.cleanseFragments( value ) ) {
                    /**
                     * Make sure value is truthy
                     *
                     * Because if it's false (say for Amazon Pay) we don't want to replace anything
                     */
                    if ( typeof value === 'string' ) {
                        jQuery( key ).replaceWith( value );
                    }
                }
            } );

            UpdateCheckoutAction._fragments = resp.fragments;
        }

        if ( !resp.show_shipping_tab ) {
            jQuery( 'body' ).addClass( 'cfw-hide-shipping' );

            // In case the current tab gets hidden
            if ( Main.instance.tabService.getCurrentTab().is( ':hidden' ) ) {
                TabService.go( Main.instance.tabService.getCurrentTab().prev().attr( 'id' ) );
            }
        } else {
            jQuery( 'body' ).removeClass( 'cfw-hide-shipping' );
        }

        jQuery( '.cfw-continue-to-payment-btn' ).not( '.cfw-smartystreets-button' ).toggle( resp.has_valid_shipping_method );

        /**
         * Fill in the payment details if possible without overwriting data if set.
         */
        if ( !jQuery.isEmptyObject( paymentDetails ) ) {
            jQuery( paymentBoxInputsSelector ).each( function () {
                const ID = jQuery( this ).attr( 'id' );
                const val = jQuery( this ).val();

                if ( ID ) {
                    if ( jQuery.inArray( jQuery( this ).attr( 'type' ), [ 'checkbox', 'radio' ] ) !== -1 ) {
                        jQuery( this ).prop( 'checked', paymentDetails[ ID ] ).trigger( 'change' );
                    } else if ( val !== null && val.toString().length === 0 ) {
                        jQuery( this ).val( paymentDetails[ ID ] ).trigger( 'change' );
                    }
                }
            } );
        }

        const alerts = [];

        if ( resp.notices.success ) {
            Object.keys( resp.notices.success ).forEach( ( key: any ) => {
                alerts.push( new Alert( 'success', resp.notices.success[ key ], 'cfw-alert-success' ) );
            } );
        }

        if ( resp.notices.notice ) {
            Object.keys( resp.notices.notice ).forEach( ( key: any ) => {
                alerts.push( new Alert( 'notice', resp.notices.notice[ key ], 'cfw-alert-info' ) );
            } );
        }

        if ( resp.notices.error ) {
            Object.keys( resp.notices.error ).forEach( ( key: any ) => {
                alerts.push( new Alert( 'error', resp.notices.error[ key ], 'cfw-alert-error' ) );
            } );
        }

        if ( !Main.instance.preserveAlerts ) {
            Alert.removeAlerts( DataService.getElement( 'alertContainerId' ) );
        }

        Main.instance.preserveAlerts = false;

        alerts.forEach( ( alert ) => { alert.addAlert(); } );

        /**
         * Unblock UI
         */
        this.unblockUI();

        /**
         * Init selected gateway again
         *
         * Matches WooCommerce core checkout.js
         */
        Main.instance.paymentGatewaysService.initSelectedPaymentGateway();

        jQuery( document.body ).trigger( 'cfw_pre_updated_checkout', [ resp ] );
        LoggingService.logEvent( 'Fired cfw_pre_updated_checkout event.' );

        UpdateCheckoutService.triggerUpdatedCheckout( resp );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        /**
         * Unblock UI
         */
        this.unblockUI();

        // eslint-disable-next-line no-console
        console.log( `Update Checkout Error: ${errorThrown} (${textStatus})` );
    }

    /**
     * Cleanses our beautiful fragments of evil dirty bad stuff
     *
     * @param value
     * @returns {string}
     */
    static cleanseFragments( value: string ) {
        if ( typeof value !== 'string' ) {
            return value;
        }

        return value.replace( /checked='checked' data-order_button_text/g, 'data-order_button_text' )
            .replace( /reveal-content" style="display:none;">/g, 'reveal-content">' )
            .replace( /cfw-radio-reveal-li cfw-active">/g, 'cfw-radio-reveal-li">' )
            .replace( /cfw-radio-reveal-li ">/g, 'cfw-radio-reveal-li">' )
            .replace( /cfw-radio-reveal-content" >/g, 'cfw-radio-reveal-content">' );
    }

    /**
     * @returns {any}
     */
    static get underlyingRequest(): any {
        return this._underlyingRequest;
    }

    /**
     * @param value
     */
    static set underlyingRequest( value: any ) {
        this._underlyingRequest = value;
    }

    static get blockUISelector(): string {
        return this._blockUISelector;
    }

    static set blockUISelector( value: string ) {
        this._blockUISelector = value;
    }
}

export default UpdateCheckoutAction;
