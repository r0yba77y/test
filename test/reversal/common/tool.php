<?php
class Tool {

    public function Error1($errno, $errstr, $errfile, $errline) {

    	# Echo
        echo "Errore [$errno]: $errstr in $errfile alla riga $errline\n";
    }

    public function Error2($data) {

    	# Id
    	$id = (int)date('NHi');

    	# File
    	$file = str_replace(ses_path, '', $data->getFile());

    	# Error
    	$error = preg_replace("/[^a-zA-Z0-9 .,_@()-]/", "", $data->getLine());

    	# Request
    	$request = ses_request;

    	# Sql
    	$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO error (id, request, error, file, `line`) VALUES ($id, '$request', '" . $data->getMessage() . "', '$file', $error)");
    }

	public function EurToUsd($value, $precision = 6, $eur_to_usd = true) {

		# Result
		if ($eur_to_usd) {
			$result = round($value * $_SESSION[mem][currency][forex][EUR_USD], $precision);
		} else {
			$result = round($value / $_SESSION[mem][currency][forex][EUR_USD], $precision);
		}

		# Return
		return (float)$result;
	}

	public function StatusPriority($status_old, $status_new) {

		# Priority
		$priority = [
		    ''              => 0,
		    submitted_in	=> 1,
		    book       		=> 2,
		    filled_in_part  => 3,
		    filled_in       => 4,
		    submitted_out   => 5,
		    booked_out      => 6,
		    filled_out_part => 7,
		    filled_out      => 8,
		    completed       => 9
		];

		# Return
		return ($priority[$status_new] <= $priority[$status_old] ? $status_old : $status_new);
	}

	public function Percentage($value, $percentage) {

		# Result
		if ($percentage > 0) {
			$result = $value * (1 + (abs($percentage) / 100));
 		} elseif ($percentage < 0) {
			$result = $value - ($value * (abs($percentage) / 100));
		} else {
			$result = $value;
		}

		# Return
		return (float)$result;
	}

	public function Multiple(int $value, int $multiple) {

		# Result
		$result = (int)($value % $multiple == 0);

		# Return
		return $result;
	}

	public function SecondsTo() {

		# Result
		$result = 60 - date('s');

		# Return
		return $result;
	}

	public function OrderAction($data) {

		# Result
		$result = $data;

		# Result
		if ($result == 'sshort') {
			$result = sell_short;
		}

		# Return
		return $result;
	}

	public function OrderType($data) {

		# Result
		$result = strtolower($data);

		# Result
		if ($result == 'lmt') {
			$result = limit;
		} elseif ($result == 'mkt') {
			$result = market;
		} elseif ($result == 'stp') {
			$result = stop;
		}

		# Return
		return $result;
	}

	public function DateNY($date = null) {

		# Date
	    $date = new DateTime(is_null($date) ? ses_date : $date);
	    $date->setTimezone(new DateTimeZone('America/New_York'));

	    # Return
	    return $date;
	}

	public function HourNY($date) {

		# Date
	    $date = $this->DateNY($date);

	    # Result
	    $result = (int)$date->format('Hi');

	    # Return
	    return $result;
	}

	private function Day($date = null) {

		# Date
	    $date = $this->DateNY($date);

	    # Result
	    $result = ((int)$date->format('N') <= 5);

	    # Return
	    return $result;
	}

	private function Holiday($date = null) {

		# Date
	    $date = $this->DateNY($date);

	    # Result
	    $result = !in_array($date->format('Y-m-d'), array_keys($_SESSION[mem][status][holiday]));

	    # Return
	    return $result;
	}

	/*public function Nightshift($date = null) {

	    # Date
	    if (is_null($date)) $date = ses_date;

	    # Timezone
	    $timezone = [
	        new DateTimeZone('America/New_York'),
	        new DateTimeZone('Europe/Rome')
	    ];

	    # Now
	    $now = strtotime($date) + $timezone[0]->getOffset(new DateTime($date));

		# Return
		if (date('H:i', $now) <= '20:00'|| $_SESSION[mem][status][market][now]) return;

	    # Result
	    $result = null;

	    # Hour
	    $hour = $this->Hours();

	    # Holiday
	    $holiday = array_keys($_SESSION[mem][status][holiday]);

	    # Compute
	    for ($a=0; $a<12; $a++) {

   	        # Check
	        $check = strtotime("+$a day", strtotime($date));

	        # Compute
	        if (date('N', $check) <= 5 && !in_array(date('Y-m-d', $check), $holiday)) {

	            # Open
	            $open = sprintf('%04d', $hour[0][0]);
	            $open_hour = substr($open, 0, 2);
	            $open_min = substr($open, 2, 2);

	            # Result
	            $result = mktime($open_hour, $open_min, 0, date('m', $check), date('d', $check), date('Y', $check));

	            # Compute
	            if ($result > $now || $a) {

	                # Date
	                $date = strtotime(date('Y-m-d H:i', $result));
	                $date += ($timezone[1]->getOffset(new DateTime()) - $timezone[0]->getOffset(new DateTime()));

	                # Result
	                $result = date('Y-m-d H:i', $date);

	                # Chain
	                $chain = 'reboot_' . date('dH', $date);

	                # Break
	                break;
	            }
	        }
	    }


		# Return
		if (is_null($result)) return;

        # Wait
        $this->Wait(reboot);

		# Id
		$id = (int)date('NHi');

		# Sql
		$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO chain (id, user, request, wait, start, ready) VALUES ($id, '$chain', 0, 0, 0, 0, '')");
	}*/

	public function ErrorReady($id_cache) {

		# Id
		$id = (int)date('i');

		# Request
		$request = ses_request;

		# Sql
		$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO error_ready (id, request, id_cache) VALUES ($id, '$request', '" . implode(';', $id_cache) . "')");
	}

	public function DateDiff($date_in, $date_out = 0, $offset = 0) {

		# Date out
		$date_out = ($date_out ? $date_out : ses_date);

		# Result
		$result = round((strtotime($date_out) - strtotime($date_in)) / 60) + $offset;

		# Return
		return $result;
	}

	public function Strategy($id) {

		# Result
		$result = $_SESSION[mem][strategy][$id];

		# Return
		return $result;
	}

	public function DateUnix($date, $second = false, $millisecond = false) {

		# Compute
		if ($millisecond) {

			# Millicond
			if (strlen($date) == 13) {
				$millisecond = substr($date, -3);
				$date = (int)($date / 1000);
			}

			# Format
			$format = 'Y-m-d H:i:s.' . $millisecond;

		} else {

			# Date
			if (strlen($date) == 13) $date = (int)($date / 1000);

			# Format
			$format = ($second ? 'Y-m-d H:i:s' : 'Y-m-d H:i:0');
		}

		# Result
		$result = date($format, $date);

		# Return
		return $result;
	}

	public function DateSum($minute, $date = null, $format = null) {

		# Date
		if (!$date) $date = ses_date;

		# Format
		if (!$format) $format = 'Y-m-d H:i';

		# Result
		$result = strtotime($date) + ($minute * 60);
		$result = date($format, $result);

		# Return
		return $result;
	}

	public function Chain($user, $try = 0, $exec = 0, $element = null, $action = null) {

		# Id
		$id = $_SESSION[mem][request][($action == api ? api : php)][ses_request][id];
		$id = (int)(date('NHi') . str_pad(($_SESSION[mem][user][$user][info][id] ?? 0), 2, 0, STR_PAD_LEFT) . str_pad($id, 2, 0, STR_PAD_LEFT));

		# Request
		$request = ses_request . ($try ? '_' . $try : null);

		# Exec
		$exec = ($exec ? $this->Exec($exec) : 0);

		# Start
		$start = ($exec ? max(0, $this->TimerFromZero() - $exec) : 0);

		# Sql
        $_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO chain (id, user, request, element, action, start) VALUES ($id, '$user', '$request', '$element', '$action', $start)");
	}

	public function RoundPrice($ticker, $price, $direction, $precision) {

		# Result
		$result = $price;

		# Precision
		$precision = $_SESSION[mem][ticker][$ticker][precision][$precision];

		# Result
		if ($precision) {
			if ($direction == max) {
				while (round($result, $precision) > $price) $result *= 0.9999;
			} else {
				while (round($result, $precision) < $price) $result *= 1.0001;
			}
		}

		# Result
		$result = round($result, $precision);

		# Return
		return $result;
	}

	public function Proportion($in_min, $in_max, $out_min, $out_max, $value, $fade = 1) {

		# Compute
		if ($in_min == $in_max) {

			# Result
			$result = $out_min;

		} else {

			# Value inversion
			if ($in_min > $in_max) list($in_min, $in_max, $value) = [-$in_min, -$in_max, -$value];

			# Clamp value between input range
			$value = min(max($value, $in_min), $in_max);

			# Rsult
			if ($fade < 1) {
				$progress = log(($value - $in_min) / ($in_max - $in_min) + 1) / log(2) * (1 - $fade) + (($value - $in_min) / ($in_max - $in_min)) * $fade;
			} elseif ($fade > 1) {
				$progress = pow(($value - $in_min) / ($in_max - $in_min), $fade);
			} else {
				$progress = ($value - $in_min) / ($in_max - $in_min);
			}

			# Result
			$result = round($progress * ($out_max - $out_min) + $out_min, 8);
		}

		# Return
		return $result;
	}

	public function Random($data) {

		# Result
		return (shuffle($data) ? $data : []);
	}

	public function RandomInt($lenght) {

		# Return
		if ($lenght == 1) {
			return random_int(0, 9);
		} else {
			return random_int(10 ** ($lenght - 1), (10 ** $lenght) - 1);
		}
	}

	public function Wait($request, $offset = null) {

		# Second
		if (is_numeric($request)) {
			$second = $request;
		} else {
			if ($offset) $request .= '_' . $offset;
			$second = ($_SESSION[mem][request][wait][$request] ?? 0);
		}

		# Compute
		if ((int)date('s') < $second) {

			# Data
			$data[0] = microtime(1);
			$data[1] = strtotime(date('Y-m-d H:i:' . str_pad($second, 2, 0, STR_PAD_LEFT)));
			$data[2] = (int)date('i');
			$data[3] = (int)(gmdate('i', $data[1]));

			# Result
			if ($data[2] == $data[3] && $data[1] >= $data[0]) {
				$result = round(($data[1] - $data[0]) * 1000000) + 50;
			} else {
				$result = 0;
			}

			# Sleep
			usleep($result);
		}
	}

	public function Pad($string, $pad, $left = 1) {

		# Result
		$result = (string)str_pad($string, $pad, 0, ($left ? STR_PAD_LEFT : STR_PAD_RIGHT));

		# Return
		return $result;
	}

	public function InTime($period, $offset = 0) {

		# Compute
		if ($period <= 1) {

			# Result
			$result = 1;

		} else {

			# Date
			$date_in = date('Y-m-d 00:00:00', strtotime('monday this week'));
			$date_out = $this->DateSum($offset);

			# Result
			$result = (int)$this->DateDiff($date_in);

			# Result
			$result = is_int($result / $period);
		}

		# Return
		return $result;
	}

	public function Function($function) {

		# Result
		foreach (explode('_', $function) as $a) {
			$result[] = ucfirst($a);
		}

		# Result
		$result = implode('', $result);

		# Return
		return $result;
	}

	public function FunctionName($data) {

		# Return
		return implode('', array_map('ucfirst', array_map('ucfirst', explode('_', $data))));
	}

	public function StatusName($data) {

	    # Result
	    $result = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $data));

		# Return
		return $result;
	}

	public function Pnl($price_in, $price_out, $quantity, $position) {

		# Compute
		if ($price_in != $price_out && $quantity) {

			# Result
			if ($position == long) {
				$result = round((($price_out - $price_in) * $quantity), 4);
			} else {
				$result = round((($price_in - $price_out) * $quantity), 4);
			}

		} else {

			# Result
			$result = 0;
		}

		# Return
		return $result;
	}

	public function PnlA($gain, $fee) {

		# Result
		$result = round($gain + $fee, 2);

		# Return
		return $result;
	}

	public function PnlR($size, $pnl) {

	    # Result
	    if ($size) {
	        $result = round(($pnl / $size) * 100, 4);
	    } else {
	        $result = 0;
	    }

		# Return
		return $result;
	}

	public function Slope($value_1, $value_2) {

		# Result
		if ($value_1 && $value_1 != $value_2) {
			$result = round(($value_2 - $value_1) / $value_1 * 100, 8);
		} else {
			$result = 0;
		}

		# Return
		return $result;
	}

	public function Periods($strategy) {

		# Result
		$result = 0;

		# Compute
		foreach ($strategy as $indicator => $indicator_set) {

			# Temp
			if (substr($indicator, 0, 3) == 'ma_') {
				$temp_1 = explode('_', $indicator_set)[0];
				$temp_2 = explode('_', $indicator_set)[1];
			} elseif (substr($indicator, 0, 3) == 'mac' || in_array($indicator, [rsi, volatility])) {
				$temp_1 = $indicator;
				$temp_2 = explode('_', $indicator_set)[0];
			} else {
				continue;
			}

			# Result
			$result = max($result, (int)($_SESSION[mem][indicator_set][$temp_1][period_factor] * $temp_2)) + 3;
		}

		# Return
		return $result;
	}

	public function Milliseconds($precision = 6) {

		# Compute
		list($microseconds, $seconds) = explode(' ', microtime());

		# Result
		$result = (int)(substr($microseconds * 1000, 0, $precision));

		# Result
		$result = (int)(str_pad($result, $precision, 0, STR_PAD_RIGHT));

		# Return
		return $result;
	}

	public function PrecisionIndicator($price) {

		# Result
		if ($price >= 1000) {
			$result = 3;
		} elseif ($price >= 100) {
			$result = 4;
		} elseif ($price >= 10) {
			$result = 5;
		} else {
			$result = 6;
		}

		# Return
		return $result;
	}

	public function VolumeProfile($ticker, $data_price, $data_volume, $volatility) {

		# Result
		$result = [];

		# Precision
		$precision = $_SESSION[mem][ticker][$ticker][precision][price];

		# Step
		$step = $data_price[0] / 50;
		#$step = $data_price[0] / 50, $volatility * 5;
		#$step = max($step[0], array_sum($step) / 2);

		# Compute
		for ($a=0; $a<count($data_price); $a++) {

			# Price
			$price = (string)round(round($data_price[$a] / $step) * $step, $precision);

			# Result
			$result[$price] = (isset($result[$price]) ? $result[$price] + $data_volume[$a] : $data_volume[$a]);
		}

		# Result
		$result = array_search(max($result), $result);

		# Return
		return $result;
	}

	public function Shift($date, $date_in, $date_out, $shifts) {

		# Range
		$range = (strtotime($date_out) - strtotime($date_in)) / $shifts;
		$range = floor((strtotime($date_out) - strtotime($date)) / $range) + 1;

		# Result
		$result = (($range >= 1 && $range <= $shifts) ? $range : $shifts);

		# Return
		return $result;
	}

	public function exec($exec = 0, $exec_sub = 0) {

		# Compute
		if ($exec) {
			$result = max(0.001, round((microtime(1) - $exec) - $exec_sub, 3));
		} else {
			$result = microtime(1);
		}

		# Return
		return $result;
	}

	public function TimerFromZero() {

		# Return
		return max(0.001, round(fmod(microtime(true), 60), 3));
	}

	public function Timeout() {

		# Result
		$result = (int)(date('s') >= 57);

		# Return
		return $result;
	}

	public function ShellExec($script) {

		# Echo
		if (ses_debug) echo 'shell_exec: ' . $script . xx;

	    # Result
	    $result = shell_exec("$script");

	    # Return
	    return $result;
	}

	public function Execc($script) {

		# Echo
		if (ses_debug) echo 'exec: ' . $script . xx;

	    # Result
	    $result = exec("$script");

	    # Return
	    return $result;
	}
}