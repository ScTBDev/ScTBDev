<?php
require_once('../public_up_html/include/classes/bt_utf8.php');
require_once('../public_up_html/include/classes/bt_string.php');
header('Content-Type: text/plain');

ini_set('pcre.recursion_limit', 10000); // Much more and long strings will simply cause PHP to crash on the w3 based PCRE matches

////////////////////////////////////////////////////////////////////////////////
$functions = array(
	'bt_utf8::is_utf8'			=> 'is_utf8',
	'mb_check_encoding'			=> 'check_utf8',
	'mb_detect_encoding'		=> 'detect_utf8',
	'mb_convert_encoding'		=> 'convert_utf8',
	'w3 preg_match'				=> 'w3_is_utf8',
	'modified w3 preg_match'	=> 'modified_w3_is_utf8',
);

function is_utf8($string) {
	return bt_utf8::is_utf8($string);
}

function check_utf8($string) {
	if (!is_string($string))
		return NULL;

	return (bool)mb_check_encoding($string, 'UTF-8');
}

function detect_utf8($string) {
	if (!is_string($string))
		return NULL;

	return mb_detect_encoding($string, array('UTF-8', '8bit'), true) === 'UTF-8';
}

function convert_utf8($string) {
	if (!is_string($string))
		return NULL;

	return mb_convert_encoding(mb_convert_encoding($string, 'UTF-16', 'UTF-8'), 'UTF-8', 'UTF-16') === $string;
}

function w3_is_utf8($string) {
	if (!is_string($string))
		return NULL;

    $valid = preg_match(
		'%^(?:
		  [\x09\x0A\x0D\x20-\x7E]			# ASCII
		| [\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
		|  \xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
		|  \xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
		|  \xF0[\x90-\xBF][\x80-\xBF]{2}	# planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
		|  \xF4[\x80-\x8F][\x80-\xBF]{2}	# plane 16
	)*$%xs', $string);

	if ($valid === false)
		return NULL;

	return (bool)$valid;
}

function modified_w3_is_utf8($string) {
	if (!is_string($string))
		return NULL;

    $valid = preg_match(
		'%^(?:
		  [\x00-\x7F]						# ASCII (including control characters)
		| [\xC2-\xDF][\x80-\xBF]			# non-overlong 2-byte
		|  \xE0[\xA0-\xBF][\x80-\xBF]		# excluding overlongs
		| [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
		|  \xED[\x80-\x9F][\x80-\xBF]		# excluding surrogates
		|  \xF0[\x90-\xBF][\x80-\xBF]{2}	# planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
		|  \xF4[\x80-\x8F][\x80-\xBF]{2}	# plane 16
	)*$%xs', $string);

	if ($valid === false)
		return NULL;

	return (bool)$valid;
}

function decode_utf8($string) {
	return bt_utf8::utf8_to_unicode($string, true);
}

//function valid_utf8($string) {
//	return bt_utf8::valid_utf8($string);
//}

////////////////////////////////////////////////////////////////////////////////

$examples = array(
	'Valid 1 Byte Sequence'									=> array(true, implode('', range("\x00", "\x7f"))),
	'Valid 2 Byte Sequence'									=> array(true, "\xc2\x82"),
	'Valid 3 Byte Sequence'									=> array(true, "\xe2\x82\xa1"),
	'Valid 4 Byte Sequence'									=> array(true, "\xf0\x90\x8c\xbc"),

	'Invalid 2 Byte Sequence'								=> array(false, "\xc3\x28"),

	'Invalid 3 Byte Sequence (in 2nd Byte)'					=> array(false, "\xe2\x28\xa1"),
	'Invalid 3 Byte Sequence (in 3rd Byte)'					=> array(false, "\xe2\x82\x28"),

	'Invalid 4 Byte Sequence (in 2nd Byte)'					=> array(false, "\xf0\x28\x8c\xbc"),
	'Invalid 4 Byte Sequence (in 3rd Byte)'					=> array(false, "\xf0\x90\x28\xbc"),
	'Invalid 4 Byte Sequence (in 4th Byte)'					=> array(false, "\xf0\x28\x8c\x28"),

	'Invalid 5 Byte Sequence (in 2nd Byte)'					=> array(false, "\xf8\x28\xa1\xa1\xa1"),
	'Invalid 5 Byte Sequence (in 3rd Byte)'					=> array(false, "\xf8\xa1\x28\xa1\xa1"),
	'Invalid 5 Byte Sequence (in 4th Byte)'					=> array(false, "\xf8\xa1\xa1\x28\xa1"),
	'Invalid 5 Byte Sequence (in 5th Byte)'					=> array(false, "\xf8\xa1\xa1\xa1\x28"),

	'Invalid 6 Byte Sequence (in 2nd Byte)'					=> array(false, "\xfc\x28\xa1\xa1\xa1\xa1"),
	'Invalid 6 Byte Sequence (in 3rd Byte)'					=> array(false, "\xfc\xa1\x28\xa1\xa1\xa1"),
	'Invalid 6 Byte Sequence (in 4th Byte)'					=> array(false, "\xfc\xa1\xa1\x28\xa1\xa1"),
	'Invalid 6 Byte Sequence (in 5th Byte)'					=> array(false, "\xfc\xa1\xa1\xa1\x28\xa1"),
	'Invalid 6 Byte Sequence (in 6th Byte)'					=> array(false, "\xfc\xa1\xa1\xa1\xa1\x28"),

	'Valid 2 Byte Sequence (but contains Overlong)'			=> array(false, "\xc0\x80"),
	'Valid 3 Byte Sequence (but contains Overlong)'			=> array(false, "\xe0\x80\x80"),
	'Valid 4 Byte Sequence (but contains Overlong)'			=> array(false, "\xf0\x80\x80\x80"),
	'Valid 5 Byte Sequence (but contains Overlong)'			=> array(false, "\xf8\x80\x80\x80\x80"),
	'Valid 6 Byte Sequence (but contains Overlong)'			=> array(false, "\xfc\x80\x80\x80\x80\x80"),

	'Valid 3 Byte Sequence (but contains Surrogate)'		=> array(false, "\xed\xbe\xb4"),

	'Valid 4 Byte Sequence (but above Unicode codepoints)'	=> array(false, "\xf4\x90\xb0\xb0"),
	'Valid 5 Byte Sequence (but above Unicode codepoints)'	=> array(false, "\xf8\xa1\xa1\xa1\xa1"),
	'Valid 6 Byte Sequence (but above Unicode codepoints)'	=> array(false, "\xfc\xa1\xa1\xa1\xa1\xa1"),

	'Invalid Sequence Identifier'							=> array(false, "\xa0\xa1"),
	'Invalid Sequence (not defined in UTF-8 spec)'			=> array(false, "\xfe\xff"),

	'Prematurely ended 2 Byte Sequence'						=> array(false, "\xc2"),
	'Prematurely ended 3 Byte Sequence'						=> array(false, "\xe2\x83"),
	'Prematurely ended 4 Byte Sequence'						=> array(false, "\xf0\x90\x8d"),
	'Prematurely ended 5 Byte Sequence'						=> array(false, "\xf8\xa1\xa1\xa1"),
	'Prematurely ended 6 Byte Sequence'						=> array(false, "\xfc\xa1\xa1\xa1\xa1"),
);

////////////////////////////////////////////////////////////////////////////////

echo 'Building string of all possible valid UTF-8 characters ... ';
$time = microtime(true);
$utf8 = '';
for ($i = 0; $i < 0x110000; $i++) {
	$char = bt_utf8::unicode_to_utf8($i);
	if ($char === false)
		continue;

	$utf8 .= $char;
}
$time = microtime(true) - $time;
$utf8_len = strlen($utf8);
echo 'DONE in '.number_format($time, 5).'s ('.$utf8_len.' bytes)'."\n";
echo 'Building random data string of same length ............... ';
$time = microtime(true);
$data = bt_string::random($utf8_len);
$time = microtime(true) - $time;
$data_len = strlen($data);
echo 'DONE in '.number_format($time, 5).'s ('.$data_len.' bytes)'."\n\n";

$num = 64;
echo "\n".'----------------------------------------------------------------------------'."\n";
foreach ($functions as $name => $func) {
	$failed = false;
	echo $name.'():'."\n";
	foreach ($examples as $name => $code) {
		$result = $func($code[1]);
		if ($result !== $code[0]) {
			$failed = true;
			echo "\t".$name.': FAILED'."\n";
		}
	}

	echo "\n";
	if ($failed)
		echo 'Failed to validate all UTF-8 conditions correctly.';
	else
		echo 'Passed validation of all UTF-8 conditions.';

	echo "\n\n";

	echo "\n".'Testing '.$num.' iterations with Random Data ... ';
	$time = microtime(true);
	for ($i = 0; $i < $num; $i++) {
		$result = $func($data);
	}
	$time = microtime(true) - $time;
	echo ($result === false ? 'PASSED' : 'FAILED').' after '.number_format($time, 5).'s';
	echo "\n".'Testing '.$num.' iterations with Valid Data .... ';
	$time = microtime(true);
	for ($i = 0; $i < $num; $i++) {
		$result = $func($utf8);
	}
	$time = microtime(true) - $time;
	echo ($result === true ? 'PASSED' : 'FAILED').' after '.number_format($time, 5).'s';

	echo "\n\n".'----------------------------------------------------------------------------'."\n";
}
?>
