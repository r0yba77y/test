<?php
class Sql {

	public function Query($database, $query, $params = null) {

	    # Echo
	    $_SESSION[fun][visual]->Query($database, $query);

	    # Connection
	    $connection = new Sql_cnx($database);
	    $connection = $connection->Get($database);

	    # Compute
	    if ($params && is_array($params)) {

	    	# Mysqli
	        $mysqli = mysqli_prepare($connection, $query);
	        if ($mysqli) {

	            # Compute
	            $mysqli->bind_param(str_repeat('s', count($params)), ...$params);
	            $mysqli->execute();

	            # Result
	            if (stripos($query, 'SELECT') == 0) {
	                $result = $mysqli->get_result();
	            } else {
	                $result = true;
	            }
	        }

	    } else {

	        # Result
	        $result = mysqli_query($connection, $query);
	    }

	    # Return
	    return $result;
	}

	public function Select($database, $query) {

		# Tag
		if (ses_debug) {
			$tag = explode(' ', str_replace('`', '', substr($query, strpos($query, 'FROM'), 50)))[1] . ' get';
			$_SESSION[fun][visual]->Tag($tag);
		}

		# Result
		$result = $this->Query($database, $query);
		if (!$result || !mysqli_num_rows($result)) $result = null;

		# return
		return $result;
	}

	public function SelectRow($database, $query) {

		# Tag
		if (ses_debug) {
			$tag = explode(' ', str_replace('`', '', substr($query, strpos($query, 'FROM'), 50)))[0] . ' get';
			$_SESSION[fun][visual]->Tag($tag);
		}

		# Result
		$result = $this->Query($database, $query);

		# Result
		if ($result && mysqli_num_rows($result)) {
			$result = mysqli_fetch_assoc($result);
		} else {
			$result = null;
		}

		# return
		return $result;
	}

	public function Select1($database, $query) {

		# Tag
		if (ses_debug) {
			$tag = explode(' ', str_replace('`', '', substr($query, strpos($query, 'FROM'), 50)))[1] . ' get';
			$_SESSION[fun][visual]->Tag($tag);
		}

		# Result
		$result = $this->Query($database, $query);

		# Result
		if ($result) {
			$result = mysqli_fetch_assoc($result);
			$result = (is_array($result) ? $result[key($result)] : null);
		} else {
			$result = null;
		}

		# Return
		return $result;
	}

	public function Insert($database, $query) {

		# Tag
		if (ses_debug) {
			$tag = explode(' ', str_replace('`', '', substr($query, strpos($query, 'INTO'), 50)))[1] . ' add';
			$_SESSION[fun][visual]->Tag($tag);
		}

		# Result
		$this->Query($database, $query);
	}

	public function Delete($database, $query) {

		# Tag
		if (ses_debug) {
			$tag = explode(' ', str_replace('`', '', substr($query, 0, 50)));
			$_SESSION[fun][visual]->Tag($tag[2] . ' ' . $tag[0]);
		}

		# Result
		$this->Query($database, $query);
	}

	public function Update($database, $query) {

		# Tag
		if (ses_debug) {
			$tag = explode(' ', str_replace('`', '', substr($query, 0, 50)));
			$_SESSION[fun][visual]->Tag($tag[1] . ' ' . $tag[0]);
		}

		# Result
		$this->Query($database, $query);
	}

	public function Alter($database, $query) {

		# Result
		$this->Query($database, $query);
	}

	public function CreateDatabase($database, $query) {

		# Result
		$this->Query($database, $query);
	}

	public function CreateTable($database, $query) {

		# Result
		$this->Query(create, $database, $query);
	}

	public function Optimize($database) {

		# Tables
		$result = $this->Query($database, "SHOW TABLES");
	    $tables = [];
	    while ($a = $result->fetch_row()) {
	        $tables[] = $a[0];
	    }

		# Actions
		$actions = ['ANALYZE TABLE', 'CHECK TABLE', 'CHECKSUM TABLE', 'OPTIMIZE TABLE', 'FLUSH TABLE', 'OPTIMIZE TABLE'];

		# Compute
	    foreach ($tables as $table) {

			# Compute
	        foreach ($actions as $action) {

	            # Tag
	            $_SESSION[fun][visual]->Tag("$database $table $action");

	            # Esegui query
	            $this->Query($database, "$action `$table`");

	            # Sleep
	            usleep(200000);
	        }
	    }
	}

	public function Truncate($database, $table) {

		# Tag
		$_SESSION[fun][visual]->Tag($table . ' truncate');

		# Result
		$this->Query($database, 'TRUNCATE TABLE `' . $table . '`');
	}

	private function Escape($connection, $value) {

		# Compute
	    if (is_array($value)) {
	        return array_map(function($v) use ($connection) {
	            return mysqli_real_escape_string($connection, (string)$v);
	        }, $value);
	    }

	    # Result
	    $result = mysqli_real_escape_string($connection, (string)$value);

	    # Return
	    return $result;
	}
}