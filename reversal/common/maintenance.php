<?php
class Maintenance {

	private $tickers;
	private $tfs;
	
	public function Exec() {

		# Market
		if ($_SESSION[mem][status][market][now]) {

			# Tickers
			$this->tickers = implode("', '", array_keys($_SESSION[mem][tickers]));

			# Tfs
			$this->tfs = implode(', ', $_SESSION[mem][tickers_tf]);

			# Wait
		   	$_SESSION[fun][tool]->Wait(ses_request);

			# Exec
			$exec = $_SESSION[fun][tool]->Exec();

			# Optimize
			$this->Optimize();

			# Server
			$this->Server();

			# Server connectivity
			$this->ServerConnectivity();

	   		# Candle
	   		$this->Candle();

	   		# Candle volume
	   		$this->CandleVolume();  		

	   		# Trade
	   		$this->Trade();

	   		# Trade call
	   		$this->TradeCall();

	   		# Ticker
	   		$this->Ticker();

	   		# Balance
	   		$this->Balance();

	   		# Chain
	   		$this->Chain();

	   		# Error
	   		$this->Error();

			# Connection
			$this->Connection();

			# Chain
			$_SESSION[fun][tool]->Chain(null, 0, $exec);
		}
	}

	private function Optimize() {

		# Intime
		$intime = [date('i'), abs(date('i') - 30)];

		# Compute
		foreach ([d_tic, d_use, d_can, d_tra, d_log, d_set] as $database) {

			# Continue
			if (!in_array((int)$database, $intime)) continue;

			# Exec
			$exec = $_SESSION[fun][tool]->Exec();

			# Optimaze
		    $_SESSION[fun][sql]->Optimize($database);

		    # Chain
			$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, $database, optimize);
		}
	}	

	private function Candle() {

		# Sql
		$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM candle WHERE ticker NOT IN ('$this->tickers') AND date < (NOW() - INTERVAL 1 DAY)");	

		# Delete
		$this->DeleteInterval(d_can, candle);		
	}

	private function CandleVolume() {

		# Limit
		$limit = $_SESSION[mem][setting][maintenance][candle_volume][limit];

		# Sql
		$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM candle_volume WHERE ticker NOT IN ('$this->tickers')");

		# Compute
		foreach (array_keys($_SESSION[mem][tickers]) as $ticker) {
	
			# Tf
			$tf = ($_SESSION[mem][ticker][$ticker][tf] ?? null);
			if (!$tf) continue; 

			# Sql
			$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM candle_volume WHERE ticker = '$ticker' AND tf NOT IN ($tf)");

			# Sql
			$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM candle_volume WHERE ticker = '$ticker' AND date < (SELECT date FROM (SELECT date FROM candle_volume WHERE ticker = '$ticker' ORDER BY date DESC LIMIT $limit, 1) x)");
		}
	}	

	private function Trade() {
		
		# Delete
		#$this->DeleteInterval(d_tra, trade, 'date_update');
		#$this->DeleteLimit(d_tra, trade, 'date_update');

		# Limit
		$limit = ($_SESSION[mem][setting][maintenance][trade][limit] ?? 0);

		# Sql
		#$_SESSION[fun][sql]->Delete(d_tra, "DELETE FROM trade WHERE date_update NOT IN (SELECT date_update FROM (SELECT date_update FROM trade ORDER BY date_update DESC LIMIT $limit) AS x)");
	}

	private function TradeCall() {

		# Sql
		$_SESSION[fun][sql]->Delete(d_tra, "DELETE FROM trade_call WHERE ticker NOT IN ('$this->tickers')");

		# Sql
		$_SESSION[fun][sql]->Delete(d_tra, "DELETE FROM trade_call WHERE tf NOT IN ($this->tfs)");		

		# Delete
		$this->DeleteInterval(d_log, call);
		$this->DeleteLimit(d_log, call);

		# Delete
		$this->DeleteInterval(d_tra, trade_call);
		$this->DeleteLimit(d_tra, trade_call);
	}

	private function Error() {

		# Delete
		foreach ([error, error_ready] as $a) {
			$this->DeleteInterval(d_log, $a);
			$this->DeleteLimit(d_log, $a);
		}
	}
	
	private function Ticker() {

		# Delete
		$this->DeleteInterval(d_tic, ticker_balance);
	}

	private function Balance() {

		# Delete
		$this->DeleteInterval(d_use, user_balance);
	}

	private function Chain() {

		# Delete
		$this->DeleteInterval(d_log, chain);
	}

	private function Connection() {

		# Delete
		$this->DeleteInterval(d_log, alive);
		$this->DeleteInterval(d_log, connection);
	}

	private function Server() {

		# Id
		$id = (int)date('NHi');

		# Load
		$load = $this->Load();

		# Ram
		$ram = $this->Ram();

		# Ram cache
		$ram_cache = $_SESSION[fun][cache]->Memory();

		# Temperature
		$temperature = $this->Temperature();

		# Uptime
		$uptime = $this->Uptime();

		# Sql
		$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO server (id, load_1, load_5, load_15, load_60, ram, ram_cache, temp_1, temp_2, uptime, date) VALUES ($id, $load[1], $load[5], $load[15], $load[60], $ram, $ram_cache, $temperature[0], $temperature[1], $uptime, '" . ses_date . "')");

		# Delete
		$this->DeleteInterval(d_log, server);
	}

	private function ServerConnectivity() {

		# Id
		$id = (int)date('NHi');

		# Ip
		$ip = $this->Ip();

		# Pings
		$pings = $this->Pings();

		# Delete
		$this->DeleteInterval(d_log, server_connectivity);

		# Sql
		$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO server_connectivity (id, ip, ethernet, wireless, fttc, 4g, date) VALUES ($id, '$ip', {$pings[1][lan]}, {$pings[2][lan]}, {$pings[1][wan]}, {$pings[2][wan]}, '" . ses_date . "')");
	}

	private function Load() {

		# Data
		$data = sys_getloadavg();

		# Data 60
		$data_60 = $_SESSION[fun][sql]->Select1(d_log, "SELECT ROUND(AVG(load_1), 2) FROM (SELECT load_1 FROM server WHERE date >= NOW() - INTERVAL 1 HOUR) AS a");

		# Result
		$result = [
			1 	=> round($data[0], 2),
			5 	=> round($data[1], 2),
			15 	=> round($data[2], 2),
			60 	=> ($data_60 ?? 0)
		];

		# Return
		return $result;
	}

	private function Ram() {

		# Result
		$result = 0;

		# Data
		$data = explode("\n", file_get_contents('/proc/meminfo'));

		# Compute
		if (is_array($data)) {

			# Data
			$data = [
				preg_replace('/[^0-9]/', '', $data[1]),
				preg_replace('/[^0-9]/', '', $data[0])
			];

			# Result
			if ($data[1]) $result = abs(100 - round(($data[0] / $data[1]) * 100, 2));
		}

		# Return
		return $result;
	}

	private function Temperature() {

		# Data
		$data = json_decode($_SESSION[fun][tool]->ShellExec('sensors --no-adapter -j'), true)['k10temp-pci-00c3'];

		# Result
		$result = [
			round($data['Tctl']['temp1_input'], 2),
			round($data['Tccd1']['temp3_input'], 2)
		];

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_log, "SELECT SUM(temp_1) AS '0', SUM(temp_2) AS '1', COUNT(*) + 1 AS count FROM server WHERE date >= NOW() - INTERVAL 4 MINUTE ORDER BY date DESC LIMIT 4");
		if ($sql) {
			$result = [
				round(($result[0] + $sql[0]) / $sql[count], 2),
				round(($result[1] + $sql[1]) / $sql[count], 2)
			];
		}

		# Return
		return $result;
	}

	private function Uptime() {

		# Data
		$data = $_SESSION[fun][tool]->Execc('cat /proc/uptime');

		# Result
		$result = round((int)(substr($data, 0, strpos($data, '.'))) / 86400, 2);

		# Return
		return $result;
	}

	private function Ip() {

		# Urls
		$urls = $_SESSION[fun][tool]->Random([
	        'https://ifconfig.me/ip',
	        'https://api.ipify.org',
	        'https://api.myip.la'
	    ]);

		# Set
		$set = stream_context_create(['http' => ['timeout' => 2]]);

		# Compute
		foreach ($urls as $url) {

			# Result
	        $result = @file_get_contents($url, false, $set);
	        if ($result !== false && trim($result) !== '') break;

    		# Sleep
    		usleep(100000);
    	}

		# Return
		return $result;
	}

	public function Pings() {

		# Set
		$set = [
			1 => [
				name => 'enp9s0',
				lan  => '192.168.1.100',
				wan  => '1.1.1.1'
			],
			2 => [
				name => 'enp9s0',
				lan  => '192.168.1.100',
				wan  => '1.1.1.1'
			]
		];

		# Pattern
		$pattern = "/time=([0-9]+\.[0-9]+)/";

		# Compute
		foreach ($set as $k => $v) {
	
			# Compute
			foreach ([lan, wan] as $a) {

				# Temp
				$temp = [];

				# Compute
				foreach (range(1, 2) as $b) {

					# Exec
					exec('ping -c 1 -I ' . escapeshellarg($v[name]) . ' ' . escapeshellarg($v[$a]), $data, $error);

					# Found
					$found = 0;
					foreach ($data as $line) {
						if (preg_match($pattern, $line, $match)) {
							$temp[] = (float) $match[1];
							$found = 1;
							break;
						}
					}
					if (!$found) $temp[] = 0;

					# Unset
					unset($data);

					# Wait
					usleep(50000);
				}

				# Result
				$result[$k][$a] = round(array_sum($temp) / 2, ($a == lan ? 3 : 0));
			}
		}

		# Return
		return $result;
	}

	private function DeleteInterval($database, $table, $column = date_) {

		# Interval
		$interval = $_SESSION[mem][setting][maintenance][$table][interval];
		if ((int)$interval == 0) return;

		# Sql
		$_SESSION[fun][sql]->Delete($database, "DELETE FROM `$table` WHERE $column < (NOW() - INTERVAL $interval)");
	}

	private function DeleteLimit($database, $table, $column = 'date', $limit = 0) {

		# Limit
		$limit = ($limit ? $limit : $_SESSION[mem][setting][maintenance][$table][limit]);

		# Sql
		$_SESSION[fun][sql]->Delete($database, "DELETE FROM `$table` WHERE $column NOT IN (SELECT $column FROM (SELECT $column FROM `$table` ORDER BY $column DESC LIMIT $limit) AS x)");
	}
}