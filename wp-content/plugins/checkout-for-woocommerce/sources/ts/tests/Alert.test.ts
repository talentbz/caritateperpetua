import { cfwDefineScrollToNotices } from '../_functions';
import Alert                        from '../frontend/Components/Alert';

describe( 'Alert.ts', () => {
    cfwDefineScrollToNotices();

    const container = document.createElement( 'div' );
    container.id = 'cfw-alert-container';
    document.body.appendChild( container );

    const errorAlert   = new Alert( 'error', 'error message', 'error-class' );
    const noticeAlert  = new Alert( 'notice', 'notice message', 'notice-class' );
    const successAlert = new Alert( 'success', 'success message', 'success-class' );

    errorAlert.addAlert();
    noticeAlert.addAlert( true );
    successAlert.addAlert();

    test( 'addAlert', () => {
        const errorAlertjQuery = jQuery( container ).find( '.error-class' );
        expect( errorAlertjQuery.length ).toBe( 1 );
        expect( errorAlertjQuery.hasClass( 'error-class' ) ).toBeTruthy();
        expect( errorAlertjQuery.hasClass( 'cfw-alert' ) ).toBeTruthy();
        expect( errorAlertjQuery.find( '.message' ).length ).toBe( 1 );
        expect( errorAlertjQuery.find( '.message' ).html() ).toBe( 'error message' );

        const noticeAlertjQuery = jQuery( container ).find( '.notice-class' );
        expect( noticeAlertjQuery.length ).toBe( 1 );
        expect( noticeAlertjQuery.hasClass( 'notice-class' ) ).toBeTruthy();
        expect( noticeAlertjQuery.hasClass( 'cfw-alert' ) ).toBeTruthy();
        expect( noticeAlertjQuery.find( '.message' ).length ).toBe( 1 );
        expect( noticeAlertjQuery.find( '.message' ).html() ).toBe( 'notice message' );
        expect( noticeAlertjQuery.hasClass( 'cfw-alert-temporary' ) ).toBeTruthy();

        const successAlertjQuery = jQuery( container ).find( '.success-class' );
        expect( successAlertjQuery.length ).toBe( 1 );
        expect( successAlertjQuery.hasClass( 'success-class' ) ).toBeTruthy();
        expect( successAlertjQuery.hasClass( 'cfw-alert' ) ).toBeTruthy();
        expect( successAlertjQuery.find( '.message' ).length ).toBe( 1 );
        expect( successAlertjQuery.find( '.message' ).html() ).toBe( 'success message' );
    } );

    test( 'removeAlerts', () => {
        Alert.removeAlerts( jQuery( container ) );

        expect( container.children.length ).toBe( 0 );
    } );
} );
