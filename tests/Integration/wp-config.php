<?php

/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
define('ABSPATH', dirname(dirname(__FILE__)) . '/wordpress/');

/*
 * Path to the theme to test with.
 *
 * The 'default' theme is symlinked from test/phpunit/data/themedir1/default into
 * the themes directory of the WordPress installation defined above.
 */
define('WP_DEFAULT_THEME', 'default');

// Test with multisite enabled.
// Alternatively, use the tests/phpunit/multisite.xml configuration file.
// define( 'WP_TESTS_MULTISITE', true );

// Force known bugs to be run.
// Tests with an associated Trac ticket that is still open are normally skipped.
// define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode (default).
define('WP_DEBUG', true);

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.

define('DB_NAME', getenv('WP_DB_NAME') ?: 'wordpress_integration_tests');
define('DB_USER', getenv('WP_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('WP_DB_PASS') ?: 'root');
define('DB_HOST', getenv('WP_DB_HOST') ?: 'localhost:8889');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 */
define('AUTH_KEY', 'HM&&Uu^_hL]lKfBB-i0:MvVK|VBvxLV?a;MW4hNa~{|V%Z/M^fWk@GxeW1X wU:+');
define('AUTH_COOKIE', 'HM&&Uu^_hL]lKfBB-i0:MvVK|VBvxLV?a;MW4hNa~{|V%Z/M^fWk@GxeW1X wU:+');
define('LOGGED_IN_COOKIE', 'HM&&Uu^_hL]lKfBB-i0:MvVK|VBvxLV?a;MW4hNa~{|V%Z/M^fWk@GxeW1X wU:+');
define('SECURE_AUTH_KEY', 'a)YdP(B`AFwEF,mw-d.ML+|M@SYmqC+D7CSb^cgSqrm:TB%l$<?^5H(M$M9{tDwG');
define('LOGGED_IN_KEY', 'f s}S%=yw[x(|.QD+cUf_SXHvN7Ev4FI:Ugs)j|-o=DIv7=lF6wDw-nT(Ca(B!J6');
define('NONCE_KEY', '/R+c;j8.d`Hm6_S~|Hu-SCgtlaeQI6X^V^XVMEf}yS[7VPHdD>qIO{z~Sl7_FeGg');
define('AUTH_SALT', '0-|H[U-hbrd@Wzvsz~C653@4d%kXE9_ac[m&[(|xtV-gl2^/<kw2|!mYm)B||`yl');
define('SECURE_AUTH_SALT', '&5|Gpc8PLl5?< <ZkiL8x<U/shc^Y+%QB-fglK%5Q:T-$87|Q5&hZa$:N@)6]ZOk');
define('LOGGED_IN_SALT', 'Yo!H_jm;4Jh1OA:Nv|uH6H[WZYyM`{izBhW40!|kr)^@U|sSQ-Bw^PqjSE`J!@DT');
define('NONCE_SALT', '?CtHM7+yZ--!B1,Z}i$j$VI[>an@ZRNQX &-<*%u+dvZW+|5OR+2V<2@<UC[>+RM');

$table_prefix = 'tests_'; // Only numbers, letters, and underscores please!

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');

define('WP_PHP_BINARY', 'php');

define('WPLANG', '');
