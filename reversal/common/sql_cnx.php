<?php
class Sql_cnx {

	private static $connection = [];

	public function __construct($database = null) {

		# Return
		if (!$database) return;

		# Compute
		self::$connection = mysqli_connect(ses_host_mariadb, 'backtest', 'lamU-55%%', $database, ses_host_mariadb_port);
		if (!self::$connection) die();
	}

	public function Get($database) {

		# Compute
		return self::$connection;
	}

	public function Close() {

		# Return
		if (!self::$connection) return;

		# Compute
		mysqli_close(self::$connection);
		self::$connection = null;
	}
}