<?php
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Cascading Settings', 
            'Cascading Settings', 
            'manage_options', 
            'my-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'my_option_name' );
        ?>
        <div class="wrap">
            <h1>Cascading Settings</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'my_option_group' );
                do_settings_sections( 'my-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'my_option_group', // Option group
            'my_option_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            '',
            array( $this, 'print_section_info' ), // Callback
            'my-setting-admin' // Page
        );  

        add_settings_field(
            'merchant_id', // ID
            'Merchant ID', // Title 
            array( $this, 'merchant_id_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'key', 
            'Key', 
            array( $this, 'key_callback' ), 
            'my-setting-admin', 
            'setting_section_id'
        );

        add_settings_field(
            'api_callback', // ID
            'Api', // Title 
            array( $this, 'api_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );
        
        add_settings_field(
            'title_callback', // ID
            'Title', // Title 
            array( $this, 'title_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );
        
        add_settings_field(
            'save_my_card_text_callback', // ID
            'Save My Card Text', // Title 
            array( $this, 'save_my_card_text_callback' ), // Callback
            'my-setting-admin', // Page
            'setting_section_id' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['merchant_id'] ) )
            $new_input['merchant_id'] = sanitize_text_field($input['merchant_id']);

        if( isset( $input['key'] ) )
            $new_input['key'] = sanitize_text_field( $input['key'] );

        if( isset( $input['api'] ) )
            $new_input['api'] = sanitize_text_field( $input['api'] );

        if( isset( $input['save_my_card_text'] ) )
            $new_input['save_my_card_text'] = sanitize_text_field( $input['save_my_card_text'] );
        
        if( isset( $input['title'] ) )
            $new_input['title'] = sanitize_text_field( $input['title'] );


        return $new_input;

    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        //print 'Enter your settings below:';
    }

    public function save_my_card_text_callback()
    {
        printf(
            '<input type="text" id="save_my_card_text" size="100" name="my_option_name[save_my_card_text]" value="%s"/>',
            isset( $this->options['save_my_card_text'] ) ? esc_attr( $this->options['save_my_card_text']) : ''
        );
    }

    public function title_callback()
    {
        printf(
            '<input type="text" id="title" size="100" name="my_option_name[title]" value="%s"/>',
            isset( $this->options['title'] ) ? esc_attr( $this->options['title']) : ''
        );
    }

    public function api_callback()
    {
        printf(
            '<input type="text" id="api" size="100" name="my_option_name[api]" value="%s"/>',
            isset( $this->options['api'] ) ? esc_attr( $this->options['api']) : ''
        );
    }

    public function merchant_id_callback()
    {
        printf(
            '<input type="text" id="merchant_id" size="100" name="my_option_name[merchant_id]" value="%s"/>',
            isset( $this->options['merchant_id'] ) ? esc_attr( $this->options['merchant_id']) : ''
        );
    }

    public function key_callback()
    {
        printf(
            '<input type="text" id="key" size="100" name="my_option_name[key]" value="%s" />',
            isset( $this->options['key'] ) ? esc_attr( $this->options['key']) : ''
        );
    }

}

if( is_admin() )
    $my_settings_page = new MySettingsPage();