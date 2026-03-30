<?php
class Candle {

	public function Exec() {

		# Connection
		if ($_SESSION[mem][status][connection]) {

			# Market
			if ($_SESSION[mem][status][market][now]) {

				# Add candle
				$this->AddCandle();

				# Intime
				if ($_SESSION[mem][status][intime]) {

					# Add spread
					$this->AddSpread();
				}
			}
		}
	}

	private function AddCandle() {

		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Compute
		foreach (array_keys($_SESSION[mem][tickers]) as $ticker) {

			# Data
			$data = $_SESSION[fun][api]->Api(ses_user, ses_request, 0, $ticker, 1, 0, array($ticker));

			# Compute
			if ($data && $data[bid] && $data[ask]) {

				# Sql
			    $_SESSION[fun][sql]->Update(d_tic, "UPDATE ticker SET bid = $data[bid], ask = $data[ask] WHERE ticker = '$ticker'");

				# Bid + ask
				$_SESSION[mem][ticker][$ticker][bid] = (float)$data[bid];
				$_SESSION[mem][ticker][$ticker][ask] = (float)$data[ask];

				# Ticker id
				$ticker_id = $_SESSION[mem][tickers][$ticker];

				# Id
				$id = (int)(date('NHi', strtotime(ses_date)) . str_pad($ticker_id, 2, 0, STR_PAD_LEFT));

				# Tf
				$tf = ($_SESSION[mem][ticker][$ticker][tf] ?? 1);

				# Spread
				$spread = abs(round($_SESSION[fun][tool]->Slope($data[bid], $data[ask]), 3));

				# Sql
				$_SESSION[fun][sql]->Insert(d_can, "INSERT INTO candle (id, ticker, bid, ask, spread, date) VALUES ($id, '$ticker', $data[bid], $data[ask], $spread, '" . ses_date . "') ON DUPLICATE KEY UPDATE bid = VALUES(bid), ask = VALUES(ask), date = '" . ses_date . "'");

				# Cache add
				$_SESSION[fun][cache]->Add([ses_request, date('i')]);
			}
		}

		# Exec
		$exec = $_SESSION[fun][tool]->Exec($exec);

		# Chain
		$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, null, write);
		$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, candle_volume, write);
	}

	private function AddSpread() {

		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Compute
		foreach (array_keys($_SESSION[mem][ticker]) as $ticker) {

			# Sql
			$sql = $_SESSION[fun][sql]->Select(d_can, "SELECT (HOUR(date) * 100 + FLOOR(MINUTE(date) / 30) * 30) AS hour, CAST(CASE WHEN (HOUR(date)*100 + FLOOR(MINUTE(date)/30) * 30) >= 1000 THEN ((HOUR(date) * 60 + FLOOR(MINUTE(date) / 30) * 30) - 600) / 30 + 1 ELSE ((HOUR(date) * 60 + FLOOR(MINUTE(date) / 30) * 30) + 840) / 30 + 1 END AS UNSIGNED) AS id, COALESCE(ROUND(AVG(spread), 3), 0) AS spread, COUNT(spread) AS sample FROM candle WHERE ticker = '$ticker' GROUP BY hour ORDER BY id ASC");
			if (!$sql) continue;

			# Write
			$write = [];

			# Compute
			while ($a = mysqli_fetch_assoc($sql)) {

				# Id
				$id = (int)($_SESSION[mem][tickers][$ticker] . str_pad($a[id], 2, 0, STR_PAD_LEFT));

				# Write
				$write[] = "($id, '$ticker', $a[hour], $a[spread], $a[sample], '" . ses_date . "')";
			}

			# Write
			$write = implode(implode_number, $write);

			# Sql
			$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM spread WHERE ticker = '$ticker'");

			# Sql
			$_SESSION[fun][sql]->Insert(d_can, "INSERT INTO spread (id, ticker, hour, spread, sample, date) VALUES $write");
		}

		# Sql
		$ticker = implode("', '", $_SESSION[mem][tickers]);
		$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM spread WHERE ticker NOT IN ('$ticker') AND date < NOW() - INTERVAL 4 HOUR");

		# Chain
		$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, 'candle_spread', write);
	}
}