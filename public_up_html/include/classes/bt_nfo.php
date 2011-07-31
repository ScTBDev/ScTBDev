<?php
/*
 *	ScTBDev - A bittorrent tracker source based on SceneTorrents.org
 *	Copyright (C) 2005-2011 ScTBDev.ca
 *
 *	This file is part of ScTBDev.
 *
 *	ScTBDev is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	ScTBDev is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU General Public License for more details.
 *
 *	You should have received a copy of the GNU General Public License
 *	along with ScTBDev.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(__DIR__.DIRECTORY_SEPARATOR.'class_config.php');
require_once(CLASS_PATH.'bt_string.php');
require_once(CLASS_PATH.'bt_theme.php');

class bt_nfo {
	private static $nfos = array(
		128 => 199, 129 => 252, 130 => 233, 131 => 226, 132 => 228, 133 => 224, 134 => 229, 135 => 231,
		136 => 234, 137 => 235, 138 => 232, 139 => 239, 140 => 238, 141 => 236, 142 => 196, 143 => 197,
		144 => 201, 145 => 230, 146 => 198, 147 => 244, 148 => 246, 149 => 242, 150 => 251, 151 => 249,
		152 => 255, 153 => 214, 154 => 220, 155 => 162, 156 => 163, 157 => 165, 158 => 8359, 159 => 402,
		160 => 225, 161 => 237, 162 => 243, 163 => 250, 164 => 241, 165 => 209, 166 => 170, 167 => 186,
		168 => 191, 169 => 8976, 170 => 172, 171 => 189, 172 => 188, 173 => 161, 174 => 171, 175 => 187,
		176 => 9617, 177 => 9618, 178 => 9619, 179 => 9474, 180 => 9508, 181 => 9569, 182 => 9570, 183 => 9558,
		184 => 9557, 185 => 9571, 186 => 9553, 187 => 9559, 188 => 9565, 189 => 9564, 190 => 9563, 191 => 9488,
		192 => 9492, 193 => 9524, 194 => 9516, 195 => 9500, 196 => 9472, 197 => 9532, 198 => 9566, 199 => 9567,
		200 => 9562, 201 => 9556, 202 => 9577, 203 => 9574, 204 => 9568, 205 => 9552, 206 => 9580, 207 => 9575,
		208 => 9576, 209 => 9572, 210 => 9573, 211 => 9561, 212 => 9560, 213 => 9554, 214 => 9555, 215 => 9579,
		216 => 9578, 217 => 9496, 218 => 9484, 219 => 9608, 220 => 9604, 221 => 9612, 222 => 9616, 223 => 9600,
		224 => 945, 225 => 223, 226 => 915, 227 => 960, 228 => 931, 229 => 963, 230 => 956, 231 => 964,
		232 => 934, 233 => 920, 234 => 937, 235 => 948, 236 => 8734, 237 => 966, 238 => 949, 239 => 8745,
		240 => 8801, 241 => 177, 242 => 8805, 243 => 8804, 244 => 8992, 245 => 8993, 246 => 247, 247 => 8776,
		248 => 176, 249 => 8729, 250 => 183, 251 => 8730, 252 => 8319, 253 => 178, 254 => 9632, 255 => 160,
	);

	private static $control = array(
		0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 15, 16, 17, 18,
		19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 127,
	);

	public static function nfo2html($nfo) {
		$html = htmlspecialchars($nfo, true);
		$search = array();
		$replace = array();
		foreach (self::$nfos as $char => $ent) {
			$search[] = bt_string::$chr[$char];
			$replace[] = sprintf('&#x%04x;', $ent);
		}

		foreach (self::$control as $char) {
			$search[] = bt_string::$chr[$char];
			$replace[] = ' ';
		}

		$html = str_replace($search, $replace, $html);
		return $html;
	}

	public static function strip_cdkeys($nfo) {
		$search = $replace = array();
		if (preg_match_all('/(?:[0-9A-Z]{4,10})(?:-[0-9A-Z]{4,10}){3,11}/', $nfo, $matches)) {
			foreach ($matches[0] as $match) {
				$search[] = $match;
				$replace = preg_replace('#[A-Z0-9]#', 'X', $match);
			}
		}

		if (count($search) && count($replace))
			$nfo = str_replace($search, $replace, $nfo);

		return $nfo;
	}

	public static function strip($nfo, $strip_cdkeys = true) {
		$nfo = preg_replace('/\&\#([0-9]+);?/', '', $nfo);
		$shit = array(array(0, 9), array(11, 12), array(14, 31), 33, 36, array(42, 43), array(59, 60), 62,64, array(91, 96), array(123, 255));
		$stains = array();
		foreach ($shit as $stain) {
			if (is_array($stain)) {
				for ($i = $stain[0]; $i <= $stain[1]; $i++)
					$stains[] = bt_string::$chr[$i];
			}
			else
				$stains[] = bt_string::$chr[$stain];
		}

		$on = str_replace($stains, '', $nfo);
		$the = str_replace(array('_',"\r\n","\r") , array(' ',"\n","\n"), $on);
		$wall = explode("\n", $the);

		foreach($wall as $smell) {
			$smell = trim($smell, '.:/%&#?-"\'()');
			$smell = trim($smell);
			if ($smell !== $lastsmell)
				$gross .= $smell."\n";
			$lastsmell = $smell;
		}

		$clean = $strip_cdkeys ? self::strip_cdkeys($gross) : $gross;

		return $clean;
	}

	public static function clean($nfo) {
		return str_replace(array("\r\n","\r") , array("\n","\n"), $nfo);
	}

	public static function nfo2png($nfo, $destfile = NULL) {
		// Get the NFO contents + create image
		$content = explode("\n", self::clean($nfo));
		$len = 80;
		foreach ($content as $string) {
			$strlen = strlen($string);
			if ($strlen > $len)
				$len = min($strlen, 120);
		}
		reset($content);
		$img = imagecreate((6 * $len), (8 * count($content)));

		// Define colours
		$textcolor = bt_theme::$settings['viewnfo']['colour'];
		$backgroundcolor =  bt_theme::$settings['viewnfo']['bg_colour'];

		$bgcolor = imagecolorallocate($img, $backgroundcolor[0], $backgroundcolor[1], $backgroundcolor[2]);
		$bgcolor = imagecolortransparent($img, $bgcolor);
		$txtcolor = imagecolorallocate($img, $textcolor[0], $textcolor[1], $textcolor[2]);
		$font = imageloadfont(ROOT_PATH.'fonts'.DIRECTORY_SEPARATOR.'6x8.dft');

		// Draw each line of text
		foreach($content as $line => $string)
			imagestring($img, $font, 0, (8 * $line), $string, $txtcolor);

		// Save the PNG file
		if ($destfile) {
			$png = imagepng($img, $destfile, 9, PNG_NO_FILTER);
			chmod($destfile, 0644);
		}
		else {
			ob_start();
			imagepng($img, NULL, 9, PNG_NO_FILTER);
			$png = ob_get_contents();
			ob_end_clean();
		}
		imagedestroy($img);
		return $png;
	}

	public static function nfo_name($nfo, $theme = NULL) {
		$id = sha1($nfo, true);
		$theme = is_null($theme) ? bt_theme_engine::$theme : $theme;
		$name = $id.'_'.$theme;
		return base_convert(md5($name), 16, 36);
	}
}
?>
