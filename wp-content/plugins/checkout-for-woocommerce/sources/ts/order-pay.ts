import 'core-js/features/object/assign';
import 'ts-polyfill';
import { cfwDomReady, cfwDefineScrollToNotices } from './_functions';
import Accordion                                 from './frontend/Components/Accordion';
import TermsAndConditions                        from './frontend/Components/TermsAndConditions';
import AlertService                              from './frontend/Services/AlertService';
import DataService                               from './frontend/Services/DataService';
import LoggingService                            from './frontend/Services/LoggingService';
import PaymentGatewaysService                    from './frontend/Services/PaymentGatewaysService';
import UpdateCheckoutService                     from './frontend/Services/UpdateCheckoutService';

// eslint-disable-next-line import/prefer-default-export
class OrderPay {
    constructor() {
        cfwDomReady( () => {
            /**
             * Services
             */
            // Init runtime params
            DataService.initRunTimeParams();

            new PaymentGatewaysService();

            // Alert Service
            new AlertService( DataService.getElement( 'alertContainerId' ) );

            /**
             * Components
             */
            // Accordion Component
            new Accordion();

            jQuery( '#cfw-mobile-cart-header' ).on( 'click', ( e ) => {
                e.preventDefault();
                jQuery( '#cfw-cart-summary-content' ).slideToggle( 300 );
                jQuery( '#cfw-expand-cart' ).toggleClass( 'active' );
            } );

            // Load Terms and Conditions Component
            new TermsAndConditions();

            // Payment Gateway Service
            new PaymentGatewaysService();

            // Trigger updated checkout
            UpdateCheckoutService.triggerUpdatedCheckout();

            cfwDefineScrollToNotices();

            // Init checkout ( WooCommerce native event )
            jQuery( document.body ).trigger( 'init_checkout' );
            LoggingService.logEvent( 'Fired init_checkout event.' );
        } );
    }
}

new OrderPay();
