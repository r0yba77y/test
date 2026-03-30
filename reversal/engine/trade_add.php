<?php
class Trade_add {

	public function Exec() {

		# Response
		$this->Response();

		# Connection
		if ($_SESSION[mem][status][connection]) {

			# Market
			if ($_SESSION[mem][status][market][now]) {

				# Intime
				if ($_SESSION[mem][status][intime]) {

					# Compute
					if ($_SERVER['REQUEST_METHOD'] == POST) {

						# Signal add
						$signal = $this->SignalAdd();

						# Compute
						if ($_SESSION[fun][cache]->Ready(candle)) {

							# Compute
							if ($signal) {
								
								# Trade add
								$trade = $this->TradeAdd($signal);

								# Compute
								if ($trade) {

									# Trade call
									$_SESSION[fun][trade_call]->TradeCall($trade);
								}
							}
						}
					}
				}
			}
		}
	}

	private function Response() {

		# Compute
		header('Content-Type: text/plain');
		http_response_code(200);
		echo 1;
		if (ob_get_level()) ob_flush();
		flush();
	}

	private function SignalAdd() {

		# Result
		$result = null;		

		# Data
		$data = json_decode(file_get_contents("php://input"), true);

		# Compute
		if ($data) {

			# Id
			$id = (int)date('dHi');

			# Ip
			$ip = $_SERVER['REMOTE_ADDR'];
			
			# Exec
			$exec = $_SESSION[fun][tool]->TimerFromZero();

			# Compute
			if (substr($ip, 0, 7) == '192.168' || $ip == $_SESSION[mem][setting][ip]) {

				# Set
				$ip = localhost;
				$exec = 0;
				$exec_avg = 0;
				$status = 0;

			} else {

				# Exec avg
				$exec_avg = $_SESSION[fun][sql]->Select1(d_tra, "SELECT ROUND(AVG(exec_avg), 2) FROM ((SELECT exec AS exec_avg FROM `signal` WHERE ip != 'localhost' ORDER BY date DESC LIMIT 30) UNION ALL SELECT $exec) AS t");

				# Status
				$status = (int)in_array($data[ticker], array_keys($_SESSION[mem][tickers]));
				$status = 1;
			}

			# Sql
	        $_SESSION[fun][sql]->Insert(d_tra, "INSERT IGNORE INTO `signal` (id, ticker, tf, position, ip, exec, exec_avg, status, date) VALUES ($id, '$data[ticker]', $data[tf], '$data[position]', '$ip', $exec, $exec_avg, $status, '" . ses_date . "')");

	        # Result
	        if ($status) {
		        $result = [
		        	id       => $id,
		        	ticker   => $data[ticker],
		        	tf 		 => $data[tf],
		        	position => $data[position]
		        ];
	        }

			# Chain
			$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, webhook, write);
		}

		# Return
		return $result;
	}

	private function TradeAdd($data) {

		# Result
		$result = [];

		# Strategy
		$strategy = $_SESSION[mem][ticker][$data[ticker]][strategy] ?? null;

		# Compute
		if ($strategy) {

			# User
			foreach ($_SESSION[mem][user] as $user => $v) {

				# Ticker ?
				if (!isset($v[ticker][$data[ticker]])) continue;

				# Sql
				$sql = $_SESSION[fun][sql]->Select1(d_tra, "SELECT EXISTS (SELECT 1 FROM trade WHERE user = '$user' AND ticker = '$data[ticker]' AND position = '$data[position]' AND status_out = '')");
				#if ($sql) continue;

				# Id
				$id = (int)($data[id] . str_pad($v[info][id], 2, 0, STR_PAD_LEFT) . str_pad($_SESSION[mem][ticker][$data[ticker]][id], 2, 0, STR_PAD_LEFT));

				# Chain
				$chain = (int)($_SESSION[mem][tickers][$data[ticker]] . str_pad($data[tf], 2, 0, STR_PAD_LEFT));

				# Price
				$price = $_SESSION[mem][ticker][$data[ticker]][ask];
				$price = $_SESSION[fun][tool_trade]->PriceOffset($data[ticker], $data[position], $price, $strategy);

				# Quantity
				$quantity = $v[ticker][$data[ticker]][available][$data[position]];

				# Compute
				if ($quantity) {

					# Status
					$status = null;
					
					# Status out
					$status_out = null;

					# Size
					$size = round($price * $quantity, 2);

					# Stop loss
					$stop_loss = $_SESSION[fun][tool_trade]->StopLoss($data[ticker], $data[position], $price, $strategy);

					# Take profit
					$take_profit = $_SESSION[fun][tool_trade]->TakeProfit($data[ticker], $data[position], $price, $strategy);	        

				} else {

					# Status
					$status = completed;
					
					# Status out
					$status_out = ($_SESSION[mem][user][$user][status][$data[position]] ? quantity : position);

					# Size
					$size = 0;

					# Stop loss
					$stop_loss = 0;

					# Take profit
					$take_profit = 0;
				}

				# Result
		        $result[$user][] = [
		            id    		=> $id,
		            chain    	=> $chain,
		            cluster  	=> 0,
		            ticker      => $data[ticker],
		            tf          => $data[tf],
		            position    => $data[position],
		            status      => $status,
		            price       => $price,
		            quantity    => $quantity,
		            stop_loss   => $stop_loss,
		            take_profit => $take_profit
		        ];					

				# Sql
				$_SESSION[fun][sql]->Insert(d_tra, "INSERT INTO trade (id, user, chain, ticker, tf, strategy, position, price, quantity, size, status, status_out, stop_loss, take_profit, date_add) VALUES ($id, '$user', $chain, '$data[ticker]', $data[tf], $strategy, '$data[position]', $price, $quantity, $size, '$status', '$status_out', $stop_loss, $take_profit, '" . ses_date . "') ON DUPLICATE KEY UPDATE id = id");

				# Chain
				$_SESSION[fun][tool]->Chain($user, 0, 0, trade, write);
			}
		}

		# Return
		return $result;
	}	
}