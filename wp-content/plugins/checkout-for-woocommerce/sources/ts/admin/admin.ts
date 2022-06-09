import { cfwDomReady }            from '../_functions';
import FieldToggler               from './components/FieldToggler';
import FontSelector               from './components/FontSelector';
import ImagePicker                from './components/ImagePicker';
import OrderBumpsAdmin            from './components/OrderBumpsAdmin';
import RichEditor                 from './components/RichEditor';
import SettingsExporterButton     from './components/SettingsExporterButton';
import SettingsImporterButton     from './components/SettingsImporterButton';
import TrustBadgeRepeater         from './components/TrustBadgeRepeater';

cfwDomReady( () => {
    /**
     * Code Editors
     */
    // Header Scripts
    new RichEditor( '#_cfw__settingheader_scriptsstring' );

    // Footer Scripts
    new RichEditor( '#_cfw__settingfooter_scriptsstring' );

    // Custom CSS
    new RichEditor( '#cfw_css_editor textarea.wp-editor-area', 'css' );

    // PHP Snippets
    new RichEditor( '#_cfw__settingphp_snippetsstring', 'php' );

    /**
     * Color Pickers
     */
    jQuery( '.cfw-admin-color-picker' ).wpColorPicker();

    /**
     * Font Selectors
     */
    new FontSelector( '#cfw-body-font-selector' );
    new FontSelector( '#cfw-heading-font-selector' );

    /**
     * Settings Export / Import
     */
    new SettingsExporterButton( '#export_settings_button' );
    new SettingsImporterButton( '#import_settings_button' );

    /**
     * Toggled Field Sections
     */
    new FieldToggler( '#cfw_checkbox_enable_cart_editing', '#cart_edit_empty_cart_redirect' );
    new FieldToggler( '#cfw_checkbox_enable_thank_you_page', '#cfw_checkbox_enable_map_embed, #thank_you_order_statuses, #cfw_checkbox_override_view_order_template' );
    new FieldToggler( '#cfw_checkbox_enable_trust_badges', '#trust_badges_title, .cfw-admin-trust-badge-row:not(.cfw-admin-trust-badge-template-row), .cfw-admin-add-trust-badge-row-button' );
    new FieldToggler( '#cfw_checkbox_enable_smartystreets_integration', '#smartystreets_auth_id, #smartystreets_auth_token' );
    new FieldToggler( '#cfw_checkbox_enable_fetchify_address_autocomplete', '#fetchify_access_token' );
    new FieldToggler( '#cfw_checkbox_enable_international_phone_field', '[name="_cfw__setting[international_phone_field_standard][string]"]' );

    new FieldToggler(
        '#cfw_enable_side_cart',
        '#cfw_checkbox_enable_ajax_add_to_cart, '
        + '#cfw_checkbox_enable_free_shipping_progress_bar, '
        + '#side_cart_free_shipping_threshold, '
        + '#side_cart_amount_remaining_message, '
        + '#side_cart_free_shipping_message, '
        + '#side_cart_free_shipping_progress_indicator_color, '
        + '#cfw_checkbox_enable_floating_cart_button, '
        + '#floating_cart_button_right_position, '
        + '#floating_cart_button_bottom_position, '
        + '#hide_floating_cart_button_empty_cart, '
        + '#cfw_checkbox_enable_order_bumps_on_side_cart, '
        + '#side_cart_icon_width, '
        + '[name="_cfw__setting[side_cart_icon][string]"], '
        + '#cfw_checkbox_enable_promo_codes_on_side_cart',
    );

    new FieldToggler(
        '#cfw_checkbox_enable_floating_cart_button',
        '#floating_cart_button_right_position, #floating_cart_button_bottom_position, #hide_floating_cart_button_empty_cart',
    );

    new FieldToggler(
        '#cfw_checkbox_enable_free_shipping_progress_bar',
        '#side_cart_free_shipping_threshold, #side_cart_amount_remaining_message, #side_cart_free_shipping_message, #side_cart_free_shipping_progress_indicator_color',
    );

    /**
     * Image Pickers
     */
    new ImagePicker( '.cfw-admin-image-picker-button' );

    /**
     * Trust Badge Repeater
     */
    new TrustBadgeRepeater();

    /**
     * Order Bumps Metaboxes
     */
    new OrderBumpsAdmin();

    // Enable Select2
    jQuery( document.body ).trigger( 'wc-enhanced-select-init' );

    /**
     * Order Bumps Form Validation
     */
    jQuery( '.post-type-cfw_order_bumps form#post' ).validate( {
        rules: {
            'cfw_ob_categories[]': {
                required() {
                    return jQuery( '#cfw_ob_display_for option:selected' ).val() === 'specific_categories';
                },
            },
            'cfw_ob_products[]': {
                required() {
                    return jQuery( '#cfw_ob_display_for option:selected' ).val() === 'specific_products';
                },
            },
            cfw_ob_offer_discount: {
                required: true,
                number: true,
            },
            cfw_ob_offer_product: {
                required: true,
            },
            cfw_ob_offer_language: {
                required: true,
            },
            cfw_ob_offer_description: {
                required: true,
            },
        },
        // Specify validation error messages
        messages: {
            'cfw_ob_categories[]': 'You must specify at least one category.',
            'cfw_ob_products[]': 'You must specify at least one product.',
            cfw_ob_offer_discount: 'Discount value must be a number. Example: 10, or 10.00',
            cfw_ob_offer_product: 'You must specify an offer product.',
        },
        focusInvalid: false,
        invalidHandler( form, validator ) {
            if ( !validator.numberOfInvalids() ) return;

            jQuery( 'html, body' ).animate( {
                scrollTop: jQuery( validator.errorList[ 0 ].element ).offset().top,
            }, 300 );
        },
        errorPlacement( error, element ) {
            error.appendTo( element.closest( 'td' ) );
        },
        submitHandler( form ) {
            form.submit();
        },
    } );
} );
