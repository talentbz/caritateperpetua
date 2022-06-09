/**
 * Field Persistence Service
 */
import DataService    from './DataService';
import LoggingService from './LoggingService';

class FieldPersistenceService {
    constructor( form: JQuery ) {
        form.garlic( {
            events: [ 'textInput', 'input', 'change', 'click', 'keypress', 'paste', 'focus', 'cfw_garlic_store' ],
            destroy: false,
            excluded: DataService.getSetting( 'field_persistence_excludes' ).join( ', ' ),
            onRetrieve: this.onRetrieve.bind( this ),
        } );

        this.setListeners();
    }

    setListeners(): void {
        // After Parsley Service resets field
        jQuery( document.body ).on( 'cfw-after-field-country-to-state-changed', ( e ) => {
            jQuery( e.target ).garlic();
        } );
    }

    onRetrieve( element: JQuery, retrievedValue ): void {
        jQuery( document.body ).trigger( 'cfw-field-persistence-after-retrieve-value', [ element, retrievedValue ] );
        LoggingService.logEvent( 'Fired cfw-field-persistence-after-retrieve-value event.' );
    }
}

export default FieldPersistenceService;
