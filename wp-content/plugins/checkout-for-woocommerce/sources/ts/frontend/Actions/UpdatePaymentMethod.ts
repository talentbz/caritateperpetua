import Action                          from './Action';

/**
 *
 */
class UpdatePaymentMethod extends Action {
    /**
     *
     * @param payment_method
     */
    constructor( payment_method: string ) {
        const data = {
            payment_method,
        };

        super( 'update_payment_method', data );
    }

    /**
     *
     * @param resp
     */
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            resp = JSON.parse( resp );
        }
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        // eslint-disable-next-line no-console
        console.log( `Update Payment Method Error: ${errorThrown} (${textStatus})` );
    }
}

export default UpdatePaymentMethod;
