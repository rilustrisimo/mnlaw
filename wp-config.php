<?php
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
define( 'DB_NAME', 'mnlaw' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Passw0rd1' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'jO-Sv3[28],7G5?@FjF1uH]<WL<*8p!-pKr$#-fnG}6`;[^K^p;PvJ0gdQ.pt0o3' );
define( 'SECURE_AUTH_KEY',  'H8KlrrjXle9Z1};B+F&p!;?G9^^9.U@cROLzv5Iury$)9rIHgfQYA+m-G>PZ`Nph' );
define( 'LOGGED_IN_KEY',    'tHT!X0HOMzh4yW>hR%]2^hx-0>:Bw}b4t$.S) B@0p0-db)FG?<ff%lo&n-eUImY' );
define( 'NONCE_KEY',        'Re+o^ )9eG],-aZCZz]D/4O=N6> _i8-E(,)a}X$68V8S!qose38>5}JFOk9[($I' );
define( 'AUTH_SALT',        'Bw7R+ l?}ebUQ3AUhyQ1@dDC?f=l&KJdS9c..nSTaJTH|5SsMta/!Qst&9o]H#gm' );
define( 'SECURE_AUTH_SALT', 'y*xh4&4*+2B~o0eqg`92LcwC1)|mJ^jrCmzMITvF2e`Wl]0?^7ZzM=kv8Ua<q%=G' );
define( 'LOGGED_IN_SALT',   'ZqJVFjs&XJ[{|wgoI=q%e2 Y(*%pH(Ec$5}3Ueyj,D;7F%IyE[t1=2Kn:(QIS)jx' );
define( 'NONCE_SALT',       ']<L[aA|Y[O&$@N&X+|<A>*b!MM.h)ckJux[t(.F%=h]jHE(yYd>nE6I$Ng?}%jPb' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
