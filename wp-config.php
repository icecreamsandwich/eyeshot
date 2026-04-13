<?php
define( 'WP_CACHE', true );

 // Added by SpeedyCache

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
// define( 'DB_NAME', 'eyesrzcn_wp707' );

// /** Database username */
// define( 'DB_USER', 'eyesrzcn_wp707' );

// /** Database password */
// define( 'DB_PASSWORD', 'k1)@q8t@p1]8w[FS' );

// /** Database hostname */
// define( 'DB_HOST', 'localhost' );

// /** Database charset to use in creating database tables. */
// define( 'DB_CHARSET', 'utf8mb4' );

// /** The database collate type. Don't change this if in doubt. */
// define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'dynb0jpvgbdlxj5katuvgtjbtwbma2nfazci8uwts4mfpya3p76dm5zbcaia39py' );
define( 'SECURE_AUTH_KEY',  'ngtpvtugjys4rg9yqaeqxrbm79dohwozo6izg0rjpnq34keyhlfpqw1t5ps0vsip' );
define( 'LOGGED_IN_KEY',    'nggwfc7iyxxl2irbplwlpuqxzgzu8wvkgrb9j7xpygsjd64qn7avmlbzsugl6lcu' );
define( 'NONCE_KEY',        'qe63cmfksuuqgl3hka9osipl3adu8zm3324m2k1wcgiug8ss4cyhfoutelbnhevt' );
define( 'AUTH_SALT',        'ktw1dzvap7fgfoqugnglbzuut5ao3b9lawxy3l9rwsapu0coxynvqosp3tndz8w4' );
define( 'SECURE_AUTH_SALT', 'djy0zgov6c6scyvtwogpntfcvqjyih7masfzlkeahj2ghftdcj3bdooaodsoswef' );
define( 'LOGGED_IN_SALT',   '7pnporuiam1megqdhubnxqehlayq0r1yxcrnlrskvqwcdlaspnwhgboa5ep93zpp' );
define( 'NONCE_SALT',       '1psocvhzs99yhvsp0vdkrzavbgziq09ghdjiinmscpvmq7icfhg7skguacjndhty' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp1l_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

/* That's all, stop editing! Happy publishing. */

// Include DDEV settings if available.
if (file_exists(__DIR__ . '/wp-config-ddev.php')) {
    require_once __DIR__ . '/wp-config-ddev.php';
}

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';