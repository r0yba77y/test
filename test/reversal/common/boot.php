<?php
class Boot {

	public function Exec() {

		# Result
		$result = 0;

		# Compute
		if ($this->Require()) {

			# Error
			set_error_handler([$_SESSION[fun][tool], 'Error1']);
			set_exception_handler([$_SESSION[fun][tool], 'Error2']);

			# Header
			$_SESSION[fun][visual]->Header();

			# Compute
			if (ses_request) {

				# Tag
				$_SESSION[fun][visual]->Tag(start, '', 1, true) . xx;

				# Exec
				$_SESSION[fun][ses_request]->Exec();

				# Result
				$result = 1;
			}
		}

		# Return
		return $result;
	}

	private function Require() {

		# Requests
		$requests = [

			# Common
			tool 		   => 'common/tool',
			tool_trade	   => 'common/tool_trade',
			visual 		   => 'common/visual',
			sql 		   => 'common/sql',
			sql_cnx 	   => 'common/sql_cnx',
			setting		   => 'common/setting',
			cache 	 	   => 'common/cache',
			connection 	   => 'common/connection',
			maintenance	   => 'common/maintenance',
			test 		   => 'common/test',
			alive 	 	   => 'common/alive',

			# Dashboard
			dashboard 	   => 'dashboard/dashboard',
			server 		   => 'dashboard/server',

			# Engine
			api			   	=> 'engine/api',
			api_wrap	   	=> 'engine/api_wrap',
			candle		   	=> 'engine/candle',
			candle_volume   => 'engine/candle_volume',
			forex		   	=> 'engine/forex',
			trade_add	    => 'engine/trade_add',
			trade_call	    => 'engine/trade_call',
			trade_call_send => 'engine/trade_call_send',
			trade_history   => 'engine/trade_history',
			trade_order     => 'engine/trade_order'
		];

		# Compute
		if (in_array(ses_request, array_keys($requests))) {

			# Compute
			foreach ($requests as $request => $file) {

				# Require
				require ses_path . $file . '.php';
				
				# New
				if (class_exists($request)) $_SESSION[fun][$request] = new $request();
			}

			# Result
			$result = 1;

		} else {	

			# Result
			$result = 0;
		}

		# Return
		return $result;
	}
}