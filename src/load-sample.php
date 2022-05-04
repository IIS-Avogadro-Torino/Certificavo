<?php
/*******************
 * This is the secret configuration file of my website *
 *                                                     *
 * Fill and rename this file as 'load.php'             *
 *                                                     *
 * NEVER PUT IN GIT YOUR 'load.php'!                   *
 *******************/

// Site configuration
$logo = 'assets/img/avologo.png';
$altLogo = 'assets/img/scrittaLogo.png';   //if not set will automaticaly replaced with logo
$loginLogo = 'assets/img/logo-certificavo.png'; //if not set will automaticaly replaced with logo
$background = 'assets/img/avodavanti.jpg';
$backgroundFilterColor = '#FFE161';// put rgb color ina hex notation es: #FFFFFF

// Certficate configuration
$certCockade = _DIR_.'/templates/components/coccarda.png';
$certBackground = _DIR_.'/templates/components/sfondo.png';
$certSample = 'templates/esempio.png';

// Database configuration
$username = '';
$password = '';
$database = '';
$location = 'localhost';
$charset  = 'utf8mb4';

// SMTP credentials
$SMTPHost = '';
$SMTPUsername = '';
$SMTPPassword = '';

// change these contants only ONCE for your website and then nevermind
// if you change this, your users will not be able to log in with the same password
define( 'PASSWD_HASH_SALT', 'very-random-data'    );
define( 'PASSWD_HASH_PEPP', 'another-random-data' );

// you can change these to log-out every user (longer is more secure)
define('COOKIE_HASH_SALT', 'asdasasd paposdppioasasd');
define('COOKIE_HASH_PEPP', 'another-very-random-data-asd');

// Prefix for every your database table (if any)
//  You can use something as 'foobar_' or an empty string
$prefix = '';

// How you visit your website?
//  Use an empty string if you visit it with http://localhost/
//  Use '/project'      if you visit it with http://localhost/project/
define( 'ROOT', '' );

// Absolute pathname to this directory
//  Usually leave it as-is
define( 'ABSPATH', _DIR_);

// Path for the library
define( 'LIBRARY_PATH', _DIR_.'/library' );

// ____IN ORDER TO WORK THE PRIVATE AND PUBLIC MUST BE Ed25519 KEYS____

// Path for the private key
define( 'PRIVATE_KEY_PATH', _DIR_.'/keys/privateKey');

// Path for the public key
define( 'PUBLIC_KEY_PATH', _DIR_.'/keys/publicKey');

//_____________________________

// Load the framework
//  You know where the framework is
//  For example I put it in: '/usr/share/php/suckless-php/load.php'
//  but often you want to put it in this same directory,
//  in that case just use this pathname:    'suckless-php/load.php'
//  Anyway, it may be everywhere. You can choose.
require LIBRARY_PATH.'/suckless-php/load.php';