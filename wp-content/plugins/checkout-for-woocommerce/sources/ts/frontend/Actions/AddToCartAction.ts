import DataService             from '../Services/DataService';
import LoggingService          from '../Services/LoggingService';
import Action                  from './Action';

class AddToCartAction extends Action {
    protected button: JQuery;

    /**
     *
     * @param productData
     * @param button
     */
    constructor( productData: string, button: JQuery ) {
        super( 'cfw_add_to_cart', productData );

        this.button = button;
    }

    /**
     * @param resp
     */
    // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
    public response( resp: any ): void {
        if ( typeof resp !== 'object' ) {
            // eslint-disable-next-line no-param-reassign
            resp = JSON.parse( resp );
        }

        if ( resp.result ) {
            jQuery( document.body ).trigger( 'wc_fragment_refresh' );
            jQuery( document.body ).trigger( 'added_to_cart', [ resp.fragments, resp.cart_hash, this.button ] );
        } else {
            window.location.reload();
        }
    }

    public complete(): void {
        this.button.removeClass( 'loading' ).addClass( 'added' );
    }

    /**
     * @param xhr
     * @param textStatus
     * @param errorThrown
     */
    public error( xhr: any, textStatus: string, errorThrown: string ): void {
        LoggingService.logError( `An error occurred during adding to cart. Error: ${errorThrown} (${textStatus})` );
    }
}

export default AddToCartAction;
