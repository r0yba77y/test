<?php
class Api_wrap {

	public function TradeAdd($data) {

		# Compute
		if ($data) {

			# Result
			$result = $data;

		} else {

			# Result
			$result = [
				cluster => 0,
				call    => 0,
				status	=> call_error
			];
		}

		# Return
		return $result;
	}
}