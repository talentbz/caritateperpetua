import LostPasswordAction from '../Actions/LostPasswordAction';
import DataService        from '../Services/DataService';

class LostPasswordModal {
    constructor() {
        jQuery( '#cfw_lost_password_trigger' ).modaal( { width: 600 } );

        jQuery( document.body ).on( 'submit', '#cfw_lost_password_form', ( e ) => {
            e.preventDefault();

            new LostPasswordAction( jQuery( e.target ).serialize() ).load();
        } );

        // Pre-fill lost password form input when billing email is changed
        jQuery( document.body ).on( 'change', '#billing_email', function () {
            jQuery( '#cfw_lost_password_form #user_login' ).val( jQuery( this ).val() );
        } );
    }
}

export default LostPasswordModal;
