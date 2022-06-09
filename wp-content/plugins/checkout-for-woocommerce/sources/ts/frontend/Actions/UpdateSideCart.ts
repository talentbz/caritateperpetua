import DataService             from '../Services/DataService';
import LoggingService          from '../Services/LoggingService';
import Action                  from './Action';

class UpdateSideCart extends Action {
    protected blockedElements: JQuery;

    /**
     *
     * @param cartData
     * @param blockedElements
     */
    constructor( cartData: string, blockedElements: JQuery ) {
        const data = {
            security: DataService.getCheckoutParam( 'update_side_cart_nonce' ),
            cart_data: cartData,
        };

        super( 'update_side_cart', data );

        this.blockedElements = blockedElements;
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

        if ( resp.result ) {
            jQuery( document.body ).trigger( 'wc_fragment_refresh' );
        }

        this.blockedElements.unblock();
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        LoggingService.logError( `An error occurred during updating side cart. Error: ${errorThrown} (${textStatus})` );
    }
}

export default UpdateSideCart;
