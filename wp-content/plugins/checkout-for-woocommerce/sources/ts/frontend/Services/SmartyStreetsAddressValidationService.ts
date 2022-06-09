import fastDeepEqual                        from 'fast-deep-equal';
import Main                                 from '../Main';
import DataService                          from './DataService';
import TabService                           from './TabService';

class SmartyStreetsAddressValidationService {
    protected userAddress;

    protected suggestedAddress = {};

    protected userHasAcceptedAddress = false;

    protected modaalTrigger;

    protected tabChangeDestinationID = null;

    constructor() {
        this.setupModaal();
        this.run();
    }

    public isWrongTabContext( target ) {
        const currentTab = Main.instance.tabService.getCurrentTab();
        const destinationTab = jQuery( target );
        const destinationTabIsAfterCurrentTab   = currentTab.nextAll( '.cfw-panel' ).filter( `#${destinationTab.attr( 'id' )}` ).length;
        const currentTabIsInformationTab = currentTab.attr( 'id' ) === TabService.customerInformationTabId;

        return !destinationTabIsAfterCurrentTab || !currentTabIsInformationTab;
    }

    public setupModaal() {
        this.modaalTrigger = jQuery( '.cfw-smartystreets-modal-trigger' );

        this.modaalTrigger.modaal( { width: 600, custom_class: 'checkoutwc' } );
    }

    public getAddress() {
        return {
            address_1: jQuery( '[name="shipping_address_1"]' ).val(),
            address_2: jQuery( '[name="shipping_address_2"]' ).val(),
            city: jQuery( '[name="shipping_city"]' ).val(),
            state: jQuery( '[name="shipping_state"]' ).val(),
            postcode: jQuery( '[name="shipping_postcode"]' ).val(),
            country: jQuery( '[name="shipping_country"]' ).val(),
            company: jQuery( '[name="shipping_company"]' ).val(),
        };
    }

    public handleEasyTabsBefore( event, clicked, target ) {
        if ( this.isWrongTabContext( target ) ) {
            return true;
        }

        this.tabChangeDestinationID = target[ 0 ].id;

        const address = this.getAddress();

        const addressHasNotChanged = fastDeepEqual( this.userAddress, address );

        if ( this.userHasAcceptedAddress && addressHasNotChanged ) { // user confirmed address selected
            return true;
        }

        this.userHasAcceptedAddress = false;

        let data = null;

        const setData = ( val ) => { data = val; };

        const handleError = ( xhr: any, textStatus: string, errorThrown: string ) => {
            // eslint-disable-next-line no-console
            console.log( `SmartyStreets Address Validation Error: ${errorThrown} (${textStatus})` );
        };

        jQuery.ajax( {
            type: 'POST',
            url: DataService.getCheckoutParam( 'wc_ajax_url' ).toString().replace( '%%endpoint%%', 'cfw_smartystreets_address_validation' ),
            data: { address },
            success: setData,
            error: handleError,
            dataType: 'json',
            async: false,
        } );

        const response = data;

        if ( !response.result ) {
            return true;
        }

        jQuery( '.cfw-smartystreets-user-address' ).html( response.original );
        jQuery( '.cfw-smartystreets-suggested-address' ).html( response.address );

        // Set to suggested by default
        jQuery( '.cfw-radio-suggested-address' ).prop( 'checked', true ).trigger( 'change' );

        this.modaalTrigger.modaal( 'open' );

        this.suggestedAddress = response.components;
        this.userAddress = address;

        event.stopImmediatePropagation();
        return false;
    }

    run() {
        /**
         * Tab Change Intercept
         *
         * Only fires when the current tab is the information tab
         * and the destination tab is to the right
         */
        Main.instance.tabService.tabContainer.bind( 'easytabs:before', this.handleEasyTabsBefore.bind( this ) );

        const wraps  = jQuery( '.cfw-smartystreets-option-wrap' );

        const updateButtons = () => {
            wraps.each( ( i, wrap ) => {
                const noCheckedRadio = jQuery( wrap ).find( 'input:radio:checked' ).length === 0;

                jQuery( wrap ).toggleClass( 'cfw-smartystreets-hide-buttons', noCheckedRadio );
            } );
        };

        jQuery( '.cfw-smartystreets-option-wrap input:radio' ).on( 'change', updateButtons );

        updateButtons();

        const closeItUp = ( event ) => {
            this.userHasAcceptedAddress = true;

            this.modaalTrigger.modaal( 'close' );
            Main.instance.tabService.tabContainer.easytabs( 'select', `#${this.tabChangeDestinationID}` );
            this.tabChangeDestinationID = null;
        };

        const handleSuggestedAddressButtonClick = ( event ) => {
            // Replace address with suggested address
            Object.keys( this.suggestedAddress ).forEach( ( key: any ) => {
                jQuery( `[name="shipping_${key}"]` ).val(  this.suggestedAddress[ key ] ).trigger( 'change' );
            } );

            this.userAddress = this.suggestedAddress;

            closeItUp( event );
        };

        jQuery( document.body ).on( 'click', '.cfw-smartystreets-suggested-address-button', handleSuggestedAddressButtonClick );
        jQuery( document.body ).on( 'click', '.cfw-smartystreets-user-address-button', closeItUp );
    }
}

export default SmartyStreetsAddressValidationService;
