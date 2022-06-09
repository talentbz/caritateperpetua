import Alert  from '../Components/Alert';
import Main                  from '../Main';
import DataService           from './DataService';
import TabService            from './TabService';

/**
 * Validation Sections Enum
 */
export enum EValidationSections {
    SHIPPING,
    BILLING,
    ACCOUNT
}

/**
 *
 */
class ValidationService {
    /**
     * @type {EValidationSections}
     * @private
     */
    private static _currentlyValidating: EValidationSections;

    /**
     */
    constructor() {
        ( <any>window ).cfw_suppress_js_field_validation = false;

        this.validateSectionsBeforeSwitch();
        this.validateOnFormSubmit();

        ValidationService.validateShippingOnLoadIfNotCustomerTab();
    }

    /**
     * Execute validation checks before each easy tab easy tab switch.
     *
     * @param {any} easyTabsWrap
     */
    validateSectionsBeforeSwitch(): void {
        Main.instance.tabService.tabContainer.bind( 'easytabs:before', ( event, clicked, target ) => {
            const currentTab = Main.instance.tabService.getCurrentTab();
            const destinationTab = jQuery( target );
            const { checkoutForm } = DataService;

            // Make sure the next tab is after the current one
            if ( currentTab.nextAll( '.cfw-panel' ).filter( `#${destinationTab.attr( 'id' )}` ).length ) {
                /**
                 * Allow custom validation of tabs based on the destination tab
                 */
                const beforeTabSwitchValid = DataService.checkoutForm.triggerHandler( `cfw_validate_before_tab_switch_${destinationTab.attr( 'id' )}` );
                const customFieldOnTabAreValid = checkoutForm.parsley().validate( { group: currentTab.attr( 'id' ).toString() } );

                if ( beforeTabSwitchValid === false || customFieldOnTabAreValid === false ) {
                    return false;
                }

                /**
                 * Validate Customer Information tab
                 */
                if ( currentTab.attr( 'id' ) === TabService.customerInformationTabId ) {
                    let validated: boolean = ValidationService.validateCustomerInformationTab();
                    let loginRequiredError: boolean = false;

                    // Maybe fail validation on account errors
                    // eslint-disable-next-line max-len
                    if ( !DataService.getSetting( 'user_logged_in' ) && DataService.getSetting( 'is_registration_required' ) && DataService.getRuntimeParameter( 'runtime_email_matched_user' ) && DataService.getSetting( 'validate_required_registration' ) ) {
                        loginRequiredError = true;
                        validated = false;
                    }

                    // If a login required error happened, add it here so it happens after the hash jump above
                    if ( loginRequiredError ) {
                        const alert: Alert = new Alert(
                            'error',
                            DataService.getMessage( 'account_already_registered_notice' ),
                            'cfw-alert-error cfw-login-required-error',
                        );

                        alert.addAlert();
                    }

                    if ( !validated ) {
                        event.stopImmediatePropagation();
                    }

                    // Return the validation
                    return validated;
                }

                if ( currentTab.attr( 'id' ) === TabService.shippingMethodTabId ) {
                    let validated = true;

                    currentTab.find( '.validate-required:visible' ).each( ( i, el ) => {
                        const fieldContainer = jQuery( el );
                        const field = fieldContainer.find( ':input' ).not( '[data-parsley-group]' );

                        if ( field.val() !== '' ) {
                            return;
                        }

                        let label = fieldContainer.find( 'label' ).text();

                        // If field doesn't have label, look for TH
                        if ( !label ) {
                            label = fieldContainer.closest( 'td' ).siblings( 'th' ).text();
                        }

                        const alert: Alert = new Alert(
                            'error',
                            DataService.getMessage( 'generic_field_validation_error_message' ).replace( '%s', label ),
                            'cfw-alert-error cfw-login-required-error',
                        );

                        alert.addAlert();

                        validated = false;
                    } );

                    return validated;
                }
            }

            // If we are moving forward / backwards, have a shipping easy tab,
            // and are not on the customer tab then allow
            // the tab switch
            return true;
        } );
    }

    validateOnFormSubmit(): void {
        const { checkoutForm } = DataService;

        checkoutForm.on( 'submit', ( e ) => {
            let validated = false;

            let billingAddressValid = true;

            if ( DataService.getSetting( 'needs_shipping_address' ) && checkoutForm.find( 'input[name="bill_to_different_address"]:checked' ).val() !== 'same_as_shipping' ) {
                billingAddressValid = ValidationService.validateSection( EValidationSections.BILLING );
            }

            let customerInformationTabValid = true;

            if ( DataService.getSetting( 'enable_one_page_checkout' ) ) {
                customerInformationTabValid = ValidationService.validateCustomerInformationTab();
            }

            // Hard code this because this is all we should really be validating on submit for custom fields
            // and more importantly, the tab service isn't available for one page checkout
            const customFieldsAreValid = checkoutForm.parsley().validate( { group: 'cfw-payment-method' } );

            validated = billingAddressValid && customerInformationTabValid && customFieldsAreValid;

            if ( !validated ) {
                e.preventDefault();
                e.stopImmediatePropagation(); // prevent bubbling up the DOM *and* prevent other submit handlers from firing, such as completeOrder
            }

            return validated;
        } );
    }

    /**
     *
     * @returns {boolean}
     */
    static validateCustomerInformationTab(): boolean {
        let validated;

        const accountValidated = ValidationService.validateSection( EValidationSections.ACCOUNT );

        if ( !DataService.getSetting( 'needs_shipping_address' ) ) {
            const billingAddressValidated = ValidationService.validateSection( EValidationSections.BILLING );

            validated = accountValidated && billingAddressValidated;
        } else {
            const shippingAddressValidated = ValidationService.validateSection( EValidationSections.SHIPPING );

            validated = accountValidated && shippingAddressValidated;
        }

        return validated;
    }

    /**
     * @param {EValidationSections} section
     * @returns {any}
     */
    static validateSection( section: EValidationSections ): any {
        if ( ( <any>window ).cfw_suppress_js_field_validation ) {
            return true;
        }

        let validated: boolean;
        const { checkoutForm } = DataService;

        ValidationService.currentlyValidating = section;

        // eslint-disable-next-line default-case
        switch ( section ) {
        case EValidationSections.SHIPPING:
            validated = checkoutForm.parsley().validate( { group: 'shipping' } );
            break;
        case EValidationSections.BILLING:
            validated = checkoutForm.parsley().validate( { group: 'billing' } );
            break;
        case EValidationSections.ACCOUNT:
            validated = checkoutForm.parsley().validate( { group: 'account' } );
            break;
        default:
        }

        if ( validated == null ) {
            validated = true;
        }

        return validated;
    }

    /**
     * Handles non ajax cases
     */
    static validateShippingOnLoadIfNotCustomerTab(): void {
        const { hash } = window.location;
        const customerInfoId: string = `#${TabService.customerInformationTabId}`;
        const sectionToValidate: EValidationSections = ( DataService.getSetting( 'needs_shipping_address' ) === true ) ? EValidationSections.SHIPPING : EValidationSections.BILLING;

        if ( hash !== customerInfoId && hash !== '' ) {
            if ( !ValidationService.validateSection( sectionToValidate ) ) {
                TabService.go( TabService.customerInformationTabId );
            }
        }
    }

    /**
     * @return {EValidationSections}
     */
    static get currentlyValidating(): EValidationSections {
        return this._currentlyValidating;
    }

    /**
     * @param {EValidationSections} value
     */
    static set currentlyValidating( value: EValidationSections ) {
        this._currentlyValidating = value;
    }
}

export default ValidationService;
