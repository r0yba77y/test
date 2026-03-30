<?php
class Connection {

	public function Exec() {

		# Compute
		foreach ([now, nxt] as $offset => $temp) {

			# Market
			if ($_SESSION[mem][status][market][$temp]) {

				# Wait
				$_SESSION[fun][tool]->Wait(ses_request, $offset);

				# Add
				$this->Add($offset);

				# Date
				$date = strtotime($_SESSION[fun][tool]->DateSum($offset));

				# Cache add
				$_SESSION[fun][cache]->Add([ses_request, date('i', $date)]);
			}
		}
	}

	private function Add($offset) {
		
		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Date
		$date = strtotime($_SESSION[fun][tool]->DateSum($offset));

		# Id
		$id = (int)date('NHi', $date);

		# Connection
		$connection = (int)$_SESSION[fun][api]->Api();

		# Date
		$date = date('Y-m-d H:i', $date);

		# Sql
		$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO connection (id, connection, date) VALUES ($id, $connection, '$date') ON DUPLICATE KEY UPDATE connection = VALUES(connection), date = VALUES(date)");

		# Chain
		$_SESSION[fun][tool]->Chain(null, $offset, $exec, null, write);
	}
}