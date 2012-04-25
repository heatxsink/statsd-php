<?php

class StatsD {

	public static $HOSTNAME = "some.hostname.here";
	public static $PORT_NUMBER = 8125;

	private function __construct() { }
	private function __clone() { }
	private function __destruct() { }

	/**
	 * Log timing information
	 *
	 * @param string $stats The metric to in log timing info for.
	 * @param float $time The ellapsed time (ms) to log
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 **/
	public static function timing($stat, $time, $sample_rate = 1) {
		StatsD::send(array($stat => "$time|ms"), $sample_rate);
	}

	/**
	 * Increments one or more stats counters
	 *
	 * @param string|array $stats The metric(s) to increment.
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 * @return boolean
	 **/
	public static function increment($stats, $sample_rate = 1) {
		StatsD::update_stats($stats, 1, $sample_rate);
	}

	/**
	 * Decrements one or more stats counters.
	 *
	 * @param string|array $stats The metric(s) to decrement.
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 * @return boolean
	 **/
	public static function decrement($stats, $sample_rate = 1) {
		StatsD::update_stats($stats, -1, $sample_rate);
	}

	/*
	 * Updates one or more stats counters by arbitrary amounts.
	 *
	 * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
	 * @param int|1 $delta The amount to increment/decrement each metric by.
	 * @param float|1 $sample_rate the rate (0-1) for sampling.
	 * @return boolean
	 */
	public static function update_stats($stats, $delta = 1, $sample_rate = 1) {
		if (!is_array($stats)) {
			$stats = array($stats);
		}
		$data = array();
		foreach ($stats as $stat) {
			$data[$stat] = "$delta|c";
		}
		StatsD::send($data, $sample_rate);
	}

	/*
	 * Send the metrics over UDP
	 */
	public static function send($data, $sample_rate = 1) {
		$sampled_data = array();
		if ($sample_rate < 1) {
			foreach ($data as $stat => $value) {
				if ((mt_rand() / mt_getrandmax()) <= $sample_rate) {
					$sampled_data[$stat] = "$value|@$sample_rate";
				}
			}
		} else {
			$sampled_data = $data;
		}
		if (empty($sampled_data)) {
			return;
		}
		try {
			$host = StatsD::$HOSTNAME;
			$port = StatsD::$PORT_NUMBER;
			$fp = fsockopen("udp://$host", $port, $errno, $errstr);
			if (!$fp) {
				return;
			}
			foreach ($sampled_data as $stat => $value) {
				fwrite($fp, "$stat:$value");
			}
			fclose($fp);
		} catch (Exception $e) {
			//You should probably plug-in your web applications logging here, else errors will not be handled.
		}
	}
}

?>