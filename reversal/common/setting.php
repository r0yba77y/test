<?php
class Setting {

	public function __construct() {

		# Return
		if (isset($_SESSION[mem])) return;

		# NON CAMBIARE ORDINE

		# Running
		$_SESSION[mem][status][request] = setting;

		# Connection
		$this->Connection();

		# Market holiday
		$this->MarketHoliday();

		# Market hours
		$this->MarketHours();

		# Token
		$this->Token();

		# Setting
		$this->Setting();

		# User
		$this->User();	

		# Intime
		$this->Intime();

		# Compute
		if ($_SESSION[mem][status][intime]) {

			# Ticker tf
			$this->TickerTf();

			# Strategy
			$this->Strategy();

			# Currency
			$this->Currency();

			# Ticker
			$this->Ticker();

			# Ticker balance
			$this->TickerBalance(false);

			# Indicator
			$this->Indicator();
		}

		# Tickers
		$this->Tickers();

		# Tickers tf
		$this->TickersTf();

		# Market
		$this->Market();

		# Request
		$this->Request();

		# Request wait
		$this->RequestWait();

		# Maintenance
		$this->Maintenance();

		# Software
		$this->Software();

		# Demo
		$this->Demo();

		# Running
		$_SESSION[mem][status][request] = ses_request;

		# Sort
		ksort($_SESSION[mem]);
	}

	private function Demo() {

		# Result
		if ($_SESSION[mem][setting][demo]) {
			$_SESSION[mem][status][connection] = 1;
			$_SESSION[mem][status][market][now] = 1;
			$_SESSION[mem][status][market][nxt] = 1;
		}
	}

	private function Connection() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select1(d_log, "SELECT IFNULL(connection, 0) AS connection FROM connection WHERE date = '" . ses_date . "'");

		# Result
		$_SESSION[mem][status][connection] = (int)$sql;
	}

	private function Token() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT token, value FROM token");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
			if (ctype_digit($a[value])) {
		    	$_SESSION[mem][token][$a[token]] = (int)$a[value];
			} elseif (is_numeric($a[value])) {
		    	$_SESSION[mem][token][$a[token]] = (float)$a[value];
			} else {
		    	$_SESSION[mem][token][$a[token]] = $a[value];
			}
		}
	}

	private function Setting() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT setting, value FROM setting");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
			if (ctype_digit($a[value])) {
		    	$_SESSION[mem][setting][$a[setting]] = (int)$a[value];
			} elseif (is_numeric($a[value])) {
		    	$_SESSION[mem][setting][$a[setting]] = (float)$a[value];
			} else {
		    	$_SESSION[mem][setting][$a[setting]] = $a[value];
			}
		}
	}

	private function Ticker() {

		# Tf
		$tf = implode(', ', $_SESSION[mem][ticker_tf]);

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tic, "SELECT t.id, tu.ticker, t.priority, t.prec_price, t.prec_quantity, st.tf, st.strategy, t.bid, t.ask FROM ticker_user AS tu JOIN `" . d_str . "`.strategy_ticker AS st ON st.ticker = tu.ticker JOIN ticker AS t ON t.ticker = tu.ticker  WHERE tu.status = 1 AND st.tf IN ($tf) ORDER BY st.tf ASC, t.priority ASC");
		if (!$sql) return;

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][ticker][$a[ticker]] = [
	        	id        => (int)$a[id],
		        tf        => $a[tf],
		        bid       => (float)$a[bid],
		        ask       => (float)$a[ask],
		        strategy  => $a[strategy],
	            priority  => (int)$a[priority],
		        precision => [
		            price    => (int)$a[prec_price],
		            quantity => (int)$a[prec_quantity]
		        ]
		    ];
		}
	}

	private function TickerBalance() {

		# User
		$user = implode("', '", array_keys($_SESSION[mem][user]));

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tic, "SELECT tu.ticker, tu.user, tu.size_min, tu.size_max, tu.weight, tb.allocation, tb.locked, tb.available_long AS `long`, tb.available_short AS short, COALESCE((SELECT balance FROM `" . d_use . "`.user_balance WHERE user = tu.user AND balance > 0 ORDER BY date DESC LIMIT 1), 0) AS balance FROM ticker_user AS tu JOIN (SELECT ticker, MAX(date) AS max_date FROM `" . d_can . "`.candle GROUP BY ticker) AS c2 ON tu.ticker = c2.ticker LEFT JOIN (SELECT tb1.* FROM ticker_balance tb1 INNER JOIN (SELECT ticker, user, MAX(date) AS max_date FROM ticker_balance GROUP BY ticker, user) tb2 ON tb1.ticker = tb2.ticker AND tb1.user = tb2.user AND tb1.date = tb2.max_date) AS tb ON tb.ticker = tu.ticker AND tb.user = tu.user WHERE tu.status = 1 AND tu.user IN ('$user') AND EXISTS (SELECT 1 FROM `" . d_use . "`.user AS u WHERE u.user = tu.user AND u.status = 1) AND COALESCE((SELECT balance FROM `" . d_use . "`.user_balance WHERE user = tu.user AND balance > 0 ORDER BY date DESC LIMIT 1), 0) > 0");
		if (!$sql) return;

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
			$_SESSION[mem][user][$a[user]][ticker][$a[ticker]] = [
		        weight     => (float)$a[weight],		
		        size_min   => $a[size_min],
		        size_max   => $a[size_max],
				allocation => $a[allocation],
				locked     => $a[locked],
				available  => [
					long  => ($_SESSION[mem][user][$a[user]][status][long] ? $a[long] : 0),
					short => ($_SESSION[mem][user][$a[user]][status][short] ? $a[short] : 0)
				]
			];
		}
	}

	private function Tickers() {
	    
	    # Sql
	    $sql = $_SESSION[fun][sql]->Select(d_tic, "SELECT tu.ticker, t.id FROM ticker_user AS tu JOIN `" . d_str . "`.strategy_ticker AS st ON st.ticker = tu.ticker JOIN ticker AS t ON t.ticker = tu.ticker WHERE tu.status = 1");
	    if (!$sql) return;
	   
	    # Result
	    while ($a = mysqli_fetch_assoc($sql)) {
	        $_SESSION[mem][tickers][$a[ticker]] = (int)$a[id];
	    }
	}			

	private function TickersTf() {

		# Result
		$_SESSION[mem][tickers_tf] = [];

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_str, "SELECT DISTINCT tf FROM strategy_ticker");
		if (!$sql) return;

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][tickers_tf][] = $a[tf];
		}
	}

	private function MarketHoliday() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT date, hour FROM market_holiday WHERE date >= '" . date('Y-m-d') . "'");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][setting][market][holiday][$a[date_]] = $a[hour];
		}
	}

	private function MarketHours() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT id, name, start, end FROM market_hours");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][setting][market][hours][$a[id]] = [
		    	name  => $a[name],
		    	start => (int)$a[start],
		    	end   => (int)$a[end]
		    ];
		}
	}

	private function Market() {

    	# Holiday
    	$holiday = $this->Holiday();

	    # Compute
	    foreach ([now, nxt] as $offset => $temp) {

	    	# Result
	    	$result = 0;

		    # Date
		    $date = $_SESSION[fun][tool]->DateSum($offset);

		    # Compute
		    if ($this->DayNY($date) && $holiday) {

		    	# Hour
		    	$hour = $this->HourNY($date);

			    # Result
			    foreach ($_SESSION[mem][setting][market][hours] as $id => $v) {
			        if ($hour >= $v[start] && $hour < $v[end]) {
			            $result = $id;
			            break;
			        }
			    }
		    }

			# Result
			$_SESSION[mem][status][market][$temp] = $result;
	    }
	}

	private function Maintenance() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT request, CONCAT(value, ' ', unit) AS `interval`, `limit` FROM maintenance");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
			$_SESSION[mem][setting][maintenance][$a[request]][interval] = $a[interval];
			if ($a[limit]) $_SESSION[mem][setting][maintenance][$a[request]][limit] = $a[limit];
		}
	}

	private function Currency() {

		# Result
		$_SESSION[mem][currency][stable][account] = EUR;
		$_SESSION[mem][currency][stable][trade] = USD;

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_can, "SELECT t.pair, t.value FROM forex AS t INNER JOIN (SELECT pair, MAX(date) AS date_max FROM forex GROUP BY pair) AS date_max ON t.pair = date_max.pair AND t.date = date_max.date_max");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
			$_SESSION[mem][currency][forex][$a[pair]] = (float)$a[value];
		}
	}

	private function User() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_use, "SELECT u.user, u.id, u.name, u.alias, u.type, u.host, u.port, u.`call`, u.trade, u.long, u.short, u.demo, ub.balance, ub.locked, ub.available FROM user AS u LEFT JOIN (SELECT ub1.user, ub1.balance, ub1.locked, ub1.available FROM user_balance AS ub1 INNER JOIN (SELECT user, MAX(date) AS max_date FROM user_balance GROUP BY user) AS ub2 ON ub1.user = ub2.user AND ub1.date = ub2.max_date) AS ub ON u.user = ub.user WHERE u.status = 1 ORDER BY u.id");
		if (!$sql) return;

	    # Result
	    while ($a = mysqli_fetch_assoc($sql)) {
	        $_SESSION[mem][user][$a[user]] = [
	            info => [
	                id    => $a[id],
	                name  => $a[name],
	                alias => $a[alias],
	                type  => $a[type],
	                host  => $a[host],
	                port  => $a[port]
	            ],
	            status => [
	                call  => min($a[call], $_SESSION[mem][setting][call]),
	                trade => min($a[trade], $_SESSION[mem][setting][trade]),
	                long  => min($a[long], $_SESSION[mem][setting][long]),
	                short => min($a[short], $_SESSION[mem][setting][short]),
	                demo  => min($a[demo], $_SESSION[mem][setting][demo])
	            ],
	            stable => [
	                balance   => (float)$a[balance],
	                locked    => (float)$a[locked],
	                available => (float)$a[available]
	            ]
	        ];
	    }
	}

	private function Strategy() {

		# Return
		if (!$_SESSION[mem][ticker_tf]) return;

		# Tf
		$tf = implode(', ', $_SESSION[mem][ticker_tf]);

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tic, "SELECT st.ticker, st.tf, st.strategy AS id, s.stop_loss, s.take_profit, s.offset FROM `" . d_str . "`.strategy_ticker AS st JOIN ticker_user AS tu ON st.ticker = tu.ticker LEFT JOIN `" . d_str . "`.strategy AS s ON st.strategy = s.strategy WHERE st.tf IN ($tf) ORDER BY st.tf ASC, st.ticker ASC");

		# Compute
		while ($a = mysqli_fetch_assoc($sql)) {
			if ($a[stop_loss] > 0) $_SESSION[mem][strategy][$a[id]][stop_loss] = (float)$a[stop_loss];
			if ($a[take_profit] > 0) $_SESSION[mem][strategy][$a[id]][take_profit] = (float)$a[take_profit];
			if ($a[offset] > 0) $_SESSION[mem][strategy][$a[id]][offset] = (float)$a[offset];
		}
	}

	private function Intime() {

		# Sql
		$result = $_SESSION[fun][sql]->Select1(d_tic, "SELECT EXISTS (SELECT 1 FROM `" . d_str . "`.strategy_ticker AS st JOIN ticker_user AS tu ON st.ticker = tu.ticker WHERE tu.status = 1 AND MOD(MINUTE(NOW()), st.tf) = 0) AS x");

		# Result
		$_SESSION[mem][status][intime] = $result;
	}

	private function TickerTf() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tic, "SELECT DISTINCT tf FROM `" . d_str . "`.strategy_ticker AS st JOIN ticker_user AS tu ON st.ticker = tu.ticker WHERE tu.status = 1 AND MOD(MINUTE(NOW()), st.tf) = 0 ORDER BY tf ASC");

		# Result
		$_SESSION[mem][ticker_tf] = [];
		while ($a = mysqli_fetch_assoc($sql)) {
			$_SESSION[mem][ticker_tf][] = $a[tf];
		}
	}

	private function Indicator() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT indicator, id FROM indicator ORDER BY id ASC");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][indicator][$a[indicator]] = $a[id];
		}
	}

	private function Request() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT request, type, id, folder, file, flow, id_client, ready FROM request WHERE type = 'php'");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][request][php][$a[request]] = [
				id     => (int)$a[id],
				folder => $a[folder],
				file   => $a[file],
				ready  => (int)$a[ready]
			];
		}

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT request, type, id, folder, file, flow, id_client, ready FROM request WHERE type = 'api'");		

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
			$_SESSION[mem][request][api][$a[request]] = [
				id        => (int)$a[id],
				folder    => $a[folder],
				file   	  => $a[file],
				flow   	  => (int)$a[flow],
				id_client => (int)$a[id_client]
			];
		}
	}

	private function RequestWait() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_set, "SELECT * FROM (SELECT CASE WHEN `offset` = 0 THEN request ELSE CONCAT(request, '_', `offset`) END AS request, CAST(priority / 10 AS INT) AS priority FROM request_chain) AS x WHERE priority > 0 ORDER BY priority ASC");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) {
		    $_SESSION[mem][request][wait][$a[request]] = $a[priority];
		}
	}

	private function Software() {

		# Result
		$_SESSION[mem][setting][software] = [
			name 	=> 'cron',
			fork 	=> 'webhook',
			version => '2026.03.12'
		];
	}

	private function DateNY($date = null) {

		# Date
	    $date = new DateTime(is_null($date) ? ses_date : $date);
	    $date->setTimezone(new DateTimeZone('America/New_York'));

	    # Return
	    return $date;
	}

	private function HourNY($date) {

		# Date
	    $date = $this->DateNY($date);

	    # Result
	    $result = (int)$date->format('Hi');

	    # Return
	    return $result;
	}

	private function DayNY($date = null) {

		# Date
	    $date = $this->DateNY($date);

	    # Result
	    $result = ((int)$date->format('N') <= 5);

	    # Return
	    return $result;
	}

	private function Holiday() {

		# Date
	    $date = $this->DateNY();

	    # Result
	    $result = !in_array($date->format('Y-m-d'), array_keys($_SESSION[mem][setting][market][holiday]));

	    # Return
	    return $result;
	}
}