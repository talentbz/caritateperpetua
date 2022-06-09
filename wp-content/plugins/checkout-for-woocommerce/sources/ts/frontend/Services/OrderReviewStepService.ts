import Main                                       from '../Main';
import DataService                                from './DataService';
import TabService                                 from './TabService';
import ValidationService, { EValidationSections } from './ValidationService';

class OrderReviewStepService {
    static handleSubmit: any;

    constructor() {
        DataService.checkoutForm.on( `cfw_validate_before_tab_switch_${TabService.orderReviewTabId}`, OrderReviewStepService.validateBeforeReviewStep );
        jQuery( document.body ).on( 'cfw-after-tab-change', OrderReviewStepService.afterTabSwitch );
    }

    static validateBeforeReviewStep(): boolean {
        return ValidationService.validateCustomerInformationTab() && OrderReviewStepService.validatePaymentTab();
    }

    static validatePaymentTab(): boolean {
        OrderReviewStepService.handleSubmit = true;

        // Only validate billing address if billing address is on payment tab
        // And radio toggle is set to use different address
        if (
            DataService.getSetting( 'needs_shipping_address' ) && DataService.checkoutForm.find( 'input[name="bill_to_different_address"]:checked' ).val() !== 'same_as_shipping'
            && !ValidationService.validateSection( EValidationSections.BILLING )
        ) {
            return false;
        }

        const form = DataService.checkoutForm;

        // Some gateways do client validation of terms - stop that by temporarily checking the terms box
        jQuery( '[name="terms"]' ).not( ':checked' ).prop( 'checked', true ).data( 'cfw-overridden', true );

        const paymentMethodId = form.find( 'input[name="payment_method"]:checked' ).val();

        // Handle gateways that submit the form on checkout_place_order
        // Processing prevents the order from submitting
        // For gateways that don't resubmit, this class is only there during
        // validation and then is immediately removed after validation passes.
        // Also: Excludes this action for authorize.net
        if ( paymentMethodId !== 'authorize_net_cim_credit_card' ) {
            form.addClass( 'processing' );
        }

        // IF a gateway does submit, go to the next tab and unblock it
        // If gateway does NOT submit, this code is inert - the tab will
        // already be the review tab and the form will already be unblocked
        form.one( 'submit', () => {
            if ( !OrderReviewStepService.handleSubmit ) {
                return;
            }

            TabService.go( TabService.orderReviewTabId );
            form.unblock();
        } );

        // Fire checkout_place_order to tell gateways to validate
        if ( form.triggerHandler( 'checkout_place_order' ) === false ) {
            return false;
        }

        return form.triggerHandler( `checkout_place_order_${paymentMethodId}` ) !== false;
    }

    static afterTabSwitch() {
        OrderReviewStepService.handleSubmit = false;

        // Make sure we're on the review tab
        if ( Main.instance.tabService.getCurrentTab().attr( 'id' ) !== TabService.orderReviewTabId ) {
            return;
        }

        // Remove the processing class so that the next submit works
        DataService.checkoutForm.removeClass( 'processing' );

        // Revert the terms checkbox if it was changed by us
        const termsCheckbox = jQuery( '[name="terms"]' );

        if ( termsCheckbox.data( 'cfw-overridden' ) ) {
            termsCheckbox.prop( 'checked', false ).data( 'cfw-overridden', null );
        }
    }
}

export default OrderReviewStepService;
