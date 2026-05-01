<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'syrsocco_wp_tln8f' );

/** Database username */
define( 'DB_USER', 'syrsocco_wp_amdde' );

/** Database password */
define( 'DB_PASSWORD', '0X@rnDL!7!f!7t1t' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define('AUTH_KEY', 'p6h69716|IP88(K]C92T692@4Cv4Mn~/)8v%6h/E1BX]7-3RKe99~p0nqH5b_raB');
define('SECURE_AUTH_KEY', ')u:oau*f24#0mR[;:(P|7k#9/6;_-1gmj2+lwiE&zgg3%;S~*0OG&1Z/ZZJ!)_O8');
define('LOGGED_IN_KEY', '+TQ-H77piTe4U:0)Z]OKE9|W(r5/79~-r*JaWlM5s-LP4Pj]twG4Ap9[1sMeESKe');
define('NONCE_KEY', ')n;|E|Q11~6@7w;1F8u:v_6q|5YZ[+Q~)Z(51]%a!b;q)P3@VJ6sG40/9*Fh~Lk[');
define('AUTH_SALT', '72O[12ae+RZw3@yx06D]c!g9#F62*6N_5+Q7/4_0Dv7R5I7OpGm(8|QgdMzu100t');
define('SECURE_AUTH_SALT', '*xLb/[T]:RvD5tvR;X7+#(o%x/AqDd4l34y9A(z8KKa(D)p_97L]60Suoj9p6U7l');
define('LOGGED_IN_SALT', '/k6DF2xKs17Gl0B16n&%2Kl42g3mYdh/V2etW;UoHcb56_3+3r0*|Q8ir6&MJ|Je');
define('NONCE_SALT', '08geA4@Hy8V56SmE+682bkse_4P3W&A%6!P07w4iGl52dkQ*@jc_ph_Y0x5WlsEz');


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = '6ttiGPR5_';


/* Add any custom values between this line and the "stop editing" line. */

define('WP_ALLOW_MULTISITE', true);
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'DISALLOW_FILE_EDIT', true );
define( 'CONCATENATE_SCRIPTS', false );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
