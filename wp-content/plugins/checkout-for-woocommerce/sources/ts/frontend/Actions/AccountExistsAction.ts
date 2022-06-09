import DataService                     from '../Services/DataService';
import Action                          from './Action';

/**
 * Ajax does the account exist action. Takes the information from email box and fires of a request to see if the account
 * exists
 */
class AccountExistsAction extends Action {
    /**
     * @type {boolean}
     * @private
     */
    private static _checkBox: boolean = true;

    /**
     * @param email
     */
    constructor( email: string ) {
        // Call parent
        super( 'account_exists', {
            email,
        } );
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

        const loginSlide: any = jQuery( '#cfw-login-slide' );
        const createAccount =  jQuery( '#createaccount.cfw-create-account-checkbox' ); // only target our specific checkbox in our stuff
        const registerUserCheckbox: any = ( createAccount.length > 0 ) ? createAccount : null;
        const registerContainer: any = jQuery( '#cfw-login-details .cfw-check-input' );
        const accountPasswordSlide = jQuery( '#cfw-account-password-slide' );
        const passwordInput: any = jQuery( '#cfw-password' );
        const loginButton: any = jQuery( '#cfw-login-btn' );

        // Cleanup any login required alerts
        jQuery( '.cfw-login-required-error' ).remove();

        // If account exists slide down the password field, uncheck the register box, and hide the container for the checkbox
        DataService.setRuntimeParameter( 'runtime_email_matched_user', resp.account_exists );

        if ( resp.account_exists ) {
            loginSlide.slideDown( 300 );

            if ( registerUserCheckbox && registerUserCheckbox.is( ':checkbox' ) ) {
                registerUserCheckbox.prop( 'checked', false );
                registerUserCheckbox.trigger( 'change' );
                registerUserCheckbox.prop( 'disabled', true );
            }

            registerContainer.css( 'display', 'none' );
            passwordInput.prop( 'disabled', false );
            loginButton.prop( 'disabled', false );

            AccountExistsAction.checkBox = true;

            if ( !DataService.getSetting( 'registration_generate_password' ) && accountPasswordSlide.is( ':visible' ) ) {
                accountPasswordSlide.slideUp( 300 );
            }
        } else { // If account does not exist, reverse
            if ( !loginSlide.hasClass( 'stay-open' ) ) {
                loginSlide.slideUp( 300 );
            }

            passwordInput.prop( 'disabled', true );
            loginButton.prop( 'disabled', true );

            registerContainer.css( 'display', 'flex' );

            if ( AccountExistsAction.checkBox ) {
                if ( registerUserCheckbox && registerUserCheckbox.is( ':checkbox' ) ) {
                    if ( DataService.getSetting( 'check_create_account_by_default' ) ) {
                        registerUserCheckbox.prop( 'checked', true );
                    }

                    registerUserCheckbox.prop( 'disabled', false );
                    registerUserCheckbox.trigger( 'change' );
                }

                AccountExistsAction.checkBox = false;
            }

            // eslint-disable-next-line max-len
            if ( !DataService.getSetting( 'registration_generate_password' ) && ( ( registerUserCheckbox && registerUserCheckbox.is( ':checked' ) ) || DataService.getSetting( 'is_registration_required' ) ) ) {
                accountPasswordSlide.slideDown( 300 );
            }
        }
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        // eslint-disable-next-line no-console
        console.log( `Account Exists Error: ${errorThrown} (${textStatus})` );
    }

    /**
     * @returns {boolean}
     */
    static get checkBox(): boolean {
        return AccountExistsAction._checkBox;
    }

    /**
     * @param {boolean} value
     */
    static set checkBox( value: boolean ) {
        AccountExistsAction._checkBox = value;
    }
}

export default AccountExistsAction;
