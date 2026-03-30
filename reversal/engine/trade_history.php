<?php
class Trade_history {

	public function Exec() {

		# Connection
		if ($_SESSION[mem][status][connection]) {

			# Market
			if ($_SESSION[mem][status][market][now]) {

				# Intime
				if ($_SESSION[mem][status][intime]) {

					# Try
					foreach (range(0, 1) as $try) {

						# Wait
						$_SESSION[fun][tool]->Wait(ses_request, $try);

						# Data
						$data = $_SESSION[fun][api]->Api();

						# Compute
						if ($data) {

							# Update trade
							$this->UpdateTrade($data, $try);
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

	private function UpdateTrade($data, $try) {

		# Compute
	    foreach ($data as $user => $a) {

			# Compute
		    foreach ($a as $ticker => $b) {

				# Compute
			    foreach ($b as $call => $v) {

			        # Sql
			        if ($v[action] == buy) {
			        	$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET price_in_filled = $v[price], fee_in = $v[fee] WHERE user = '$user' AND cluster = $v[cluster] AND ticker = '$ticker'");
			        } else {
			        	$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET price_out_filled = $v[price], fee_out = $v[fee], date_out = '$v[date]' WHERE user = '$user' AND cluster = $v[cluster] AND ticker = '$ticker'");
			        }
				}
			}
			
	        # Chain
	        $_SESSION[fun][tool]->Chain($user, $try, 0, trade_history, write);
		}
	}
}