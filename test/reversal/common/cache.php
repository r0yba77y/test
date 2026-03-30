<?php
class Cache {

	private $cache;

	public function __construct() {

		# Open
		$this->Open();
	}
	
	private function Open() {

		# Compute
		$this->cache = new Redis();
		$this->cache->Connect(ses_host_redis, 6379);
		$this->cache->Auth($_SESSION[mem][token][redis]);
	}

	public function Id($id) {

		# Result
		if (is_array($id)) {
			$result = implode(';', array_filter($id));
		} else {
			$result = $id;
		}

		# Return
		return $result;
	}

	public function Add($id, $expiration = 0, $data = null) {

		# Id
		$id = $this->Id($id);

		# Expiration
		$expiration = max(60, $expiration);

		# Data
		if (is_array($data)) $data = json_encode($data);

		# Compute
		$this->cache->Set($id, $data);
		$this->cache->Expire($id, $expiration);

		# Tag
		$_SESSION[fun][visual]->TagCache(add, $id);		
	}

	public function Get($id) {

		# Id
		$id = $this->Id($id);

		# Data
		$data = $this->cache->Get($id);

		# Result
		if ($this->cache->Exists($id)) {
			$result = json_decode($data, true);
			if (!$result) $result = $data;
		} else {
			$result = null;
		}

		# Tag
		$_SESSION[fun][visual]->TagCache(get_, $id);

		# Return
		return $result;
	}

	public function Exists($id) {

		# Id
		$id = $this->Id($id);

		# Data
		$result = (int)$this->cache->Exists($id);

		# Return
		return $result;
	}

	public function Ready($request) {

		# Timeout
		$timeout = $_SESSION[mem][request][php][$request][ready];

		# Compute
		if ($timeout) {

			# Id
			$id = [$request, date('i')];

			# Limit
			$limit = $timeout;

			# Timeout
			$timeout *= 1000000;

			# Ping
			$ping = microtime(1);

			# Compute
			while (1) {

				# Temp
				$temp = $this->Get($id);

				# Result ?
				if (!is_null($temp)) {

					# Return
					return 1;

				} elseif (($limit && date('s') >= $limit) || (microtime(1) - $ping >= $timeout)) {

					# Error ready
					$_SESSION[fun][tool]->ErrorReady($id);

					# Return
					return 0;
				}

				# Wait
				usleep(50000);
			}

		} else {

			# Return
			return 1;
		}
	}

	public function Dump() {

		# Datas
		$datas = $this->cache->Keys('*');

		# Data
		foreach ($datas as $a) {
			$data[$a] = $this->cache->Get($a);
		}

		# Compute
		if (is_array($data)) {

			# Compute
			foreach ($data as $a => $null) {

				# Temp
				$temp = explode(';', $a);

				# Result
				$result[$temp[0]][] = $a;
			}

			# Result
			ksort($result);
			foreach ($result as $a => $null) {
				ksort($result[$a]);
				$result[$a] = array_values($result[$a]);
				foreach ($result[$a] as $b => $null) {
					if (is_array($result[$a][$b])) {
						asort($result[$a][$b]);
						$result[$a][$b] = array_values($result[$a][$b]);
					}
				}
			}

		} else {

			# Echo
			echo 'NO DATA';
		}

		# Print
		$_SESSION[fun][visual]->Print($result);
	}

	public function List() {

		# Result
		$result = $this->cache->Keys('*');

		# Return
		return $result;
	}

	public function Memory($id = null) {

		# Result
		$result = $this->cache->Info(memory);

		# Result
		$result = round($result['used_memory'] / 1048576, 2);

		# Return
		return $result;
	}

	public function Delete($id) {

		# Id
		$id = $this->Id($id);

		# Compute
		$this->cache->Del($id);

		# Tag
		$_SESSION[fun][visual]->TagCache(delete, $id);
	}

	public function DeleteAll() {

		# Compute
		$this->cache->flushDB();

		# Tag
		$_SESSION[fun][visual]->TagCache(delete, 'all');
	}

	public function Close() {

		# Compute
		$this->cache->Close();
	}
}