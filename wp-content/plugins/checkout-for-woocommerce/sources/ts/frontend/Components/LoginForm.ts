import AccountExistsAction   from '../Actions/AccountExistsAction';
import LoginAction           from '../Actions/LoginAction';
import DataService           from '../Services/DataService';

const debounce = require( 'debounce' );

class LoginForm {
    private readonly _debounceAccountExists;

    constructor() {
        this._debounceAccountExists = debounce( this.triggerAccountExistsCheck, 200 );
        this.setListeners();
    }

    setListeners() {
        if ( DataService.getSetting( 'enable_account_exists_check' ) ) {
            this.setAccountCheckListener();
        }

        this.setLogInListener();
        this.setDefaultLoginFormListener();
        this.setAnimationListeners();
        this.setCreateAccountCheckboxListener();
    }

    /**
     *
     */
    setAccountCheckListener() {
        const emailInput: any = jQuery( '#billing_email' );

        if ( emailInput ) {
            // Add check to keyup event
            emailInput.on( 'keyup change', this._debounceAccountExists );

            // Handles page onload use case
            this.triggerAccountExistsCheck();
        }
    }

    /**
     *
     */
    setLogInListener() {
        const emailInput: any = jQuery( '#billing_email' );

        if ( emailInput ) {
            const passwordInput: any = jQuery( '#cfw-password' );
            const loginBtn: any = jQuery( '#cfw-login-btn' );
            const otherFields = {};

            // Fire the login action on click
            loginBtn.on( 'click', () => {
                jQuery( '#cfw-login-details .cfw-input-container :input' ).not( '#billing_email, #cfw-password, #cfw-login-btn, #createaccount' ).each( function ( index ) {
                    otherFields[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
                } );

                new LoginAction( emailInput.val(), passwordInput.val(), otherFields ).load();
            } );
        }
    }

    setDefaultLoginFormListener() {
        jQuery( document.body ).on( 'click', 'a.showlogin', () => {
            jQuery( 'form.login, form.woocommerce-form--login' ).slideToggle();

            return false;
        } );
    }

    /**
     * Sets up animation listeners
     */
    setAnimationListeners(): void {
        const createAccountCheckbox = jQuery( '#createaccount' );

        jQuery( '#cfw-ci-login' ).on( 'click', () => {
            const passwordInput: any = jQuery( '#cfw-password' );
            const loginButton: any = jQuery( '#cfw-login-btn' );

            jQuery( '#cfw-login-slide' ).slideToggle( 300, () => {
                passwordInput.prop( 'disabled', !passwordInput.is( ':visible' ) );
                loginButton.prop( 'disabled', !loginButton.is( ':visible' ) );
            } ).toggleClass( 'stay-open' );

            if ( createAccountCheckbox.is( ':checked:enabled' ) ) {
                createAccountCheckbox.prop( 'checked', false ).trigger( 'change' );
            }
        } );
    }

    setCreateAccountCheckboxListener(): void {
        if ( !DataService.getSetting( 'registration_generate_password' ) ) {
            const createAccountCheckbox = jQuery( '#createaccount' );
            const accountPasswordSlide = jQuery( '#cfw-account-password-slide' );
            const accountPassword = jQuery( '#account_password' );

            createAccountCheckbox.on( 'change', function () {
                if ( jQuery( this ).is( ':checked' ) ) {
                    accountPasswordSlide.slideDown( 300 );
                    accountPassword.attr( 'data-parsley-required', 'true' );
                    accountPassword.prop( 'disabled', false );
                    jQuery( '#cfw-login-slide' ).slideUp( 300 );
                } else {
                    accountPasswordSlide.slideUp( 300 );
                    accountPassword.attr( 'data-parsley-required', 'false' );
                    accountPassword.prop( 'disabled', true );
                }
            } ).trigger( 'change' );
        }
    }

    triggerAccountExistsCheck() {
        const emailInput = jQuery( '#billing_email' );
        const loginSlide = jQuery( '#cfw-login-slide' );
        const emailValue: string = emailInput.length ? emailInput.val().toString() : '';

        if ( emailValue.length ) {
            new AccountExistsAction( emailValue ).load();
        } else if ( !loginSlide.hasClass( 'stay-open' ) ) {
            loginSlide.slideUp( 300 );
        }
    }
}

export default LoginForm;
