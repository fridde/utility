<?php

namespace Fridde;

class Utility
{
	/**
	* [filterFor description]
	* @param  Array $array    [description]
	* @param  array $criteria [[$field_1, $value_1, $comparison_operator_1], [$field_2, $value_2, $comparison_operator_2],...] Accepts one-dimensional array, too.
	*                         default for just one parameter within $criteria is [$field_1, "", "!="], for two parameters it's [$field_1, $value_1, "="]
	* @return Array           [description]
	*/
	public static function filterFor($array, $criteria = [], $return_single = true)
	{
		$only_arrays = count(array_filter($criteria, "is_array"))  == count($criteria);
		if(!$only_arrays){
			$criteria = [$criteria];
		}

		foreach($criteria as $c){
			$c = array_map("trim", $c);
			$crit_length = count($c);
			if($crit_length === 1){
				$c = [$c[0], "", "!="];
			} elseif ($crit_length === 2) {
				$c = [$c[0], $c[1], "=="];
			} elseif ($crit_length !== 3) {
				throw new \Exception("Non-valid criterium given: " . var_export($c, true));
			}

			$filter_function = function($row) use ($c){
				list($field, $value, $comp_operator) = $c;
				$cell = $row[$field];
				switch($comp_operator){
					case "==":
					return $cell == $value;
					break;
					case "!=":
					return $cell != $value;
					break;
					case ">":
					return $cell > $value;
					break;
					case "<":
					return $cell < $value;
					break;
					case "in":
					return in_array($cell, $value);
					break;
					case "not_in":
					return !(in_array($cell, $value));
					break;
					case "before":
						return strtotime($cell) - strtotime($value) < 0;
						break;

						case "after":
						return strtotime($cell) - strtotime($value) > 0;
						break;

						default:
						throw new \Exception("Operator " . $comp_operator . " not defined.");
					}
				};
				$array = array_filter($array, $filter_function);
			}
			if($return_single && count($array) === 1){
				$array = reset($array);
			}
			return $array;
		}

		/**
		 * [getById description]
		 * @param  [type] $array     [description]
		 * @param  [type] $id_value  [description]
		 * @param  string $id_column [description]
		 * @return [type]            [description]
		 */
		public static function getById($array, $id_value, $id_column = "id")
		{
			$result = self::filterFor($array, [$id_column, $id_value]);
			if(empty($result)){
				return $array[$id_value] ?? false;
			}
			return $result;
		}


		public static function arrayIsMulti($array) {
			foreach ($array as $value) {
				if (is_array($value)) return true;
			}
			return false;
		}

		/**
		* SUMMARY OF redirect
		*
		* DESCRIPTION
		*
		* @param TYPE ($to) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/


		public static function redirect($to)
		{
			@session_write_close();
			if (!headers_sent()) {
				header("Location: $to");
				flush();
				exit();
			} else {
				print "<html><head><META http-equiv='refresh' content='0;URL=$to'></head><body><a href='$to'>$to</a></body></html>";
				flush();
				exit();
			}
		}
		/**
		* SUMMARY OF get_all_files
		*
		* DESCRIPTION
		*
		* @param TYPE ($dir = 'files') ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function get_all_files($dir = 'files')
		{
			$fileArray = array();
			$handle = opendir($dir);

			while (false !== ($entry = readdir($handle))) {
				if (!in_array($entry, array(
					".",
					".."
				))) {
					$fileArray[] = $entry;
				}
			}
			closedir($handle);
			sort($fileArray);

			return $fileArray;
		}

		/**
		* Returns the current url of the page.
		*
		* DESCRIPTION
		*
		* @param TYPE () ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function curPageURL()
		{
			$pageURL = 'http';
			if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
				$pageURL .= "s";
			}
			$pageURL .= "://" . $_SERVER["SERVER_NAME"];
			if ($_SERVER["SERVER_PORT"] != "80") {
				$pageURL .= ":" . $_SERVER["SERVER_PORT"];
			}
			$pageURL .= $_SERVER["REQUEST_URI"];
			return $pageURL;
		}
		/**
		* [Summary].
		*
		* [Description]
		*
		* @param [Type] $[Name] [Argument description]
		*
		* @return [type] [name] [description]
		*/
		public static function print_r2($val, $return = false)
		{
			if($return){
				$r = var_export($val, true);
				return $r;
			}
			else {
				echo '<pre>' . var_export($val, true) . '</pre>';
			}
		}


		/**
		* SUMMARY OF csvstring_to_array
		*
		* DESCRIPTION
		*
		* @param TYPE ($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = "\n") ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function csvstring_to_array($string, $separatorChar = ',', $enclosureChar = '"', $newlineChar = "\n")
		{

			$array = array();
			$size = strlen($string);
			$columnIndex = 0;
			$rowIndex = 0;
			$fieldValue = "";
			$isEnclosured = false;
			for ($i = 0; $i < $size; $i++) {

				$char = $string{$i};
				$addChar = "";

				if ($isEnclosured) {
					if ($char == $enclosureChar) {

						if ($i + 1 < $size && $string{$i + 1} == $enclosureChar) {
							// escaped char
							$addChar = $char;
							$i++;
							// dont check next char
						}
						else {
							$isEnclosured = false;
						}
					}
					else {
						$addChar = $char;
					}
				}
				else {
					if ($char == $enclosureChar) {
						$isEnclosured = true;
					}
					else {

						if ($char == $separatorChar) {
							$array[$rowIndex][$columnIndex] = $fieldValue;
							$fieldValue = "";

							$columnIndex++;
						}
						elseif ($char == $newlineChar) {
							$array[$rowIndex][$columnIndex] = $fieldValue;
							$fieldValue = "";
							$columnIndex = 0;
							$rowIndex++;
						}
						else {
							$addChar = $char;
						}
					}
				}
				if ($addChar != "") {
					$fieldValue .= $addChar;

				}
			}

			if ($fieldValue) {// save last field

				$array[$rowIndex][$columnIndex] = $fieldValue;
			}

			return $array;
		}
		/**
		* SUMMARY OF remove_whitelines
		*
		* DESCRIPTION
		*
		* @param TYPE ($array) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function remove_whitelines($array)
		{

			foreach ($array as $key => $row) {
				if (strlen(trim(implode($row))) == 0) {
					$array[$key] = NULL;
				}
			}
			$array = array_filter($array);
			return $array;
		}
		/**
		* SUMMARY OF dateRange
		*
		* DESCRIPTION
		*
		* @param TYPE ($first, $last, $step = "+1 day", $format = "Y-m-d", $addLast = TRUE) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function dateRange($first, $last, $step = "+1 day", $format = "Y-m-d", $addLast = TRUE)
		{

			$step = date_interval_create_from_date_string($step);

			$dates = array();
			$current = date_create_from_format($format, $first);
			$last = date_create_from_format($format, $last);

			while ($current <= $last) {
				$dates[] = $current -> format($format);
				$current = date_add($current, $step);
			}

			if ($addLast && end($dates) != $last) {
				$dates[] = $last -> format($format);
			}

			return $dates;
		}

		/**
		* [orderArrayBy description]
		* @param  [type] $array    [description]
		* @param  [type] $key      [description]
		* @param  [type] $callable [description]
		* @return [type]           [description]
		*/
		public static function orderBy($array, $key, $callable = "lexical")
		{
			if( ! is_string($callable)){
				$orderFunction = $callable;
			} else {
				$orderFunction = function($a, $b) use ($callable, $key)
				{
					$a = $a[$key];
					$b = $b[$key];

					switch($order){
						case "lexical":
						return strcmp($a, $b);
						break;

						case "datestring":
						return strtotime($a) - strtotime($b);
						break;

						case "float":
						return floatval($a) - floatval($b);
						break;

						default:
						return $$callable($a, $b);
					}
				};
			}
			usort($array, $orderFunction);
			return $array;
		}


		/**
		* SUMMARY OF create_download
		*
		* DESCRIPTION
		*
		* @param TYPE ($source, $filename = "export.csv") ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function create_download($source, $filename = "export.csv")
		{

			$textFromFile = file_get_contents($source);
			$f = fopen('php://memory', 'w');
			fwrite($f, $textFromFile);
			fseek($f, 0);

			header('Content-Type: text/plain');
			header('Content-Disposition: attachment; filename="' . $filename . '"');
			// make php send the generated csv lines to the browser
			fpassthru($f);
		}


		/**
		* SUMMARY OF find_most_similar
		*
		* DESCRIPTION
		*
		* @param TYPE ($needle, $haystack, $alwaysFindSomething = TRUE) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function find_most_similar($needle, $haystack, $alwaysFindSomething = TRUE)
		{

			if ($alwaysFindSomething) {
				$bestWord = reset($haystack);
				similar_text($needle, $bestWord, $bestPercentage);
			}
			else {
				$bestWord = "";
				$bestPercentage = 0;
			}

			foreach ($haystack as $key => $value) {
				similar_text($needle, $value, $thisPercentage);

				if ($thisPercentage > $bestPercentage) {
					$bestWord = $value;
					$bestPercentage = $thisPercentage;
				}
			}
			return $bestWord;
		}
		/**
		* SUMMARY OF logg
		*
		* DESCRIPTION
		*
		* @param TYPE ($data, $infoText = "", $filename = "logg.txt") ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/

		public static function logg($data, $infoText = "", $file_name = "logg.txt")
		{
			$debug_info = array_reverse(debug_backtrace());
			$chainFunctions = function($p,$n){
				$class = (isset($n["class"]) ? "(". $n["class"] . ")" : "");
				$p.='->' . $class . $n['function'] . ":" . $n["line"];
				return $p;
			};
			$calling_functions = ltrim(array_reduce($debug_info, $chainFunctions), "->");
			$file = pathinfo(reset($debug_info)["file"], PATHINFO_BASENAME);

			$string = "\n\n####\n--------------------------------\n";
			$string .= date("Y-m-d H:i:s");
			$string .= ($infoText != "") ? "\n" . $infoText : "" ;
			$string .= "\n--------------------------------\n";

			if (is_string($data)) {
				$string .= $data;
			}
			else if (is_array($data)) {
				$string .= print_r($data, true);
			}
			else {
				$string .= var_export($data, true);
			}
			$string .= "\n----------------------------\n";
			$string .= "Calling stack: " . $calling_functions . "\n";
			$string .= $file . " produced this log entry";

			file_put_contents($file_name, $string, FILE_APPEND);

		}
		/**
		* SUMMARY OF activate_all_errors
		*
		* DESCRIPTION
		*
		* @param TYPE () ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function activate_all_errors()
		{
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		}
		/**
		* SUMMARY OF DMStoDEC
		*
		* DESCRIPTION
		*
		* @param TYPE ($deg,$min,$sec) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/

		public static function DMStoDEC($deg,$min,$sec)
		{

			// Converts DMS ( Degrees / minutes / seconds )
			// to decimal format longitude / latitude

			return $deg+((($min*60)+($sec))/3600);
		}
		/**
		* SUMMARY OF DECtoDMS
		*
		* DESCRIPTION
		*
		* @param TYPE ($dec) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/

		public static function DECtoDMS($dec)
		{

			// Converts decimal longitude / latitude to DMS
			// ( Degrees / minutes / seconds )

			// This is the piece of code which may appear to
			// be inefficient, but to avoid issues with floating
			// point math we extract the integer part and the float
			// part by using a string function.

			$vars = explode(".",$dec);
			$deg = $vars[0];
			$tempma = "0.".$vars[1];

			$tempma = $tempma * 3600;
			$min = floor($tempma / 60);
			$sec = $tempma - ($min*60);

			return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
		}

		/**
		* SUMMARY OF generateRandomString
		*
		* DESCRIPTION
		*
		* @param TYPE ($length = 10) ARGDESCRIPTION
		*
		* @return TYPE NAME DESCRIPTION
		*/
		public static function generateRandomString($length = 10)
		{
			$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			foreach(range(0,$length) as $i) {
				$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
			}
			return $randomString;
		}
		/**
		* Put a list of variables from $_REQUEST into the global scope
		*
		* DESCRIPTION
		*
		* @param array @translation_array
		* @param string $prefix
		*
		* @return void
		*/
		public static function extractRequest()
		{
			// arguments: translation_array, prefix
			$args = func_get_args();
			if(count($args) == 0 || is_null($args[0])) {
				$translation_array = array_keys($_REQUEST); // i.e. all elements of $_REQUEST are put into the global scope. Use with caution!
			}
			else {
				$translation_array = $args[0];
			}

			$p = $args[1] ?? ""; // prefix

			$dont_translate = array_filter($translation_array, "is_numeric" , ARRAY_FILTER_USE_KEY);

			$translate = array_diff_assoc($translation_array, $dont_translate);

			array_walk($dont_translate, function($v, $k, $p){$GLOBALS["$p$v"] = $_REQUEST[$v] ?? null;}, $p);
			array_walk($translate, function($v, $k, $p){$GLOBALS["$p$v"] = $_REQUEST[$k] ?? null;}, $p);
		}

		/**
		* [resolvePath description]
		* @param  [type] $array     [description]
		* @param  string $path      [description]
		* @param  string $delimiter [description]
		* @return [type]            [description]
		*/
		public static function resolvePath($array, $path = "", $delimiter = '/')
		{
			$keys = explode($delimiter, $path);
			foreach($keys as $key){
				$array = &$array[$key] ?? false;
			}
			return $array;
		}

	} // END OF CLASS
