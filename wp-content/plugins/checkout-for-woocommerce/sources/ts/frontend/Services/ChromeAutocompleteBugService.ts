class ChromeAutocompleteBugService {
    constructor() {
        // Chrome has an awful, pernicious bug: https://bugs.chromium.org/p/chromium/issues/detail?id=448539
        // This results in fields on other tabs from being autofilled
        // By setting fields in hidden tabs to readonly it prevents them from being autofilled
        jQuery( window ).on( 'load updated_checkout cfw-after-tab-change', () => {
            jQuery( '.cfw-panel' ).find( ':input' ).attr( 'readonly', 'readonly' );
            jQuery( '.cfw-panel.active' ).find( ':input' ).removeAttr( 'readonly' );
        } );
    }
}
export default ChromeAutocompleteBugService;
