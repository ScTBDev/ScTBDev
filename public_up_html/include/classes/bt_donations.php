<?php
require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(CLASS_PATH.'bt_memcache.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_sql.php');

class bt_donations {
	private static $openssl = '/usr/bin/openssl';
	private static $local_key = '';
	private static $local_cert = '';
	private static $paypal_key = '';
	private static $tmp_dir = '';
	private static $secret = '';
	private static $s1 = '0123456789ABCDEF';
	private static $s2 = 'ABCDEFGHIJKLMNOP';

	private static $emails = array('donate@sctbdev.ca');

	private static function days(&$fromdt, &$todt, $nextday, $time = NULL) {
		$curdate = gmdate('Y-n-j', is_null($time) ? time() : $time);
		$date = explode('-', $curdate);

		$year   = 0 + $date[0];
		$month  = 0 + $date[1];
		$day    = 0 + $date[2];

		if ($day < $nextday) {
			$fromdt = ($month > 1) ? gmmktime(0, 0, 0, ($month - 1), $nextday, $year) : gmmktime(0, 0, 0, 12, $nextday, ($year - 1));
			$todt = gmmktime(0, 0, 0, $month, $nextday, $year);
		}
		else {
			$fromdt = gmmktime(0, 0, 0, $month, $nextday, $year);
			$todt = ($month < 12) ? gmmktime(0, 0, 0, ($month + 1), $nextday, $year) : gmmktime(0, 0, 0, 1, $nextday, ($year + 1));
		}
	}

	public static function get_donations($day) {
		bt_memcache::connect();
		$donations = bt_memcache::get('stats:donations');
		if ($donations === bt_memcache::NO_RESULT) {
			self::days($fromdt, $todt, $day);
			$emails = implode(', ', array_map(array('bt_sql', 'esc'), self::$emails));
			$sql = 'SELECT (SUM(`payment_amount`) - SUM(`payment_fee`)) AS `donations` FROM `don_attempt` WHERE `receiver_email` IN ('.$emails.') AND `payment_status` IN ("Completed", "Refunded", "Reversed") AND `verified` != "fake" AND `item_name` IN ("Order") AND `payment_date` BETWEEN '.$fromdt.' AND '.$todt;
			$q = bt_sql::query($sql);
			$row = $q->fetch_assoc();
			$q->free();
			$donations = array(
				'ammount'	=> round((float)$row['donations']),
				'from'		=> $fromdt,
				'to'		=> $todt,
			);
			bt_memcache::add('stats:donations', $donations, 900);
		}
		return $donations;
	}

	public static function encrypt($hash) {
		if (!file_exists(self::$openssl) || !is_executable(self::$openssl) ||
			!file_exists(self::$local_key) || !file_exists(self::$paypal_key) ||
			!is_dir(self::$tmp_dir) || !is_writable(self::$tmp_dir))
			return false;

		$keys = array();
		foreach ($hash as $key => $value) {
			if ($value !== '')
				$keys[] = $key.'='.$value;
		}
		$data = join("\n", $keys);
		$tmpvar = 'HOME='.self::$tmp_dir;
		
		$openssl_cmd = $tmpvar.' '.self::$openssl.' smime -sign -signer '.self::$local_cert.' -inkey '.self::$local_key.
			' -outform der -nodetach -binary | '.$tmpvar.' '.self::$openssl.' smime -encrypt -des3 -binary -outform pem '.
			self::$paypal_key;

		$descriptors = array(
			array('pipe', 'r'),
			array('pipe', 'w'),
			array('pipe', 'w'),
		);


		$process = proc_open($openssl_cmd, $descriptors, $pipes);

		if (is_resource($process)) {
			fwrite($pipes[0], $data);
			fclose($pipes[0]);
			$output = trim(stream_get_contents($pipes[1]));
			fclose($pipes[1]); 
			$error = trim(stream_get_contents($pipes[2]));
			fclose($pipes[2]);
			if ($error != '') {
				trigger_error('openssl returned error: '.$error, E_USER_WARNING);
				return false;
			}
			$return_value = proc_close($process);
			return $output ? $output : false;
		}
		return false;
	}

	public function encode($userid, $type, $amount, $opt) {
		$don_id = array('u'.$userid, 't'.$type, 'a'.$amount, 'o'.$opt, 'r'.mt_rand(100,999));
		shuffle($don_id);
		$don = implode(' ',$don_id);
		$donation_id = strtr(strtoupper(bt_string::str2hex(bt_string::xor_string($don, self::$secret))), self::$s1, self::$s2);
		return $donation_id;
	}

	public function decode($donid) {
		$donation = bt_string::xor_string(bt_string::hex2str(strtr($donid, self::$s2, self::$s1)), self::$secret);
		$parts = array();
		$dons = explode(' ', $donation);
		foreach ($dons as $don) {
			if (!preg_match('/^([a-z])([0-9]+)$/', $don, $mat))
				return false;
			$parts[$mat[1]] = $mat[2];
		}

		if (!isset($parts['r']) || !isset($parts['u']) || !isset($parts['t'])|| !isset($parts['a']) || !isset($parts['o']))
			return false;

		return array(
			'userid'	=> (int)$parts['u'],
			'type'		=> (int)$parts['t'],
			'amount'	=> (int)$parts['a'],
			'opt'		=> (int)$parts['o'],
		);
	}
}
?>
