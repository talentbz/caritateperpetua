import Alert                   from '../Components/Alert';
import Action                  from './Action';

class LostPasswordAction extends Action {
    /**
     *
     * @param fields
     */
    constructor( fields: any ) {
        const data = {
            fields,
        };

        super( 'cfw_lost_password', data );
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

        jQuery( '#cfw_lost_password_form' ).replaceWith( resp.message );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        const alert: Alert = new Alert( 'error', `An error occurred during login. Error: ${errorThrown} (${textStatus})`, 'cfw-alert-error' );
        alert.addAlert();
    }
}

export default LostPasswordAction;
