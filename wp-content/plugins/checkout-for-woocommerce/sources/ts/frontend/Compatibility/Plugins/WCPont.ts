import Alert         from '../../Components/Alert';
import Main          from '../../Main';
import Compatibility from '../Compatibility';

class WCPont extends Compatibility {
    constructor() {
        super( 'WCPont' );
    }

    load(): void {
        const easyTabsWrap: any = Main.instance.tabService.tabContainer;

        easyTabsWrap.bind( 'easytabs:before', ( event ) => {
            const selected_shipping_method = jQuery( '[name="shipping_method[0]"]:checked' ).val().toString();

            if ( jQuery( '[name="wc_selected_pont"]' ).val() == '' && selected_shipping_method.indexOf( 'wc_pont_' ) >= 0 ) {
                // Prevent removing alert on next update checkout
                Main.instance.preserveAlerts = true;

                const alert: Alert = new Alert(  'error', 'Nem választottál átvevőhelyet', 'cfw-alert-error' );
                alert.addAlert( true );

                event.stopImmediatePropagation();

                return false;
            }

            return true;
        } );
    }
}

export default WCPont;
