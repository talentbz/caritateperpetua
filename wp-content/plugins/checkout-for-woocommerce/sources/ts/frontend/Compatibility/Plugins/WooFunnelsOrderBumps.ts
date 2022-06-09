import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WooFunnelsOrderBumps extends Compatibility {
    constructor() {
        super( 'WooFunnelsOrderBumps' );
    }

    load(): void {
        jQuery( document.body ).on( 'wfob_bump_trigger', () => {
            Main.instance.updateCheckoutService.queueUpdateCheckout();
        } );
    }
}

export default WooFunnelsOrderBumps;
