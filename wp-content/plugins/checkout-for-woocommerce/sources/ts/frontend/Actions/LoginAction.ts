import Alert                   from '../Components/Alert';
import Action                  from './Action';

/**
 *
 */
class LoginAction extends Action {
    /**
     *
     * @param email
     * @param password
     * @param otherFields
     */
    constructor( email: string, password: string, otherFields: object ) {
        const data = {
            email,
            password,
            ...otherFields,
        };

        super( 'login', data );
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

        if ( resp.logged_in ) {
            ( <any>window ).location.reload();
        } else {
            const alert: Alert = new Alert( 'error', resp.message, 'cfw-alert-error' );
            alert.addAlert();
        }
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

export default LoginAction;
