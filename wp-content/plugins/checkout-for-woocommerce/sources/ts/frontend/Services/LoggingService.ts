import DataService from './DataService';

class LoggingService {
    static logError( message: string ): void {
        LoggingService.log( `${message} ⚠️`, true );
    }

    static logNotice( message: string ): void {
        LoggingService.log( `${message} ℹ️` );
    }

    static logEvent( message: string ): void {
        LoggingService.log( `${message} 🔈` );
    }

    static log( message: string, force = false ): void {
        if ( force || DataService.getCheckoutParam( 'cfw_debug_mode' ) ) {
            // eslint-disable-next-line no-console
            console.log( `CheckoutWC: ${message}` );
        }
    }
}

export default LoggingService;
