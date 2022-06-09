class FormField {
    private static _floatClass = 'cfw-floating-label';

    private static _nonFloatingSelectClass = 'cfw-nonfloating-select-label';

    private static _select2Class = 'cfw-select2';

    constructor() {
        // This needs to run before the select2 handler in country-select.js
        // So we're using firstOn - https://www.npmjs.com/package/jquery-first-event
        jQuery( document.body ).firstOn( 'country_to_state_changed', () => {
            jQuery( '.state_select' ).removeClass( 'state_select' );
        } );

        // Timeout is to make sure Select2 init'd since it doesn't have an event
        setTimeout( () => {
            jQuery( document.body ).on( 'keyup change', '.cfw-input-wrap :input', ( e ) => {
                this.maybeAddFloatClass( jQuery( e.target ) );
            } );

            this.processFieldLabels();
        } );

        // Handle fields after dynamic refreshes
        jQuery( document.body ).on( 'updated_checkout', () => {
            // Ditto
            setTimeout( () => {
                this.processFieldLabels();
            } );
        } );
    }

    maybeAddFloatClass( element: any ): void {
        const parentElement = jQuery( element ).parent( '.cfw-input-wrap' );

        if ( ( !jQuery( element ).is( 'select' ) && jQuery( element ).val() !== '' ) || ( jQuery( element ).is( 'select' ) && !jQuery( element ).hasClass( 'select2-hidden-accessible' ) )  ) {
            parentElement.addClass( FormField.floatClass );
        } else if ( jQuery( element ).is( 'select' ) ) {
            parentElement.addClass( FormField.nonFloatingSelectClass );
            parentElement.addClass( FormField.select2Class );
            parentElement.removeClass( FormField.floatClass );
        } else {
            parentElement.removeClass( FormField.floatClass );
        }
    }

    processFieldLabels(): void {
        jQuery( '.cfw-input-wrap :input' ).each( ( index, element ) => {
            this.maybeAddFloatClass( element );
        } );
    }

    static get floatClass(): string {
        return this._floatClass;
    }

    static get nonFloatingSelectClass(): string {
        return this._nonFloatingSelectClass;
    }

    static get select2Class(): string {
        return this._select2Class;
    }
}

export default FormField;
