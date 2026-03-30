<?php
class Trade_call_send {

	public function TradeCallSend($data) {
		
		# Call
		if (($_SESSION[mem][status][call] && $_SESSION[mem][status][trade]) || $_SESSION[mem][setting][demo]) {

			# Send
			$this->Send($data);
		}
	}

	private function Send($data) {

		# Demo
		$demo = 0;

		# User
		foreach ($data as $user => $a) {

			# Request
			foreach ($a as $request => $b) {

				# Request
				foreach ($b as $k => $v) {

					# Exec
					$exec = $_SESSION[fun][tool]->Exec();

					# Compute
					if ($request == trade_add) {
						
						# Call
						if ($demo) {
							$call[status] = submitted;
							$call[cluster] = rand(1000, 9999);
							$call[call] = rand(0, 1000000000);
						} else {
							$call = $_SESSION[fun][api]->Api($user, $request, $v[id], $v[ticker], $v[tf], 0, array($v[ticker], $v[price], $v[quantity], $v[action]));
						}

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET cluster = $call[cluster], status = 'submitted_in', date_update = '" . ses_date . "' WHERE id = $v[id]");

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade_call SET cluster = $call[cluster] WHERE id = $v[id]");
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade_call SET `call` = $call[call], status = '$call[status]' WHERE id = $v[id] AND request = '$request' AND status = ''");

					} elseif ($request == trade_close) {

						# Call
						if ($demo) {
							$call[call] = rand(0, 1000000000);
						} else {
							$call = $_SESSION[fun][api]->Api($user, $request, $v[id], $v[ticker], $v[tf], 0, array($v[ticker], $v[price], $v[quantity], $v[action]));
						}					

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET status = 'submitted_out', date_update = '" . ses_date . "' WHERE id = $v[id]");

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade_call SET `call` = $call[call], status = 'submitted' WHERE id = $v[id] AND request = '$request' AND status = ''");
					
					} elseif (in_array($request, [stop_loss_add, take_profit_add])) {

						# Call
						if ($demo) {
							$call[status] = submitted;
							$call[call] = rand(0, 1000000000);
						} else {
							$call = $_SESSION[fun][api]->Api($user, $request, $v[id], $v[ticker], $v[tf], 0, array($v[ticker], $v[price], $v[action], $v[cluster]));
						}	

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade_call SET `call` = $call[call], status = '$call[status]' WHERE id = $v[id] AND request = '$request' AND status = ''");
					
					} elseif ($request == trade_cancel) {

						# Call
						$_SESSION[fun][api]->Api($user, $request, $v[id], $v[ticker], $v[tf], 0, array($v[cluster]));

						# Status out
						$status_out = (isset($a[request][trade_close]) ? null : cancelled);		

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET status = 'completed', status_out = '$status_out', date_update = '" . ses_date . "' WHERE cluster = $v[cluster] AND status_out = ''");

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade_call SET status = 'submitted' WHERE id = $v[id] AND request = '$request' AND status = ''");

					} elseif (in_array($request, [take_profit_cancel, stop_loss_cancel])) {

						# Call
						$_SESSION[fun][api]->Api($user, $request, $v[id], $v[ticker], $v[tf], 0, array($v[cluster]));

						# Sql
						$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade_call SET status = 'submitted' WHERE id = $v[id] AND request = '$request' AND status = ''");
					}
					
					# Exec
					$exec = $_SESSION[fun][tool]->Exec($exec);

					# Chain
					$_SESSION[fun][tool]->Chain($user, 0, $exec, $request, write);

					# Sleep
					usleep(200000);
				}
			}
		}
	}

	private function TradeUpdate($user) {

		# Sql
		$_SESSION[fun][sql]->Update(d_tra, "UPDATE trade SET status = 'call', date_update = '" . ses_date . "' WHERE user = '$user' AND status_out = '' AND date_add = '" . ses_date . "'");

	    # Chain
		$_SESSION[fun][tool]->Chain(ses_user, 0, 0, trade, update);
	}	
}