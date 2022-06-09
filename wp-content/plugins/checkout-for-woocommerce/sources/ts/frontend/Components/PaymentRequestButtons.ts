class PaymentRequestButtons {
    private expressButtonContainer: any;

    private expressButtonSeparator: any;

    constructor() {
        this.expressButtonContainer = jQuery( '#cfw-payment-request-buttons' );
        this.expressButtonSeparator = jQuery( '#payment-info-separator-wrap .pay-button-separator' );

        jQuery( window ).on( 'load', () => {
            // If we only have an h2 we dont' have any buttons
            // If separator is already hidden then respect that
            if ( this.buttonCount() === 0 || this.expressButtonSeparator.css( 'display' ) === 'none' ) {
                this.expressButtonContainer.hide();
                this.expressButtonSeparator.hide();
            }

            // Ladder of timings to try to handle even the slowest sites
            // TODO: We should be able to find a better way to do this
            const timings = [ 750, 1500, 3000, 6000 ];
            timings.forEach( ( time ) => {
                ( <any>window ).setTimeout( this.maybeShowExpressButtons.bind( this ), time );
            } );
        } );
    }

    maybeShowExpressButtons(): void {
        if ( this.buttonCount() > 0 ) {
            this.expressButtonContainer.show();
            // this.expressButtonSeparator.show();
        }
    }

    buttonCount(): number {
        const count = this.expressButtonContainer.children().not( 'h2, .blockUI' ).length;
        const stripeButtonHtml = this.expressButtonContainer.find( '#wc-stripe-payment-request-wrapper #wc-stripe-payment-request-button, #wcpay-payment-request-wrapper' ).html();

        let hasEmptyStripeButton = false;

        if ( stripeButtonHtml ) {
            hasEmptyStripeButton = stripeButtonHtml.toString().includes( 'A Stripe Element will be inserted here' ) || stripeButtonHtml.toString().trim().length === 0;
        }

        if ( count === 1 && hasEmptyStripeButton ) {
            return 0;
        }

        return count;
    }
}

export default PaymentRequestButtons;
