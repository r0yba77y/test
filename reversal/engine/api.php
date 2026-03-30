<?php
class Api {

	public function Api($user = ses_user, $request = ses_request, $id = 0, $ticker = null, $tf = 0, $offset = 0, $argument = []) {

		# Result
		$result = null;

        # Exec
        $exec = 0;

		# Ticker id
		$ticker_id = ($ticker ? $_SESSION[mem][tickers][$ticker] : 0);

		# Id chain
		if ($ticker) {
			$chain = (int)($ticker_id . str_pad($tf, 2, 0, STR_PAD_LEFT) . str_pad($_SESSION[mem][request][api][$request][id], 2, 0, STR_PAD_LEFT));
		} else {
			$chain = (int)str_pad($_SESSION[mem][request][api][$request][id], 2, 0, STR_PAD_LEFT);
		}

		# File
		$file = $_SESSION[mem][request][api][$request][folder] . '/' . $_SESSION[mem][request][api][$request][file] . '.py';

		# Host
		$host = ses_host_ibgateway;

		# Port
		$port = $_SESSION[mem][user][$user][info][port];

		# Client id
		$id_client = ($_SESSION[mem][request][api][$request][id_client] ?: null);

		# Argument
		$argument = $this->Argument($argument);

		# Python
		$python = implode(' ', array_filter([$file, $host, $port, $user, $id_client, $argument]));

        # Python
    	$python = ses_path . 'engine_ibr/venv/bin/python ' . ses_path . 'engine_ibr/' . $python;

		# Argument
		$argument = implode(' ', array_filter([$id_client, $argument]));

        # Tag
        $_SESSION[fun][visual]->Tag(python);

        # Python
        $_SESSION[fun][visual]->Python($python);
        
		# Compute
		if ($request == connection || $_SESSION[mem][user][$user][status][call] || $_SESSION[mem][setting][demo]) {

			# Exec
			$exec = $_SESSION[fun][tool]->Exec();

			# Client id lock
			$this->ClientIdLock($id_client, 1);

        	# Exec
        	exec($python, $data, $error);

			# Client id lock
			$this->ClientIdLock($id_client);

			# Exec
			$exec = $_SESSION[fun][tool]->Exec($exec);

	        # Chain
	        $temp = (in_array(ses_request, [connection, forex]) ? null : $user);
			$_SESSION[fun][tool]->Chain($temp, $offset, $exec, null, api);

	        # Data
	        $data = json_decode(implode('', $data), true);

	        # Id
		    if ($id) $data[id] = $id;

	        # Compute
	        if (!$error) {

	        	# Funcion
	        	$function = $_SESSION[fun][tool]->FunctionName($request);

	        	# Result
	        	if (method_exists($_SESSION[fun][api_wrap], $function)) {
	        		$result = $_SESSION[fun][api_wrap]->{$function}($data);
	        	} else {
	        		$result = $data;
	        	}
	        }

	        # Compute
	        if ($user == 'U17532659') {

				# Exec avg
				$exec_avg = $_SESSION[fun][sql]->Select1(d_log, "SELECT COALESCE(ROUND(AVG(exec), 3), $exec) FROM `call` WHERE request = '$request'");

		        # Sql
				$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO `call` (chain, request, file, host, port, user, argument, python, exec, exec_avg) VALUES ($chain, '$request', '$file', '$host', $port, '$user', '$argument', '$python', $exec, $exec_avg)");
	        }
		}

        # Return
        return $result;
	}

	private function Argument($argument) {

		# Compute
		if (count($argument)) {
		    $result = array_map(function($item) {
		        return is_numeric($item) ? floatval($item) : $item;
		    }, $argument);
    	    $result = implode(' ', $result);
		} else {
			$result = null;
		}

		# Return
		return $result;
	}

	private function ClientIdLock($id, $lock = 0) {

		# Return
		if (!$id) return;

		# Compute
		if ($lock) {

		    # Timer
		    $timer = microtime(true);

		    # Compute
		    while ($_SESSION[fun][cache]->Exists($id)) {

		        # Timeout ?
		        if ((microtime(true) - $timer) > 5) break;

				# Wait
		        usleep(100000);
		    }

			# Add
			$_SESSION[fun][cache]->Add($id);

		} else {

			# Delete
			$_SESSION[fun][cache]->Delete($id);
		}
	}
}