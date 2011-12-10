<?php
/**
 * Install a fresh copy of Elgg
 */

// figure out what our URL should be
$protocol = 'http';
if (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
	$protocol = 'https';
}
$port = ':' . $_SERVER["SERVER_PORT"];
if ($port == ':80' || $port == ':443') {
	$port = '';
}
$uri = $_SERVER['REQUEST_URI'];
$cutoff = strpos($uri, 'mod/demo/install.php');
$uri = substr($uri, 0, $cutoff);
$url = "$protocol://{$_SERVER['SERVER_NAME']}$port{$uri}";



require_once(dirname(dirname(dirname(__FILE__))) . "/install/ElggInstaller.php");

$installer = new ElggInstaller();

$params = array(
	// database parameters already in settings.php
	'dbuser' => '',
	'dbpassword' => '',
	'dbname' => '',

	// site settings
	'sitename' => 'Elgg Demo',
	'wwwroot' => $url,
	'dataroot' => '/var/lib/elgg/',
	'siteemail' => 'noreply@elgg.org',

	// admin account
	'displayname' => 'Site Admin',
	'email' => 'cash@elgg.org',
	'username' => 'admin',
	'password' => substr(md5(microtime() . rand()), 0, 8),
);

file_put_contents($params['dataroot'] . 'login', $params['password']);

$installer->batchInstall($params, false);
