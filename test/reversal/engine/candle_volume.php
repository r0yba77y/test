<?php
class Candle_volume {

	public function Exec() {

		# Connection
		if ($_SESSION[mem][status][connection]) {

			# Market
			if ($_SESSION[mem][status][market][now]) {

				# Intime
				if ($_SESSION[mem][status][intime]) {

					# Wait
					$_SESSION[fun][tool]->Wait(ses_request);

					# Add candle
					$this->AddCandle();

					# Add volume
					$this->AddVolume();

					# Cache add
					$_SESSION[fun][cache]->Add([ses_request, date('i')]);
				}
			}
		}
	}

	private function AddCandle() {

		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Compute
		foreach ($_SESSION[mem][ticker] as $ticker => $a) {

			# Continue ?
			if (!isset($a[strategy])) continue;

			# Ticker id
			$ticker_id = $_SESSION[mem][tickers][$ticker];

			# Data
			$data = $_SESSION[fun][api]->Api(ses_user, ses_request, 0, $ticker, $a[tf], 0, array($ticker, $a[tf]));

			# Sleep
			usleep(50000);

			# Compute
			if ($data) {

				# Write
				$write = [];

				# Compute
				foreach ($data as $k => $v) {

					# Id
					$id = (int)(date('dHi', strtotime($v[date_])) . str_pad($ticker_id, 2, 0, STR_PAD_LEFT));

					# Write
					$write[] = "($id, '$ticker', $a[tf], $v[volume], '" . $v[date_] . "')";
				}

				# Write
				$write = implode(implode_number, $write);

				# Sql
				$_SESSION[fun][sql]->Insert(d_can, "INSERT INTO candle_volume (id, ticker, tf, volume, date) VALUES $write ON DUPLICATE KEY UPDATE volume = VALUES(volume), date = VALUES(date)");
			}
		}

		# Chain
		$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, null, write);
	}

	private function AddVolume() {

		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Compute
		foreach (array_keys($_SESSION[mem][ticker]) as $ticker) {

			# Sql
			$sql = $_SESSION[fun][sql]->Select(d_can, "SELECT (HOUR(date) * 100 + FLOOR(MINUTE(date) / 30) * 30) AS hour, CAST(CASE WHEN (HOUR(date)*100 + FLOOR(MINUTE(date) / 30) * 30) >= 1000 THEN ((HOUR(date) * 60 + FLOOR(MINUTE(date) / 30) * 30) - 600) / 30 + 1 ELSE ((HOUR(date) * 60 + FLOOR(MINUTE(date) / 30) * 30) + 840) / 30 + 1 END AS UNSIGNED) AS id, COALESCE(ROUND(AVG(volume / tf), 0), 0) AS volume, COUNT(volume) AS sample FROM candle_volume WHERE ticker = '$ticker' GROUP BY hour ORDER BY id ASC");
			if (!$sql) continue;

			# Write
			$write = [];

			# Compute
			while ($a = mysqli_fetch_assoc($sql)) {

				# Id
				$id = (int)($_SESSION[mem][tickers][$ticker] . str_pad($a[id], 2, 0, STR_PAD_LEFT));

				# Write
				$write[] = "($id, '$ticker', $a[hour], $a[volume], $a[sample], '" . ses_date . "')";
			}

			# Write
			$write = implode(implode_number, $write);

			# Sql
			$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM volume WHERE ticker = '$ticker'");

			# Sql
			$_SESSION[fun][sql]->Insert(d_can, "INSERT INTO volume (id, ticker, hour, volume, sample, date) VALUES $write");
		}

		# Sql
		$ticker = implode("', '", $_SESSION[mem][tickers]);
		$_SESSION[fun][sql]->Delete(d_can, "DELETE FROM volume WHERE ticker NOT IN ('$ticker') AND date < NOW() - INTERVAL 4 HOUR");			

		# Chain
		$_SESSION[fun][tool]->Chain(ses_user, 0, $exec, candle_volume, write);
	}
}