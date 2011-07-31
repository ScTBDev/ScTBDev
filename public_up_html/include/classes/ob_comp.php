<?php
/*
 *  ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *  Copyright (C) 2005-2011 ScTBDev.ca
 *
 *  This file is part of ScTBDev.
 *
 *  ScTBDev is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  ScTBDev is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 *	Class: ob_comp
 *	Author: djGrrr
 *	Version: 1.0.0
 *	Description: This class is for use as an output buffer callback in ob_start. It will
 *		automatically determine between using bzip2, gzip, or deflate compression output
 *		for pages. It will also try to make sure that it is the top level output buffer
 *		and if not, default to no compression and trigger an error.
*/

class ob_comp {
	const NONE = 0;
	const COMPRESS = 1;		// not used, php doesn't have support for LZW compression, which is ancient 
	const DEFLATE = 2;		// only avaliable if zlib extension loaded
	const GZIP = 3;			// only avaliable if zlib extension loaded
	const BZIP2 = 4;		// only avaliable if bzip2 extension loaded

	const BUFFER_LIMIT = 5;	// the maximum number of times to try and clear all output buffers

	private static $encoding = NULL;

	public static $bzip2_level = 0;		// don't use bzip2 by default, it's just too slow
	public static $gzip_level = 7;
	public static $deflate_level = 7;
	public static $enable_md5s = false;

	private static $buffer = b'';
	private static $done = false;

	public static function handler($data, $mode) {
		if (self::$done) {
			trigger_error(__METHOD__.' has already ended, this should not happen', E_USER_WARNING);
			return false;
		}

		if ($mode & PHP_OUTPUT_HANDLER_START) {
			if (self::$encoding !== NULL) {
				trigger_error(__METHOD__.' cannot be started more than once', E_USER_WARNING);
				return false; // can't do multiple output buffers with this handler
			}

			if (!self::detect_encoding())
				return false;
		}

		if (strlen($data))
			self::$buffer .= $data;

		if ($mode & PHP_OUTPUT_HANDLER_END) {
			self::$done = true;

			switch (self::$encoding) {
				case self::BZIP2:
					header('Content-Encoding: bzip2');
					$output = bzcompress(self::$buffer, self::$bzip2_level);
				break;

				case self::GZIP:
					header('Content-Encoding: gzip');
					$output = gzencode(self::$buffer, self::$gzip_level, FORCE_GZIP);
				break;

				case self::DEFLATE:
					header('Content-Encoding: deflate');
					$output = gzdeflate(self::$buffer, self::$deflate_level);
				break;

				default:
					$output = self::$buffer;
				break;
			}

			self::$buffer = '';
			header('Vary: Accept-Encoding');
			header('Content-Length: '.strlen($output));
			if (self::$enable_md5s)
				header('Content-MD5: '.base64_encode(hash('md5', $output, true)));

			return $output;
		}
	}

	private static function detect_encoding() {
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			self::$encoding = self::NONE;
			return false;
		}

		$encodings = strtolower($_SERVER['HTTP_ACCEPT_ENCODING']);

		$zlib = extension_loaded('zlib');
		$bzip2 = self::$bzip2_level && extension_loaded('bz2');
		$gzip = self::$gzip_level && $zlib;
		$deflate = self::$deflate_level && $zlib;

		if (defined('NO_OUTPUT_COMPRESSION'))
			self::$encoding = self::NONE;

		elseif ($bzip2 && (strpos($encodings, 'bzip') !== false))
			self::$encoding = self::BZIP2;
		elseif ($deflate && (strpos($encodings, 'deflate') !== false))
			self::$encoding = self::DEFLATE;
		elseif ($gzip && (strpos($encodings, 'gzip') !== false))
			self::$encoding = self::GZIP;
		else
			self::$encoding = self::NONE;	// default to no compression, "identity"

		return true;
	}

	private static function clear_buffers() {
		$attempts = 0;
		while ($level = ob_get_level()) {
			if ($attempts > self::BUFFER_LIMIT) {
				trigger_error('Failed to clear all output buffers in '.__METHOD__.', still at level '.$level.' after '.$attempts.
					' attempts to clear all buffers', E_USER_WARNING);
				return false;
			}
			$attempts++;
			ob_end_clean();
		}
		return true;
	}

	public static function start() {
		if (self::clear_buffers())
			ob_start(array(__CLASS__, 'handler'), 0);
	}
}
?>
