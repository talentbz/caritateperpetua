import Alert          from '../Components/Alert';
import LoggingService from './LoggingService';
import TabService     from './TabService';

class AlertService {
    private errorObserver: MutationObserver;

    private alertContainer: any;

    constructor( alertContainer: any ) {
        this.alertContainer = alertContainer;
        this.setMutationObserverWatcher();
    }

    setMutationObserverWatcher(): void {
        jQuery( document.body ).trigger( 'cfw-before-alert-service-mutation-observer-init' );
        LoggingService.logEvent( 'Fired cfw-before-alert-service-mutation-observer-init event.' );

        if ( this.errorObserver ) {
            return;
        }

        // We use form.checkout here instead of form#checkout because one weird gateway changes the form ID :-(
        const targetNode = jQuery( 'form.checkout, form#order_review' ).get( 0 );

        const config = { childList: true, characterData: true, subtree: true };

        this.errorObserver = new MutationObserver( ( mutationsList ) => this.errorMutationListener( mutationsList ) );

        this.errorObserver.observe( targetNode, config );
    }

    errorMutationListener( mutationsList ): void {
        if ( !jQuery( `#${TabService.paymentMethodTabId}:visible, #${TabService.orderReviewTabId}:visible, .context-order-pay` ).length ) {
            return;
        }

        mutationsList.forEach( ( { addedNodes } ) => {
            let $errorNode: any = null;

            Array.from( addedNodes ).forEach( ( node ) => {
                const $node: any = jQuery( node );

                if ( !$node.hasClass( 'woocommerce-error' ) && !$node.hasClass( 'woocommerce-NoticeGroup-checkout' ) ) {
                    return;
                }

                jQuery( document.body ).trigger( 'cfw-remove-overlay' );
                LoggingService.logEvent( 'Fired cfw-remove-overlay event.' );
                $errorNode = $node;
                $errorNode.attr( 'class', '' );
            } );

            if ( $errorNode ) {
                if ( $errorNode.find( 'li' ).length > 0 ) {
                    jQuery.each( $errorNode.find( 'li' ), ( i, el ) => {
                        const alert: Alert = new Alert( 'error', jQuery( el ).text().trim(), 'cfw-alert-error' );
                        alert.addAlert();
                    } );
                } else {
                    const alert: Alert = new Alert( 'error', $errorNode.text().trim(), 'cfw-alert-error' );
                    alert.addAlert();
                }

                $errorNode.remove();
            }
        } );
    }
}

export default AlertService;
