import { Md5 } from 'ts-md5/dist/md5';

class Alert {
    private type: 'error' | 'notice' | 'success';

    private message: any;

    private cssClass: string;

    private alertContainer: any;

    /**
     * @param type
     * @param message
     * @param cssClass
     */
    constructor( type: 'error' | 'notice' | 'success', message: string, cssClass: string ) {
        this.type = type;
        this.message = message;
        this.cssClass = cssClass;
        this.alertContainer = jQuery( '#cfw-alert-container' );
    }

    /**
     * @param {boolean} temporary
     */
    addAlert( temporary = false ): void {
        const alertElement = this.getOrBuildAlert( this.type, this.message, this.cssClass );

        alertElement.appendTo( this.alertContainer );
        alertElement.toggleClass( 'cfw-alert-temporary', temporary );

        // Scroll to the top of the alert container
        jQuery.scroll_to_notices( this.alertContainer );
    }

    private buildAlert( id: string, type: string, message: string, cssClass: string ): string {
        return `<div id="${id}" class="cfw-alert ${cssClass}"><div class="message">${message}</div></div>`;
    }

    private getOrBuildAlert( type: string, message: string, cssClass: string ): JQuery<HTMLElement> {
        // const alertTypeClass = type === 'notice' ? 'info' : type;
        const hash = Md5.hashStr( message + cssClass + type );
        const id = `cfw-alert-${hash}`;
        const existingAlert = jQuery( `#${id}` );

        if ( existingAlert.length > 0 ) {
            // has to be a better place for this
            this.alertContainer.slideDown( 300 );

            return existingAlert;
        }

        return jQuery( this.buildAlert( id, type, message, `${cssClass} ${hash}` ) );
    }

    /**
     * @param {any} alertContainer
     */
    static removeAlerts( alertContainer: any ): void {
        alertContainer.find( '.cfw-alert' ).remove();
    }
}

export default Alert;
