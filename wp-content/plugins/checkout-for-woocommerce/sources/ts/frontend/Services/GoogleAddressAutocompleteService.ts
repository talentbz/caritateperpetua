import FieldValidationRefresher       from '../Interfaces/FieldValidationRefresher';
import DataService                    from './DataService';
import LoggingService                 from './LoggingService';

/* global google */

class GoogleAddressAutocompleteService {
    private addressFormats = {
        AL: 'street_name house_number',
        AO: 'street_name house_number',
        AR: 'street_name house_number',
        AT: 'street_name house_number',
        BA: 'street_name house_number',
        BE: 'street_name house_number',
        BG: 'street_name house_number',
        BI: 'street_name house_number',
        BN: 'house_number, street_name',
        BO: 'street_name house_number',
        BQ: 'street_name house_number',
        BR: 'street_name, house_number',
        BW: 'street_name house_number',
        BY: 'street_name house_number',
        CF: 'street_name house_number',
        CH: 'street_name house_number',
        CL: 'street_name house_number',
        CM: 'street_name house_number',
        CO: 'street_name house_number',
        CW: 'street_name house_number',
        CZ: 'street_name house_number',
        DE: 'street_name house_number',
        DK: 'street_name house_number',
        DO: 'street_name house_number',
        EC: 'street_name house_number',
        EE: 'street_name house_number',
        EH: 'street_name house_number',
        ER: 'street_name house_number',
        ES: 'street_name, house_number',
        ET: 'street_name house_number',
        FI: 'street_name house_number',
        FO: 'street_name house_number',
        GD: 'street_name house_number',
        GL: 'street_name house_number',
        GN: 'street_name house_number',
        GQ: 'street_name house_number',
        GR: 'street_name house_number',
        GT: 'street_name house_number',
        GW: 'street_name house_number',
        HN: 'street_name house_number',
        HR: 'street_name house_number',
        HT: 'street_name house_number',
        HU: 'street_name house_number',
        IR: 'street_name house_number',
        IS: 'street_name house_number',
        IT: 'street_name house_number',
        JO: 'street_name house_number',
        KG: 'street_name house_number',
        KI: 'street_name house_number',
        KM: 'street_name house_number',
        KW: 'street_name house_number',
        KZ: 'street_name house_number',
        LC: 'street_name house_number',
        LI: 'street_name house_number',
        LR: 'street_name house_number',
        LT: 'street_name house_number',
        LV: 'street_name house_number',
        LY: 'street_name house_number',
        MD: 'street_name house_number',
        ME: 'street_name house_number',
        MK: 'street_name house_number',
        ML: 'street_name house_number',
        MO: 'street_name house_number',
        MX: 'street_name house_number',
        MY: 'street_name house_number',
        MZ: 'street_name, house_number',
        NL: 'street_name house_number',
        NO: 'street_name house_number',
        PA: 'street_name house_number',
        PE: 'street_name house_number',
        PK: 'house_number - street_name',
        PL: 'street_name house_number',
        PT: 'street_name house_number',
        PY: 'street_name house_number',
        QA: 'street_name house_number',
        RO: 'street_name house_number',
        RS: 'street_name house_number',
        RU: 'street_name house_number',
        SB: 'street_name house_number',
        SD: 'street_name house_number',
        SE: 'street_name house_number',
        SI: 'street_name house_number',
        SJ: 'street_name house_number',
        SK: 'street_name house_number',
        SM: 'street_name house_number',
        SO: 'street_name house_number',
        SR: 'street_name house_number',
        SS: 'street_name house_number',
        ST: 'street_name house_number',
        SV: 'street_name house_number',
        SX: 'street_name house_number',
        SY: 'street_name house_number',
        TD: 'street_name house_number',
        TJ: 'street_name house_number',
        TR: 'street_name house_number',
        TZ: 'street_name house_number',
        UA: 'street_name house_number',
        UY: 'street_name house_number',
        VA: 'street_name house_number',
        VU: 'street_name house_number',
        WS: 'street_name house_number',
    };

    private _fieldValidator: FieldValidationRefresher;

    constructor( fieldValidator: FieldValidationRefresher ) {
        if ( !DataService.getSetting( 'enable_address_autocomplete' ) ) {
            return;
        }

        this._fieldValidator = fieldValidator;

        if ( typeof google === 'undefined' || typeof google.maps === 'undefined' || typeof google.maps.places === 'undefined' || typeof google.maps.places.Autocomplete === 'undefined' ) {
            LoggingService.logError( 'CheckoutWC: Could not load Google Maps object.' );
            return;
        }

        if ( DataService.getSetting( 'needs_shipping_address' ) === true ) {
            this.initAutocomplete( 'shipping_', DataService.getSetting( 'address_autocomplete_shipping_countries' ) );
        }

        this.initAutocomplete( 'billing_', DataService.getSetting( 'address_autocomplete_billing_countries' )  );
    }

    initAutocomplete( prefix: string, countryRestrictions?: false|string|Array<string> ): void {
        const field = <HTMLInputElement>document.getElementById( `${prefix}address_1` );
        field.autocomplete = 'new-password';

        const options = {
            fields: [ 'address_component' ],
            types: [ DataService.getSetting( 'google_address_autocomplete_type' ) ],
        };

        const autocomplete =  new google.maps.places.Autocomplete( field, options );

        if ( countryRestrictions ) {
            autocomplete.setComponentRestrictions( { country: countryRestrictions } );
        }

        autocomplete.addListener( 'place_changed', () => this.fillAddress( prefix, autocomplete, field ) );
    }

    getComponentValue( type: string, components: google.maps.GeocoderAddressComponent[] ): string {
        const component = components.find( ( { types } ) => types[ 0 ] === type );

        return component ? component.short_name : '';
    }

    fillAddress( prefix: string, autocomplete: google.maps.places.Autocomplete, { value: formattedAddress }: HTMLInputElement ): void {
        const { address_components: components } = autocomplete.getPlace();

        if ( !components ) {
            return;
        }

        const country          = this.getComponentValue( 'country', components );
        const cityGetter       = ( [ 'NZ', 'NL', 'CA' ].includes( country ) ? this.getCityIgnoringSublocalityLevel1 : this.getCity ).bind( this );
        const stateGetter      = ( country === 'ES' ? this.getStateSpain : this.getState ).bind( this );
        const address1Getter   = this.getAddress1.bind( this );
        const address2Getter   = country === 'NZ' ? this.getAddress2NZ.bind( this ) : this.getAddress2.bind( this );
        const postalCodeGetter = ( comps ) => this.getComponentValue( 'postal_code', comps );

        this.updateField( `#${prefix}address_2`, address2Getter( components ) );
        this.updateField( `#${prefix}address_1`, address1Getter( country, formattedAddress, components ) );
        this.updateField( `#${prefix}city`, cityGetter( components ) );
        this.queueStateUpdate( prefix, stateGetter( components ) );
        this.updateField( `#${prefix}postcode`, postalCodeGetter( components ) );
        this.updateField( `#${prefix}country`, country );
    }

    getAddress2(  components: google.maps.GeocoderAddressComponent[] ): string {
        return '';
    }

    getAddress2NZ( components: google.maps.GeocoderAddressComponent[] ): string {
        return this.getComponentValue( 'sublocality_level_1', components );
    }

    getState( components: google.maps.GeocoderAddressComponent[] ): string {
        return this.getComponentValue( 'administrative_area_level_1', components );
    }

    getStateSpain( components: google.maps.GeocoderAddressComponent[] ): string {
        return this.getComponentValue( 'administrative_area_level_2', components );
    }

    updateField( id: string, value: string ): void {
        this._fieldValidator.refreshField( jQuery( id ).val( value ).trigger( 'change', [ 'cfw_store' ] ).get( 0 ) );
    }

    queueStateUpdate( prefix: string, state: string ): void {
        jQuery( document.body ).one( 'country_to_state_changed', () => {
            setTimeout( () => {
                const stateField = jQuery( `#${prefix}state` );

                const noFuzzySearchNeeded = !stateField.is( 'select' ) || stateField.find( `option[value="${state}"]` ).length;
                const stateValue          = noFuzzySearchNeeded ? state : stateField.find( `option:contains(${state})` ).val();

                stateField.val( stateValue );

                stateField.trigger( 'change', [ 'cfw_store' ] );
                this._fieldValidator.refreshField( stateField.get( 0 ) );
            } );
        } );
    }

    getAddress1( country: string, fullAddressResult: string, components: google.maps.GeocoderAddressComponent[] ): string {
        const route = this.getComponentValue( 'route', components );

        // Process <subpremise>/<street number> <route> formats
        // get all the user entered values before a match with the street name;
        // group #1 = unit number, group #2 = street number
        // eslint-disable-next-line no-useless-escape
        const results = RegExp( '^(.*?)\/(.*?) ' ).exec( fullAddressResult );

        // If this is an array, format was unit/house number format
        if ( Array.isArray( results ) ) {
            return `${results[ 1 ]}/${results[ 2 ]} ${route}`;
        }

        const houseNumber    = this.getHouseNumber( fullAddressResult, components );
        const template       = this.addressFormats[ country ] ?? 'house_number street_name';

        return template.replace( 'house_number', houseNumber ).replace( 'street_name', route  ).replace( 'undefined', '' );
    }

    getHouseNumber( fullAddressResult: string, components: google.maps.GeocoderAddressComponent[] ): string {
        const streetNumber   = this.getComponentValue( 'street_number', components );
        const premise        = this.getComponentValue( 'premise', components );
        const subpremise     = this.getComponentValue( 'subpremise', components );
        const simpleResult   = streetNumber || premise || subpremise;

        if ( simpleResult ) {
            return simpleResult;
        }

        const houseNumberResults = RegExp( '^(\\d+)\\s' ).exec( fullAddressResult );

        return Array.isArray( houseNumberResults ) ? `${houseNumberResults[ 1 ]}` : '';
    }

    getCity( components: google.maps.GeocoderAddressComponent[] ): string {
        return this.getComponentValue( 'sublocality_level_1', components ) || this.getCityIgnoringSublocalityLevel1( components );
    }

    getCityIgnoringSublocalityLevel1( components: google.maps.GeocoderAddressComponent[] ): string {
        const administrativeAreaLevel2 = this.getComponentValue( 'administrative_area_level_2', components );
        const administrativeAreaLevel3 = this.getComponentValue( 'administrative_area_level_3', components );
        const locality                 = this.getComponentValue( 'locality', components );
        const postalTown               = this.getComponentValue( 'postal_town', components );

        return locality || postalTown || administrativeAreaLevel2 || administrativeAreaLevel3 || '';
    }
}

export default GoogleAddressAutocompleteService;
