<?php


# Ping
$ping = microtime(1);

# Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

# Timezone
date_default_timezone_set('Europe/Rome');

# Session
Session();

# Path
$path = '/var/www/html/reversal/';

# Require
require $path . 'common/boot.php';
require $path . 'common/define.php';

# Request
$request = null;

# Data
if (isset($argv)) {
	$request = $argv[1];
	$dashboard = 0;
} elseif (isset($_GET[request])) {
	if ($_GET[request] == trade_add && ($_GET[token] ?? null) != 'k5mVGF97wjCtBeUZy2dP7TTpkARU0T') {
		http_response_code(404);
		exit;
	} else {
		$request = $_GET[request];
		$dashboard = in_array($request, [dashboard], true);
	}
} else { 
	Redirect();
}

# Request
$request = preg_replace('/[^a-z_]/', '', $request);

# Session
define('ses_dashboard', $dashboard);
define('ses_date', date('Y-m-d H:i'));
define('ses_debug', isset($_GET[dd]) ? 2 : (isset($_GET[d]) ? 1 : 0));
define('ses_path', $path);
define('ses_ping', $ping);
define('ses_request', $request);
define('ses_user', 'U17532659');
define('ses_host_ibgateway', 'localhost');
define('ses_host_redis', 'localhost');
define('ses_host_mariadb', 'localhost');
define('ses_host_mariadb_port', 3306);

# Boot
$boot = new Boot();

# Compute
if ($boot->Exec()) {
	$_SESSION[fun][visual]->Footer();
} else {
	header('Location: /');
}
	
# Connection
$_SESSION[fun][sql_cnx]->Close();

# Session
Session(false);

# Session
function Session($action = true) {
	if ($action) {
		session_start();
	} else {
		session_unset();
		session_destroy();
	}
}