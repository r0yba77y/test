<?php
class Visual {

	public function Header() {

		# Echo
		echo '<html style="font-family: monospace;background-color: #0E283B;font-size: 13px;color: white">';
		echo '<head><title>' . ucfirst(str_replace('_', ' ', ses_request)) . '</title></head>';

		# Echo
		if (ses_request != dashboard) {
			echo '<span style="color: gray">:::::</span> <span style="color: steelblue">' . date('Y-m-d H:i:s') . '</span>' . xx;
			echo '<span style="color: gray">:::::</span> <span>' . strtoupper(ses_request) . '</span>' . x;
		}
	}

	public function Footer() {

		# End
		$this->Tag(end, '', 1, true);

		# Time
		$time = (microtime(1) - ses_ping) * 1000;
		if ($time < 10) {
			$time = round($time, 2) . ' milliseconds';
		} elseif ($time < 1000) {
			$time = round($time / 1000, 2) . ' seconds';
		} elseif ($time < 60 * 1000) {
			$time = round($time / 1000) . ' seconds';
		} else {
			$minutes = floor($time / (60 * 1000));
			$seconds = round(($time % (60 * 1000)) / 1000);
			$time = $minutes . ' minutes and ' . $seconds . ' seconds';
   		}

		# Echo
		if (ses_request != dashboard) {
			echo xx . '<span style="color: gray">:::::</span> <span style="color: springgreen">COMPLETED | ' . $time . '</span>';
		}

		# Echo
		echo '</body>';
	}

	public function Print($data) {

		# Compute
		print('<pre>' . print_r($data, true) . '</pre>');
	}

	public function Tag($data, $tf = 0, $set = 0, $force = false) {

		# Return
		if (!ses_debug && !$force) return;
		if ($_SESSION[mem][status][request] == setting && ses_debug != 2) return;

		# Tf
		if ($tf) {
			$tf = 'TF <span style="color: #D280FF">' . $tf . '</span>';
		} else {
			$tf = null;
		}

		# Echo
		echo x;

		# Result
		if ($set == 1) {
			echo '<span style="color: gray">:::::</span> <span style="color: gold;text-decoration: underline">' . strtoupper($data);
		} elseif ($set == 2) {
			echo '<span style="color: gray">:::::</span> <span style="color: crimson;text-decoration: underline">' . strtoupper($data) . '</span> <span style="color: steelblue">' . $tf;
		} elseif ($set == 4) {
			echo '<span style="color: gray">::::: ' . strtoupper($data) . '</span>';
		} else {
			echo '<span style="color: gray">:::::</span> <span style="color: steelblue">' . strtoupper($data) . '</span> <span style="color: steelblue"><span style="color: white">' . $tf . '</span>';
		}

		# Echo
		echo '</span>' . x;
	}

	public function Python($data) {

		# Return
		if (!ses_debug) return;

        # Echo
        echo '<span style="color: gray">:::::</span> <span style="color: springgreen">' . $data . '</span>' . x;
	}

	public function TagCache($request, $id, $expiration = null) {

		# Return
		if (!ses_debug) return;

		# Echo
		if (is_array($id)) $id = implode(';', $id);

		# Result
		if (!is_null($expiration)) $expiration = '<span style="color: crimson"> (' . (int)$expiration . ' min)</span>';

		# Echo
		echo x . '<span style="color: gray">::::: </span><span style="color: crimson">CACHE ' . strtoupper($request) . ' ' . $id . $expiration . '</span>' . x;
	}

	public function TagFunction($function, $force = false) {

		# Return
		if (!ses_debug && !$force) return;

		# Echo
		echo x . '<span style="color: gray">:::::</span> <span style="color: red">FUNCTION | ' . $function . '</span>' . x;
	}

	public function Error404() {

		# Compute
		http_response_code(404);
		header('location: /');
		die();
		return;
	}

	public function Query($database, $query) {

		# Return
		if (!ses_debug) return;
		if ($_SESSION[mem][status][request] == setting && ses_debug != 2) return;

		# Ping
		$ping = number_format(round(microtime(1) - ses_ping, 3), 3);

		# Echo
		echo '<span style="color: gold">' . $ping . ' </span> <span style="color: tomato"> [' . strtoupper($database) . '] </span><span style="color: gray">' . (strlen($query) >= 2000 ? substr($query, 0, 2000) . '...' . x : $query) . '</span>' . x;
	}
}