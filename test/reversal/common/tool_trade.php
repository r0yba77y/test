<?php
class Tool_trade {

	public function PriceOffset($ticker, $position, $price, $strategy) {

		# Offset
		$offset = $_SESSION[mem][strategy][$strategy][offset];

		# Compute
		if ($offset) {

			# Result
			if ($position == long) {
				$result = $_SESSION[fun][tool]->Percentage($price, -$offset);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, max, price);
			} else {
				$result = $_SESSION[fun][tool]->Percentage($price, $offset);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, min, price);
			}

		} else {

			# Result
			$result = $price;
		}

		# Return
		return $result;
	}

	public function StopLoss($ticker, $position, $price, $strategy) {

		# Stop loss
		$stop_loss = $_SESSION[mem][strategy][$strategy][stop_loss] ?? 0;

		# Compute
		if ($stop_loss) {

			# Result
			if ($position == long) {
				$result = $_SESSION[fun][tool]->Percentage($price, -$stop_loss);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, max, price);
			} else {
				$result = $_SESSION[fun][tool]->Percentage($price, $stop_loss);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, min, price);
			}

		} else {

			# Result
			$result = 0;
		}

		# Return
		return $result;
	}

	public function TakeProfit($ticker, $position, $price, $strategy) {

		# Take profit
		$take_profit = $_SESSION[mem][strategy][$strategy][take_profit] ?? 0;

		# Compute
		if ($take_profit) {

			# Result
			if ($position == long) {
				$result = $_SESSION[fun][tool]->Percentage($price, $take_profit);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, max, price);
			} else {
				$result = $_SESSION[fun][tool]->Percentage($price, -$take_profit);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, min, price);
			}

		} else {

			# Result
			$result = 0;
		}

		# Return
		return $result;
	}

	public function Reverse($ticker, $reverse, $position, $price) {

		# Compute
		if ($reverse) {

			# Result
			if ($position == long) {
				$result = $_SESSION[fun][tool]->Percentage($price, $reverse);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, max, price);
			} else {
				$result = $_SESSION[fun][tool]->Percentage($price, -$reverse);
				$result = (float)$_SESSION[fun][tool]->RoundPrice($ticker, $result, min, price);
			}

		} else {

			# Result
			$result = 0;
		}

		# Return
		return $result;
	}
}