<?php
class Trade_call {

	private $result = [];

	public function TradeCall($data) {

		# Compute
		foreach ($data as $user => $a) {

			# Compute
			foreach ($a as $k => $v) {
				
				# Trade get
				$trade = $this->TradeGet($v, $user);

				# Compute
				if ($trade) {

					# Trade cancel
					$this->TradeCancel($user, $trade);

					# Stop cancel
					$this->StopCancel($user, $trade);

					# Compute
					if ($_SESSION[mem][user][$user][status][short]) {

						# Trade reverse
						$this->TradeReverse($user, $trade);

					} else {

						# Trade close
						$this->TradeClose($user, $trade);

						# Trade open
						$this->TradeOpen($user, $trade);
					}

					# Stop add
					$this->StopAdd($user, $trade);
				}
			}
		}

		# Result
		$result = [];
		foreach ($this->result as $user => $a) {
			foreach ($a as $flow => $v) {
				$request = array_key_first($v);
				$result[$user][$request] = $v[$request];
			}
		}		

		# Trade call send
		$_SESSION[fun][trade_call_send]->TradeCallSend($result);

		# Cache add
		$_SESSION[fun][cache]->Add([trade_call, date('i')]);
	}

	private function TradeGet($data, $user) {

		# Result
		$result[nxt] = $data;

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tra, "SELECT id, user, chain, cluster, tf, position, status, price, quantity, quantity_book_in, quantity_position, quantity_book_out, stop_loss, take_profit FROM trade WHERE ticker = '$data[ticker]' AND status_out = '' AND date_add != '" . ses_date . "' ORDER BY date_add DESC LIMIT 1");

		# Compute
		if ($sql) {
			
		    # Result
		    while ($a = mysqli_fetch_assoc($sql)) {
		        $result[lst] = [
		            id                => (int)$a[id],
		            chain          	  => (int)$a[chain],
		            cluster        	  => (int)$a[cluster],
		            ticker        	  => $data[ticker],
		            tf                => (int)$a[tf],
		            position          => $a[position],
		            status            => $a[status],
		            price             => (float)$a[price],
		            quantity          => (float)$a[quantity],
		            quantity_book_in  => (float)$a[quantity_book_in],
		            quantity_position => (float)$a[quantity_position],
		            quantity_book_out => (float)$a[quantity_book_out],
		            stop_loss         => (float)$a[stop_loss],
		            take_profit       => (float)$a[take_profit]
		        ];
		    }
		}

		# Return
		return $result;
	}

	private function TradeCancel($user, $data) {

		# Request
		$request = trade_cancel;

		# Flow
		$flow = $_SESSION[mem][request][api][$request][flow];

		# Continue
		if (!isset($data[nxt])) return;
		if (!isset($data[lst])) return;

		# Result
		$this->result[$user][$flow][$request][] = [
			id    	 => $data[lst][id],
			chain    => $data[lst][chain],
			cluster  => $data[lst][cluster],
			ticker   => $data[lst][ticker],
			tf       => $data[lst][tf],
			position => $data[lst][position],
			action   => null,
			price  	 => 0,
			quantity => $data[lst][quantity_book_in]
		];

		# Sql
    	$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$data[lst][id]}, '$user', {$data[lst][chain]}, {$data[lst][cluster]}, '{$data[lst][ticker]}', {$data[lst][tf]}, '{$data[lst][position]}', $flow, '$request', 0, {$data[lst][quantity_book_in]}, '" . ses_date . "')");
	}

	private function TradeReverse($user, $data) {

		# Request
		$request = trade_add;

		# Flow
		$flow = $_SESSION[mem][request][api][$request][flow];

		# Continue
		if (!isset($data[nxt])) return;
		if (!in_array($data[lst][status], [filled_in_part, filled_in])) return;

		# Quantity
		$quantity = $data[nxt][quantity] + $data[lst][quantity_position];

		# Result
		$this->result[$user][$flow][$request][] = [
			id    	 => $data[nxt][id],
			chain    => $data[nxt][chain],
			cluster  => $data[nxt][cluster],
			ticker   => $data[nxt][ticker],
			tf       => $data[nxt][tf],
			position => $data[nxt][position],
			action   => ($data[nxt][position] == long ? BUY : SELL),
			price  	 => $data[nxt][price],
			quantity => $quantity
		];		

		# Sql
		$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$data[nxt][id]}, '$user', {$data[nxt][chain]}, {$data[nxt][cluster]}, '$data[nxt][ticker]', {$data[nxt][tf]}, '{$data[nxt][position]}', $flow, '$request', {$data[nxt][price]}, $quantity, '" . ses_date . "')");
	}

	private function TradeClose($user, $data) {

		# Request
		$request = trade_close;

		# Flow
		$flow = $_SESSION[mem][request][api][$request][flow];

		# Continue
		if (!isset($data[nxt])) return;
		if (!isset($data[lst])) return;
		if (!$data[lst][quantity_position]) return;

		# Result
		$this->result[$user][$flow][$request][] = [
			id    	 => $data[lst][id],
			chain    => $data[lst][chain],
			cluster  => $data[lst][cluster],
			ticker   => $data[lst][ticker],
			tf       => $data[lst][tf],
			position => $data[lst][position],
			action   => null,
			price  	 => $data[lst][price],
			quantity => $data[lst][quantity_position]
		];		

		# Sql
		$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$data[lst][id]}, '$user', {$data[lst][chain]}, {$data[lst][cluster]}, '$data[lst][ticker]', {$data[lst][tf]}, '{$data[lst][position]}', $flow, '$request', {$data[nxt][price]}, {$data[lst][quantity_position]}, '" . ses_date . "')");
	}

	private function TradeOpen($user, $data) {

		# Request
		$request = trade_add;

		# Flow
		$flow = $_SESSION[mem][request][api][$request][flow];

		# Continue
		if (!isset($data[nxt])) return;
		if (!$data[nxt][quantity]) return;
		
		# Result
		$this->result[$user][$flow][$request][] = [
			id    	 => $data[nxt][id],
			chain    => $data[nxt][chain],
			cluster  => $data[nxt][cluster],
			ticker   => $data[nxt][ticker],
			tf       => $data[nxt][tf],
			position => $data[nxt][position],
			action   => ($data[nxt][position] == long ? BUY : SELL),
			price  	 => $data[nxt][price],
			quantity => $data[nxt][quantity]
		];

		# Sql
		$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$data[nxt][id]}, '$user', {$data[nxt][chain]}, {$data[nxt][cluster]}, '{$data[nxt][ticker]}', {$data[nxt][tf]}, '{$data[nxt][position]}', $flow, '$request', {$data[nxt][price]}, {$data[nxt][quantity]}, '" . ses_date . "')");
	}

	private function StopCancel($user, $data) {

		# Compute
		foreach ([stop_loss, take_profit] as $stop) {

			# Request
			$request = $stop . '_' . cancel;

			# Flow
			$flow = $_SESSION[mem][request][api][$request][flow];

			# Continue
			if (!isset($data[nxt])) continue;
			if (!isset($data[lst])) continue;
			if (!$data[lst][$stop]) continue;

			# Result
			$this->result[$user][$flow][$request][] = [
				id    	 => $data[lst][id],
				chain    => $data[lst][chain],
				cluster  => $data[lst][cluster],
				ticker   => $data[lst][ticker],
				tf       => $data[lst][tf],
				position => $data[lst][position],
				action   => null,
				price  	 => 0,
				quantity => 0
			];			

			# Sql
			$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$data[lst][id]}, '$user', {$data[lst][chain]}, {$data[lst][cluster]}, '$data[lst][ticker]', {$data[lst][tf]}, '{$data[lst][position]}', $flow, '$request', 0, 0, '" . ses_date . "')");
		}
	}

	private function StopAdd($user, $data) {

		# Compute
		foreach ([stop_loss, take_profit] as $stop) {

			# Request
			$request = $stop . '_' . add;

			# Flow
			$flow = $_SESSION[mem][request][api][$request][flow];

			# Continue
			if (!isset($data[nxt])) continue;
			if (!$data[nxt][$stop]) continue;

			# Action
			if ($stop == stop_loss) {
				$action = ($data[nxt][position] == long ? SELL : BUY);
			} else {
				$action = ($data[nxt][position] == long ? BUY : SELL);
			}
			
			# Result
			$this->result[$user][$flow][$request][] = [
				id    	 => $data[nxt][id],
				chain    => $data[nxt][chain],
				cluster  => $data[nxt][cluster],
				ticker   => $data[nxt][ticker],
				tf       => $data[nxt][tf],
				position => $data[nxt][position],
				action   => $action,
				price  	 => $data[nxt][$stop],
				quantity => 0
			];				

			# Sql
			$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$data[nxt][id]}, '$user', {$data[nxt][chain]}, {$data[nxt][cluster]}, '$data[nxt][ticker]', {$data[nxt][tf]}, '{$data[nxt][position]}', $flow, '$request', {$data[nxt][$stop]}, 0, '" . ses_date . "')");
		}
	}

	/*private function StopUpdate($user, $data) {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tra, "SELECT id, user, chain, cluster, price, ticker, tf, position, status, quantity, quantity_book_in, quantity_position, quantity_book_out, stop_loss, take_profit, date_add FROM trade ORDER BY ticker ASC, date_add DESC");

		# Result
		if ($sql) {
			while ($a = mysqli_fetch_assoc($sql)) {

		# Compute
		foreach ([stop_loss, take_profit] as $stop) {

			# Compute
			foreach ([cancel, add] as $action) {

				# Request
				$request = $stop . '_' . $action;

				# Flow
				$flow = $_SESSION[mem][request][api][$request][flow];

				# Compute
				foreach ($data as $ticker => $a) {

					# Continue
					if (!$a[nxt][$stop]) continue;

					# Sql
					$_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO trade_call (id, user, chain, cluster, ticker, tf, position, flow, request, price, quantity, date) VALUES ({$a[nxt][id]}, {$a[nxt][chain]}, {$a[nxt][cluster]}, '$ticker', {$a[nxt][tf]}, '{$a[nxt][position]}', $flow, '$request', {$a[nxt][$stop]}, 0, '" . ses_date . "')");
				}
			}
		}
	}*/
}