<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'PYS_FREE_VERSION', '9.0.0' );
define( 'PYS_FREE_PINTEREST_MIN_VERSION', '3.2.5' );
define( 'PYS_FREE_BING_MIN_VERSION', '2.2.2' );
define( 'PYS_FREE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PYS_FREE_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );





if ( isPysProActive()) {
    return; // exit early when PYS PRO is active
}
require_once PYS_FREE_PATH.'/vendor/autoload.php';
require_once PYS_FREE_PATH.'/includes/logger/class-pys-logger.php';
require_once PYS_FREE_PATH.'/includes/class-event-id-generator.php';
require_once PYS_FREE_PATH.'/includes/functions-common.php';
require_once PYS_FREE_PATH.'/includes/functions-admin.php';
require_once PYS_FREE_PATH.'/includes/events/class-event.php';
require_once PYS_FREE_PATH.'/includes/events/interface-events.php';
require_once PYS_FREE_PATH.'/includes/events/class-event-single.php';
require_once PYS_FREE_PATH.'/includes/events/class-event-grouped.php';
require_once PYS_FREE_PATH.'/includes/events/class-events-signal.php';
require_once PYS_FREE_PATH.'/includes/events/class-events-automatic.php';
require_once PYS_FREE_PATH.'/includes/events/class-events-woo.php';
require_once PYS_FREE_PATH.'/includes/events/class-events-edd.php';
require_once PYS_FREE_PATH.'/includes/events/class-events-fdp.php';
require_once PYS_FREE_PATH.'/includes/events/class-events-custom.php';

require_once PYS_FREE_PATH.'/includes/functions-custom-event.php';
require_once PYS_FREE_PATH.'/includes/functions-woo.php';
require_once PYS_FREE_PATH.'/includes/functions-edd.php';
require_once PYS_FREE_PATH.'/includes/functions-system-report.php';
require_once PYS_FREE_PATH.'/includes/functions-license.php';
require_once PYS_FREE_PATH.'/includes/functions-update-plugin.php';
require_once PYS_FREE_PATH.'/includes/functions-gdpr.php';
require_once PYS_FREE_PATH.'/includes/functions-migrate.php';
require_once PYS_FREE_PATH.'/includes/functions-optin.php';
require_once PYS_FREE_PATH.'/includes/functions-promo-notices.php';
require_once PYS_FREE_PATH.'/includes/class-fixed-notices.php';
require_once PYS_FREE_PATH.'/includes/class-pixel.php';
require_once PYS_FREE_PATH.'/includes/class-settings.php';
require_once PYS_FREE_PATH.'/includes/class-plugin.php';

require_once PYS_FREE_PATH.'/includes/class-events-manager-ajax_hook.php';
require_once PYS_FREE_PATH.'/includes/class-pys.php';
require_once PYS_FREE_PATH.'/includes/class-events-manager.php';
require_once PYS_FREE_PATH.'/includes/class-custom-event.php';
require_once PYS_FREE_PATH.'/includes/class-custom-event-factory.php';
require_once PYS_FREE_PATH.'/modules/facebook/facebook.php';
require_once PYS_FREE_PATH.'/modules/facebook/facebook-server.php';
require_once PYS_FREE_PATH.'/modules/google_analytics/ga.php';
require_once PYS_FREE_PATH.'/modules/head_footer/head_footer.php';


// here we go...
PixelYourSite\PYS();
