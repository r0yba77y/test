<?php
class Alive {

	public function Exec() {

		# Add
		$this->Add();

		# Cache add
		$_SESSION[fun][cache]->Add([ses_request, date('i')]);
	}

	private function Add() {

		# Exec
		$exec = $_SESSION[fun][tool]->Exec();

		# Id
		$id = (int)date('NHi');

		# Sql
		$_SESSION[fun][sql]->Insert(d_log, "INSERT IGNORE INTO alive (id, date) VALUES ($id, '" . ses_date . "') ON DUPLICATE KEY UPDATE date = '" . ses_date . "'");

		# Chain
		$_SESSION[fun][tool]->Chain(null, 0, $exec, null, write);

		# Cache add
		$_SESSION[fun][cache]->Add([ses_request, date('i')]);
	}
}