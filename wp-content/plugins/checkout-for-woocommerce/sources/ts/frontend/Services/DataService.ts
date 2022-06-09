declare let cfwEventData: any;

class DataService {
    /**
     * @type {any}
     * @private
     */
    private static _checkoutForm: any;

    static initRunTimeParams() {
        cfwEventData.runtime_params = {};
    }

    static getSettings() {
        return cfwEventData.settings;
    }

    static getSetting( setting: string ) {
        if ( cfwEventData.settings[ setting ] ) {
            return cfwEventData.settings[ setting ];
        }

        return false;
    }

    static getMessage( messageKey: string ) {
        if ( cfwEventData.messages[ messageKey ] ) {
            return cfwEventData.messages[ messageKey ];
        }

        return '';
    }

    static getCompatibilityClass( key: string ) {
        return cfwEventData.compatibility[ key ];
    }

    static getElements() {
        return cfwEventData.elements;
    }

    static getElement( element: string ) {
        if ( cfwEventData.elements[ element ] ) {
            return jQuery( cfwEventData.elements[ element ] );
        }

        return false;
    }

    static getCheckoutParams() {
        return cfwEventData.checkout_params;
    }

    /**
   * @param param
   */
    static getCheckoutParam( param: string ) {
        if ( cfwEventData.checkout_params[ param ] ) {
            return cfwEventData.checkout_params[ param ];
        }

        return false;
    }

    static getRuntimeParameters() {
        return cfwEventData.runtime_params;
    }

    static getRuntimeParameter( param: string ) {
        if ( cfwEventData.runtime_params[ param ] ) {
            return cfwEventData.runtime_params[ param ];
        }

        return false;
    }

    static setRuntimeParameter( param: string, value: any ) {
        cfwEventData.runtime_params[ param ] = value;
    }

    /**
     * @returns {JQuery}
     */
    static get checkoutForm(): JQuery {
        return DataService._checkoutForm;
    }

    /**
     * @param {JQuery} value
     */
    static set checkoutForm( value: JQuery ) {
        this._checkoutForm = value;
    }
}

export default DataService;
