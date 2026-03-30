<?php
class Server {

	private $currencies;
	private $output = null;
	private $blue = '#559FFF';
	private $green = '#0ECB81';
	private $gold = 'gold';
	private $red = '#FF3366';
	private $separator = '<span style="color: gray"> • </span>';
	private $data;

	public function Exec() {

		# Data
		$this->Data();

		# Ram
		$this->Ram();

		# Ip
		$this->Ip();

		# Load
		$this->Load();

		# Temperature
		$this->Temperature();

		# Size
		#$this->Size();

		# Footer
		$this->Footer();
	}

	private function Data() {

		# Ram
		$sql = $_SESSION[fun][sql]->SelectRow(d_log, "SELECT ram, ram_cache, uptime, reboot FROM server ORDER BY date DESC LIMIT 1");

		# Data
		$this->data = [
			ram 		=> $sql[ram],
			ram_cache 	=> $sql[ram_cache],
			uptime 		=> $sql[uptime],
			reboot 		=> $sql[reboot]
		];
	}

	private function Ram() {

		# Color
		if ($this->data[ram] >= 70) {
			$color = $this->red;
		} elseif ($this->data[ram] >= 60) {
			$color = $this->gold;
		} else {
			$color = $this->green;
		}

		# Color
		if ($this->data[ram_cache] >= 2.5) {
			$color = $this->red;
		} elseif ($this->data[ram_cache] >= 1.5) {
			$color = $this->gold;
		} else {
			$color = $this->green;
		}

		# Echo
		echo '<span style="color: ' . $this->blue . '">[RAM]</span> <span style="color: ' . $color . '">' . $this->data[ram] . '%</span>' . x;
		echo '<span style="color: ' . $this->blue . '">[RAM CACHE]</span> <span style="color: ' . $color . '">' . $this->data[ram_cache] . '%</span>' . x;

		# Echo
		echo '<span style="color: ' . $this->blue . '">[UPTIME]</span> <span style="color: white">' . $this->data[uptime] . ' days</span>' . x;

		# Echo
		if ($this->data[reboot]) {
			echo '<span style="color: ' . $this->blue . '">[REBOOT]</span> <span style="color: ' . $this->gold . '">*** System restart required ***</span>' . xx;
		} else {
			echo '<span style="color: ' . $this->blue . '">[REBOOT]</span> <span style="color: white">no</span>' . xx;
		}
	}

	private function Temperature() {

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_log, "SELECT date, (SELECT ROUND(AVG(temp_1), 2) FROM server) AS ma1, (SELECT ROUND(AVG(temp_2), 2) FROM server) AS ma2, temp_1, temp_2 FROM server WHERE date = (SELECT MAX(date) FROM server)");

		# Result
		foreach (range(1, 2) as $a) $temp[$a] = $sql['temp_' . $a];
		foreach (range(1, 2) as $a) $temp_slope[$a] = max(0, $_SESSION[fun][tool]->Slope($sql['ma' . $a], $temp[$a]));
		foreach (range(1, 2) as $a) $color[$a] = ($temp_slope[$a] >= 20 ? $this->red : ($temp_slope[$a] >= 10 ? $this->gold : $this->green));

		# Echo
		foreach (range(1, 2) as $a) echo '<span style="color: ' . $this->blue . '">[sensor ' . $a . ']</span> <span style="color: ' . $color[$a] . '">' . $temp[$a] . '°C</span>' . x;
		echo x;
   	}

	/*private function Size() {

		# Echo
		echo '<span style="color: white">SIZE</span>' . x;

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_log, "SELECT table_schema AS `database`, FORMAT(ROUND(SUM(data_length + index_length) / (1024 * 1024), 0), 0) AS size FROM information_schema.TABLES WHERE table_schema IN ('ticker', 'indicator', 'log', 'performance', 'trade', 'user', 'wasted') GROUP BY table_schema");

		# Result
		while ($a = mysqli_fetch_assoc($sql)) echo '<span style="color: #559FFF">[' . $a[database] . ']</span> ' . $a[size] . '_MB' . x;
	*/

	private function Ip() {

		# Sql
		$sql = $_SESSION[fun][sql]->Select(d_log, "SELECT ip, ROUND(COUNT(*) / (SELECT COUNT(*) FROM server_connectivity WHERE ip != '') * 100, 2) AS count FROM server_connectivity WHERE ip != '' GROUP BY IP ORDER BY count DESC");

		# Echo
		while ($a = mysqli_fetch_assoc($sql)) echo '<span style="color: ' . $this->blue . '">[' . $a[ip] . ']</span> <span>' . $a[count] . '%</span>' . x;

	   	echo x;
   	}

	private function Load() {

		# Sql
		$sql = $_SESSION[fun][sql]->SelectRow(d_log, "SELECT load_1, load_5, load_15, load_60 FROM server ORDER BY date DESC LIMIT 1");

		# Data
		$data = [
			1 	=> [10, 5],
			5 	=> [6, 3],
			15 	=> [4, 2],
			60 	=> [3, 1.5]
		];

		# Color
		foreach ($data as $k => $v) $color[$k] = ($sql['load_' . $k] >= $v[0] ? $this->red : ($sql['load_' . $k] >= $v[1] ? $this->gold : $this->green));

		# Echo
		foreach ([1, 5, 15, 60] as $a) echo '<span style="color: ' . $this->blue . '">[' . $a . ' min]</span> <span style="color: ' . $color[$a] . '">' . $sql['load_' . $a] . '%</span>' . x;

		# Echo
		echo x;
   	}

	private function Footer() {

		# Echo
		echo '</div>';
	}
}