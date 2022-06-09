<?php
define( 'WP_CACHE', true ); // Added by WP Rocket


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress_4');

/** MySQL database username */
define('DB_USER', 'wordpress_2');

/** MySQL database password */
define('DB_PASSWORD', '8s77Um#PrW');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'LVfePdOm9CFKmXGBdwoO9*mik2wJa81uVLvwIeH6nioDhwOUYz#DZd(rlh&3MbKi');
define('SECURE_AUTH_KEY',  'hzWKU^hn8)%Hvhs&5ron!AtoVw8oR8n*ZJxE1fFfEnSEzD^gcRQlQimYhqzuAL7V');
define('LOGGED_IN_KEY',    'd4XsCx9K@ceh92yVx1anV3hmA*hQP7FLjgl@ZxsV3pUL2ad%NtNZ6^361DPjU@Lb');
define('NONCE_KEY',        'HnhlCjWTf@L!doUAn&FG^U*k3uEVPBq1N8&T1e4j&cBirhof5^mEKDJ#SAvcwkvy');
define('AUTH_SALT',        'dFoUwA0l99HrP2thzc&8oq6O7xdd%UfEATE4M3XL^7xNrJ*jC#JJD3zfNGrMr!xX');
define('SECURE_AUTH_SALT', 'U9pCcg6L91pFpnnRVz*v#yuXtD)nVJ#TeqK^Z)LPpieEj!%Jm3QnUNzfE)OsiGBZ');
define('LOGGED_IN_SALT',   '6#6HVRx!x#Ctc!XfCR&UpnbJh(5BBOe%zZc)sncfjV8CYWnyDRGKSJEMuAB*eypp');
define('NONCE_SALT',       'z#bDvM4N)7jRUWtqbPRVp^pM*!*QSK8udmOyj*nqG7Ot5%@li@r!fYMk3#rf3Y1c');
/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

define( 'WP_ALLOW_MULTISITE', true );

define ('FS_METHOD', 'direct');
