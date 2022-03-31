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
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'db-physsa' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '[nFCH]jz[wfkVggNRa}>CO*5q;LOl0Tv7zR-D}[f.)Oh6?XCB#B`kH*Yd~|S&,+5' );
define( 'SECURE_AUTH_KEY',  's)Xo7Y/3]hH%6&h[/3_b2jfCra8n1W)Q~T*rerLVwS!?)bc;2y-$B(iWmM%ktx@I' );
define( 'LOGGED_IN_KEY',    'emA-~;.*<*qsv()n]Z3E6I]Q_U$U&9UNF[`V32m(?^* qA)zk0iCEY4U(VJ34.Yi' );
define( 'NONCE_KEY',        '{^EOC!YMSL`BR5}G@x,D9h6$@1/mJrBHvOe%1ZWXfBME4m+lj(6|JR/(PxkXWA&X' );
define( 'AUTH_SALT',        '}SlM$gYy0w](%D:`__57kY8shW_Jc~CdNo?MY}8pSse]Rj1Ww3f>o{I3_34``ZQ$' );
define( 'SECURE_AUTH_SALT', 'X3Z]4VL}q.O}hb|Wrk5Ho(5]*r FJwaeRpO(j(6=UX@*5&#tP`gui.wBOC;:&uv3' );
define( 'LOGGED_IN_SALT',   'H3y/Vg]I=5D8>5KMv&c9JO[@21~#Yn7`>/7r= ]{t27k5A1-^iJ7SG>mA>uX0Bh-' );
define( 'NONCE_SALT',       'Y$[%3+$76faY?THg|b]U{F,!6!Az|]+]aEbQ7Jj~RVDId<LyZH:Y|% F`M5&k#AU' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'dbc_';

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
