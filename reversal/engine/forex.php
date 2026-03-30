<?php
class Forex {

	public function Exec() {

		# Connection
		if ($_SESSION[mem][status][connection]) {

			# Market
			if ($_SESSION[mem][status][market][now]) {

				# Intime
				if ($_SESSION[mem][status][intime]) {

					# Wait
					$_SESSION[fun][tool]->Wait(ses_request);

					# Add
					$this->Add();
				}
			}
		}
	}

	private function Add() {

		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Value
		$value = $_SESSION[fun][api]->Api();

		# Compute
		if ($value) {

			# Id
			$id = (int)date('NHi');

			# Sql
			$_SESSION[fun][sql]->Insert(d_can, "INSERT INTO forex (id, pair, value, date) VALUES ($id, 'EUR_USD', $value, '" . ses_date . "') ON DUPLICATE KEY UPDATE value = $value");

	        # Chain
			$_SESSION[fun][tool]->Chain(null, 0, $exec, null, write);
		}
	}
}