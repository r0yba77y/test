<?php
class Trade_order {

	public function Exec() {

		# Connection
		if ($_SESSION[mem][status][connection]) {

			# Market
			if ($_SESSION[mem][status][market][now]) {

				# Intime
				if ($_SESSION[mem][status][intime]) {

					# Ready
					if ($_SESSION[fun][cache]->Ready(candle)) {

						# Argument
						$argument = [
							round(1 - ($_SESSION[mem][setting][balance_edge] / 100), 2),
							$_SESSION[mem][currency][forex][EUR_USD]
						];

						# Try
						foreach (range(0, 1) as $try) {

							# Wait
							$_SESSION[fun][tool]->Wait(ses_request, $try);

							# Order
							$order = $_SESSION[fun][api]->Api(ses_user, ses_request, 0, null, 0, 0, $argument);

							# User
							foreach ($order as $user => $v) {
									
								# Update trade
								$this->UpdateTrade($user, $try, $v);

								# Update balance
								$this->UpdateBalance($user, $try, $v);

								# Update ticker
								$this->UpdateTicker($user, $try, $v);

								# Update position
								$this->UpdatePosition($user, $try, $v);
							}
						}

						# Date
						$date = strtotime($_SESSION[fun][tool]->DateSum($try));

						# Cache add
						$_SESSION[fun][cache]->Add([ses_request, date('i', $date)]);
					}
				}
			}
		}
	}

	private function TradeGet($user) {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tra, "SELECT user, ticker, cluster, position, quantity, status, status_out, price_in_filled, price_out, price_out_filled, quantity_book_in, quantity_position, quantity_book_out, pnl_a, pnl_r, (fee_in + fee_out) AS fee, date_add FROM trade WHERE user = '$user' AND status_out = '' ORDER BY date_add DESC");

		# Compute
		if ($sql) {

			# Result
			while ($a = mysqli_fetch_assoc($sql)) {
				$result[$a[ticker]] = [
					cluster 		  => $a[cluster],
					position 		  => $a[position],
					price_in_filled   => (float)$a[price_in_filled],
					price_out   	  => (float)$a[price_out],
					price_out_filled  => (float)$a[price_out_filled],
					quantity 		  => (float)$a[quantity],
					quantity_book_in  => (float)$a[quantity_book_in],
					quantity_position => (float)$a[quantity_position],
					quantity_book_out => (float)$a[quantity_book_out],
					pnl_a   		  => (float)$a[pnl_a],
					pnl_r   		  => (float)$a[pnl_r],
					fee   		      => (float)$a[fee],
					status   		  => ($a[status] ? $a[status] : book),
					status_out        => $a[status_out],
					date_add  		  => $a[date_add]
				];
			}

		} else {

			# Result
			$result = null;
		}

	    # Chain
		$_SESSION[fun][tool]->Chain($user, 0, 0, trade, get_);

		# Return
		return $result;
	}

	private function UpdateTrade($user, $try, $data) {

		# Date
		$date = $_SESSION[fun][tool]->DateSum($try);

		# Compute
		if ($data[locked]) {

			# Trades
			$trades = $this->TradeGet($user);

			# Compute
		    foreach ($data[ticker] as $ticker => $v) {

		    	# Continue
		    	if (!$trades[$ticker]) continue;
		    	$trade = $trades[$ticker];
		    	
		    	# Life
		    	$life = $_SESSION[fun][tool]->DateDiff($trade[date_add], ses_date);

		        # Price
		        $price = $_SESSION[mem][ticker][$ticker][ask];

		        # Price in filled
		        $price_in_filled = $v[position][price];

		        # Price out
		        $price_out = max($v[book_out][price], $trade[price_out]);
		        
		        # Quantity position
				$quantity_position = $v[position][quantity];

				# Quantity book in
				$quantity_book_in = $v[book_in][quantity];

				# Quantity book out
				$quantity_book_out = max($v[book_out][quantity], $trade[quantity_book_out]);

		        # Status
				if ($quantity_book_in && !$quantity_position && !$quantity_book_out) {
				    $status = book;
				} elseif ($quantity_book_in && $quantity_position) {
				    $status = filled_in_part;
				} elseif (!$quantity_book_in && $quantity_position && !$quantity_book_out) {
				    $status = filled_in;
				} elseif ($quantity_position && $quantity_book_out) {
				    $status = booked_out;
				} elseif ($quantity_position && $quantity_book_out) {
				    $status = filled_out_part;
				} else {
				    $status = $trade[status];
				}

				# Status
				$status = $_SESSION[fun][tool]->StatusPriority($trade[status], $status);

	            # Pnl
				if ($quantity_position && $price_in_filled) {
					if ($trade[position] == long) {
					    $pnl_a = round((($price - $price_in_filled) * $quantity_position) - $trade[fee], 2);
					} else {
					    $pnl_a = round((($price_in_filled - $price) * $quantity_position) - $trade[fee], 2);
					}
					$pnl_r = round(($pnl_a / ($price_in_filled * $quantity_position)) * 100, 2);
				} else {
				    $pnl_a = 0;
				    $pnl_r = 0;
				}

				# Date in
				$date_in = ($status == book ? date0 : ses_date);

		        # Sql
		        $_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET status = '$status', price_in_filled = $price_in_filled, price_out = $price_out, quantity_book_in = $quantity_book_in, quantity_position = $quantity_position, quantity_book_out = $quantity_book_out, pnl_a = $pnl_a, pnl_r = $pnl_r, life = $life, date_update = '$date', date_in = CASE WHEN date_in = 0 THEN '$date_in' ELSE date_in END WHERE ticker = '$ticker' AND status_out = ''");
			}

		} else {

	        # Sql
	        $_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET status = 'completed', status_out = 'completed', price_out_filled = price_out, quantity_book_in = 0, quantity_position = 0, quantity_book_out = 0, date_update = '$date', date_out = CASE WHEN date_in != 0 THEN '$date' ELSE date_out END WHERE status_out = ''");
		}
		
        # Chain
        $_SESSION[fun][tool]->Chain($user, $try, 0, trade, write);
	}

	private function UpdateBalance($user, $try, $data) {	

		# Id
		$id = (int)(date('mdHi') . str_pad($_SESSION[mem][user][$user][info][id], 2, 0, STR_PAD_LEFT));

		# Funds eur
		$funds_eur = $data[funds_eur];
		
		# Funds usd
		$funds_usd = $data[funds_usd];

		# Balance
		$balance = $data[balance];

		# Locked
		$locked = $data[locked];

		# Available
		$available = max(0, $data[available]);

		# Allocation
		$allocation = ($balance ? round($locked / $balance, 2) : 0);
		
		# Pnl
		$pnl = $_SESSION[fun][sql]->SelectRow(d_use, "SELECT IF(funds_eur > 0, GREATEST(-100, LEAST(999.99, ($funds_eur - funds_eur))), 0) AS pnl_a, IF(funds_eur > 0, ROUND(GREATEST(-100, LEAST(999.99, ((($funds_eur - funds_eur) / funds_eur) * 100))), 2), 0) AS pnl_r FROM (SELECT COALESCE((SELECT funds_eur FROM user_balance WHERE user = '$user' AND funds_eur > 0 AND DATE(date) = CURDATE() ORDER BY date ASC LIMIT 1) ,0) AS funds_eur) AS t");					

		# Mem
		$_SESSION[mem][user][$user][stable][balance] = $balance;
		$_SESSION[mem][user][$user][stable][locked] = $locked;
		$_SESSION[mem][user][$user][stable][available] = $available;

		# Sql
		$_SESSION[fun][sql]->Insert(d_use, "INSERT INTO user_balance (id, user, funds_eur, funds_usd, balance, locked, available, pnl_a, pnl_r, allocation, date) VALUES ($id, '$user', $funds_eur, $funds_usd, $balance, $locked, $available, $pnl[pnl_a], $pnl[pnl_r], $allocation, '" . ses_date . "') ON DUPLICATE KEY UPDATE funds_eur = $funds_eur, funds_usd = $funds_usd, balance = $balance, locked = $locked, available = $available, pnl_a = $pnl[pnl_a], pnl_r = $pnl[pnl_r], allocation = $allocation");		

        # Chain
        $_SESSION[fun][tool]->Chain($user, $try, 0, balance, write);
	}	

	private function UpdateTicker($user, $try, $data) {

		# Balance
		$balance = $data[balance];

		# Compute
		if ($data[locked]) {
			
			# Data
			$data = $data[ticker];

		} else {

			# Data
			$data = [];
			foreach (array_keys($_SESSION[mem][user][$user][ticker]) as $ticker) {
				if (isset($_SESSION[mem][ticker][$ticker][bid])) $data[$ticker][quantity] = 0;
			}
		}

		# Compute
		foreach ($data as $ticker => $v) {

			# Id
			$id = (int)(date('NHi') . str_pad($_SESSION[mem][user][$user][info][id], 2, 0, STR_PAD_LEFT) . str_pad($_SESSION[mem][tickers][$ticker], 2, 0, STR_PAD_LEFT));

			# Weight
			$weight = $_SESSION[mem][user][$user][ticker][$ticker][weight];

			# Precision
			$precision = 10 ** $_SESSION[mem][ticker][$ticker][precision][quantity];

			# Price
			$price = $_SESSION[mem][ticker][$ticker][ask];

			# Size
			$size_min = $_SESSION[mem][user][$user][ticker][$ticker][size_min];
			$size_max = $_SESSION[mem][user][$user][ticker][$ticker][size_max];

			# Max
			$max = max(0, round(($balance * $weight) / $price, 2));

			# Allocation
			$allocation = max(0, floor((($balance * $weight) / $price) * $precision) / $precision);
		    $allocation = ($size_min && $allocation < $size_min) ? 0 : ($size_max ? min($allocation, $size_max) : $allocation);

			# Available
			if ($v[quantity]) {
				$temp = max(0, min($allocation, round($allocation - abs($v[quantity]), $precision)));
			    $available_long = ($v[quantity] > 0 ? $temp : $allocation);
			    $available_short = ($v[quantity] < 0 ? $temp : $allocation);
			} else {
				$available_long = $allocation;
				$available_short = $allocation;
			}

			# Available
			if (!$_SESSION[mem][user][$user][status][long]) $available_long = 0;
			if (!$_SESSION[mem][user][$user][status][short]) $available_short = 0;

			# Mem
		    $_SESSION[mem][user][$user][ticker][$ticker][allocation] = $allocation;
		    $_SESSION[mem][user][$user][ticker][$ticker][locked] = $v[quantity];
		    $_SESSION[mem][user][$user][ticker][$ticker][available][long] = $available_long;
		    $_SESSION[mem][user][$user][ticker][$ticker][available][short] = $available_short;

		    # Sql
		    $_SESSION[fun][sql]->Insert(d_tic, "INSERT INTO ticker_balance (id, user, ticker, max, allocation, `locked`, available_long, available_short, date) VALUES ($id, '$user', '$ticker', $max, $allocation, $v[quantity], $available_long, $available_short, '" . ses_date . "') ON DUPLICATE KEY UPDATE max = $max, allocation = $allocation, `locked` = $v[quantity], available_long = $available_long, available_short = $available_short, date = '" . ses_date . "'");
        }

        # Chain 
        $_SESSION[fun][tool]->Chain($user, $try, 0, ticker, write);
	}

	private function UpdatePosition($user, $try, $order) {

		# Compute
		if ($order[locked]) {

			# Compute
			foreach ($order[ticker] as $ticker => $v) {

				# Position
				$position = ($v[quantity] > 0 ? long : short);

				# Size
				$size = round(max($v[position][price], $v[book_in][price]) * $v[quantity], 2);

				# Sql
				$_SESSION[fun][sql]->Insert(d_use, "INSERT INTO position (user, ticker, position, size, quantity_book_in, quantity_position, quantity_book_out, date) VALUES ('$user', '$ticker', '$position', $size, {$v[book_in][quantity]}, {$v[position][quantity]}, {$v[book_out][quantity]}, '" . ses_date . "') ON DUPLICATE KEY UPDATE position = '$position', size = $size, quantity_book_in = {$v[book_in][quantity]}, quantity_position = {$v[position][quantity]}, quantity_book_out = {$v[book_out][quantity]}, date = '" . ses_date . "'");
			}

		} else {

			# Sql
			$_SESSION[fun][sql]->Delete(d_use, "DELETE FROM position WHERE user = '$user'");
		}

        # Chain
        $_SESSION[fun][tool]->Chain($user, $try, 0, position, write);
	}		
}