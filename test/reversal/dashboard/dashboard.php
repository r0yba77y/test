<?php
class Dashboard {

	private $currencies;
	private $output = null;

	public function Exec() {

		# Compute
		if ($_SESSION[fun][cache]->Get([dashboard])) {

			# Cache
			$this->output = $_SESSION[fun][cache]->Get([dashboard]);

		} else {

			# Header
			$this->Header();

			# Running
			$this->Running();

			# Today
			$this->Today();

			# Yesterday
			$this->Yesterday();

			# Days
			$this->Days();

			# Last week
			$this->Week();

			# Last month
			$this->Month();

			# Genesis
			$this->Genesis();

			# Balance
			$this->Balance();

			# Footer
			$this->Footer();

			# Cache add
			$_SESSION[fun][cache]->Add([ses_request], $_SESSION[fun][tool]->SecondsTo(), $this->output);
		}

		# Output
		echo $this->output;
	}

	private function Header() {

		# Result
		$this->output .= '<!DOCTYPE html>';
		$this->output .= '<html lang="en" style="background: #24253d">';
		$this->output .= '<head>';
		$this->output .= '<meta charset="utf-8">';
		$this->output .= '<link rel="icon" href="favicon.ico">';
		$this->output .= '<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
		$this->output .= '<meta name="robots" content="none"><meta name="googlebot" content="none">';
		$this->output .= '<meta name="theme-color" content="#24253d">';
		$this->output .= '<link rel="stylesheet" href="css.css">';
		$this->output .= '</head>';
		$this->output .= '<body style="cursor: default;background: #24253d;color: lightgray;padding: 5px">';
		$this->output .= '<div style="color: gold; font-size: 25px; display: flex; justify-content: center; align-items: center; width: 100%; height: 80px">Hey ' . 'Luca' . '!</div>';
	}

	private function Running() {

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_tra, "SELECT ROUND(SUM(pnl_rate), 2) AS pnl_rate, ROUND(SUM(pnl), 2) AS pnl, COUNT(*) AS count FROM trade WHERE status_out = '' AND DATE(date_update) = CURDATE()");
		if (!$sql[count]) return;

		# Color
		$color = ($sql[pnl_rate] == 0 ? 'slategray' : ($sql[pnl_rate] > 0 ? 'limegreen' : 'red'));

		# Result
		$this->output .= '<div style="text-align: center;border-radius: 15px;padding: 10px 0;margin-bottom: 20px;border: 4px solid #1E1F33">';
		$this->output .= '<div style="color: white;font-size: 15px">RUNNING</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: MediumPurple">' . $this->PriceAction() . '</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: lightgray">' . number_format($sql[pnl_rate], 2, ',', '.') . '%</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $sql[count] . ' trades | ' . number_format($sql[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function Today() {

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_tra, "SELECT ROUND(SUM(pnl_rate), 2) AS pnl_rate, ROUND(SUM(pnl), 2) AS pnl, COUNT(*) AS count FROM trade WHERE status_out != '' AND DATE(date_update) = CURDATE()");
		if (!$sql[count]) return;

		# Color
		$color = ($sql[pnl_rate] == 0 ? 'slategray' : ($sql[pnl_rate] > 0 ? 'limegreen' : 'red'));

		# Result
		$this->output .= '<div style="text-align: center;background: #1E1F33;border-radius: 15px;padding: 10px 0;margin-bottom: 20px">';
		$this->output .= '<div style="color: white;font-size: 15px">TODAY</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: ' . $color . '">' . number_format($sql[pnl_rate], 2, ',', '.') . '%</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $sql[count] . ' trades | ' . number_format($sql[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function Yesterday() {

		# Day
		$day = 1;

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_tra, "SELECT ROUND(SUM(pnl_rate), 2) AS pnl_rate, ROUND(SUM(pnl), 2) AS pnl, COUNT(*) AS count FROM trade WHERE status_out != '' AND DATE(date_update) = CURDATE() - INTERVAL $day DAY");
		if (!$sql[count]) return;

		# Color
		$color = ($sql[pnl_rate] == 0 ? 'slategray' : ($sql[pnl_rate] > 0 ? 'limegreen' : 'red'));

		# Result
		$this->output .= '<div style="text-align: center;background: #1d1e31;border-radius: 15px;padding: 10px 0;margin-bottom: 20px">';
		$this->output .= '<div style="color: white;font-size: 15px">YESTERDAY</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: ' . $color . '">' . number_format($sql[pnl_rate], 2, ',', '.') . '%</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $sql[count] . ' trades | ' . number_format($sql[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function Days() {

		# Day
		$day = 3;

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_tra, "SELECT ROUND((SUM(pnl_rate)) / $day, 2) AS pnl_rate, ROUND(SUM(pnl) / $day, 2) AS pnl, ROUND(COUNT(*) / $day) AS count FROM trade WHERE status_out != '' AND DATE(date_update) >= CURDATE() - INTERVAL $day DAY");

		# Compute
		while ($a = mysqli_fetch_assoc($sql)) {

			# Return
			if (!$a[count]) return;

			# Color
			$color = ($a[pnl_rate] == 0 ? 'slategray' : ($a[pnl_rate] > 0 ? 'limegreen' : 'red'));

			# Result
			$this->output .= '<div style="text-align: center;background: #1d1e31;border-radius: 15px;padding: 10px 0;margin-bottom: 20px">';
			$this->output .= '<div style="color: white;font-size: 15px">LAST 3 DAYS AVG</div>';
			$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: ' . $color . '">' . number_format($a[pnl_rate], 2, ',', '.') . '%</div>';
			$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $a[count] . ' trades | ' . number_format($a[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
			$this->output .= '</div>';
		}
	}

	private function Week() {

		# Day
		$day = 7;

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_tra, "SELECT ROUND((SUM(pnl_rate)) / $day, 2) AS pnl_rate, ROUND(SUM(pnl) / $day, 2) AS pnl, ROUND(COUNT(*) / $day) AS count FROM trade WHERE status_out != '' AND DATE(date_update) >= CURDATE() - INTERVAL $day DAY");
		if (!$sql[count]) return;

		# Color
		$color = ($sql[pnl_rate] >= 0 ? 'MediumPurple' : 'red');

		# Result
		$this->output .= '<div style="text-align: center;background: #1d1e31;border-radius: 15px;padding: 10px 0;margin-bottom: 20px">';
		$this->output .= '<div style="color: white;font-size: 15px">LAST WEEK AVG</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: ' . $color . '">' . number_format($sql[pnl_rate], 2, ',', '.') . '%</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $sql[count] . ' trades | ' . number_format($sql[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function Month() {

		# Day
		$day = 30;

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_tra, "SELECT ROUND((SUM(pnl_rate)) / $day, 2) AS pnl_rate, ROUND(SUM(pnl) / $day, 2) AS pnl, ROUND(COUNT(*) / $day) AS count FROM trade WHERE status_out != '' AND DATE(date_update) >= CURDATE() - INTERVAL $day DAY");
		if (!$sql[count]) return;

		# Color
		$color = ($sql[pnl_rate] >= 0 ? 'MediumPurple' : 'red');

		# Result
		$this->output .= '<div style="text-align: center;background: #1d1e31;border-radius: 15px;padding: 10px 0;margin-bottom: 20px">';
		$this->output .= '<div style="color: white;font-size: 15px">LAST MONTH AVG</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: ' . $color . '">' . number_format($sql[pnl_rate], 2, ',', '.') . '%</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $sql[count] . ' trades | ' . number_format($sql[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function Genesis() {

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_tra, "SELECT ROUND(SUM(pnl_rate), 2) AS pnl_rate, ROUND(SUM(pnl), 2) AS pnl, COUNT(*) AS count FROM trade WHERE status_out != ''");
		if (!$sql[count]) return;

		# Color
		$color = ($sql[pnl_rate] >= 0 ? 'MediumPurple' : 'red');

		# Result
		$this->output .= '<div style="text-align: center;background: #161724;border-radius: 15px;padding: 10px 0;margin-bottom: 20px">';
		$this->output .= '<div style="color: white;font-size: 15px">GENESIS</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: ' . $color . '">' . number_format($sql[pnl_rate], 2, ',', '.') . '%</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $sql[count] . ' trades | ' . number_format($sql[pnl], 2, ',', '.') . ' ' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function Balance() {

		# Sql
		$balance = $_SESSION[fun][sql]->Select1(d_use, "SELECT ROUND(SUM(size), 2) AS balance FROM user_balance WHERE ticker = '{$_SESSION[mem][setting][currency][stable]}'");

		# Result
		$this->output .= '<div style="text-align: center;background: #161724;border-radius: 15px;padding: 10px 0">';
		$this->output .= '<div style="color: white;font-size: 15px">BALANCE</div>';
		$this->output .= '<div style="font-size: 42px;margin: 10px 0 0 0;color: DodgerBlue">' . number_format($balance, 2, ',', '.') . '</div>';
		$this->output .= '<div style="font-size: 15px;margin: 10px 0 0 0;color: dimgray;letter-spacing: -0.5px">' . $_SESSION[mem][setting][currency][stable] . '</div>';
		$this->output .= '</div>';
	}

	private function PriceAction() {

		# Sql
		$volatility = $_SESSION[fun][sql]->Select1(d_ind, "SELECT ROUND(AVG(CASE WHEN position = 'long' THEN volatility WHEN position = 'short' THEN -volatility END), 3) AS volatility FROM `" . d_can . "`.candle WHERE tf = 1 AND position != 'flat' AND date >= NOW() - INTERVAL 2 HOUR");

		# Result
		$result = $this->PriceActionName($volatility) . ' (' . $volatility . ')';

		# Return
		return $result;
	}

	public function PriceActionName($value) {

		# Result
		if ($value > 0.025) {
			if ($value >= 0.200) {
				$result = 'to the moon';
			} elseif ($value >= 0.150) {
				$result = 'skyrocketing';
			} elseif ($value >= 0.100) {
				$result = 'pumping';
			} elseif ($value >= 0.050) {
				$result = 'uptrend';
			} else {
				$result = 'slight uptrend';
			}
		} elseif ($value < -0.025) {
			if ($value <= -0.200) {
				$result = 'the Black Swan';
			} elseif ($value <= -0.150) {
				$result = 'brutal collapse';
			} elseif ($value <= -0.100) {
				$result = 'dumping';
			} elseif ($value <= -0.050) {
				$result = 'downtrend';
			} else {
				$result = 'slight downtrend';
			}
		} else {
			$result = 'flat';
		}

		# Return
		return $result;
	}

	private function Footer() {

		# Result
		$this->output .= '<div style="color: gold;display: flex;justify-content: center;align-items: center;width: 100%;height: 50px">' . $_SESSION[mem][setting]['software'][name] . ' ' . $_SESSION[mem][setting][setting][software][version] . ' (fork: ' .  $_SESSION[mem][setting][setting][software][fork] . ')</div>';
		$this->output .= '</div>';
		$this->output .= '</body></html>';
	}
}