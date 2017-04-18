<?php
/**
* Contains the Utility class.
*/

namespace Fridde;

use Carbon\Carbon;

/**
* Contains helpful static functions that are missing from the PHP core.
*/
class Utility
{
	/**
	* Filter function for arrays.
	*
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
				$e = 'Could not find any entry with the value "';
				$e .= $id_value . '" in the column "' . $id_column . '".';
				throw new \Exception($e);
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
		* checks if all elements of the array are arrays themselves
		* @param  [type] $array [description]
		* @return [type]        [description]
		*/
		public static function onlyArrays($array)
		{
			return count(array_filter($array, "is_array")) === count($array);
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
		public static function dateRange(string $first, string $last, string $step = '+1 day', string $format = "Y-m-d", bool $addLast = TRUE)
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
				$dates[] = $last->format($format);
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
				$order = $callable;
				$orderFunction = function($a, $b) use ($order, $key)
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
			usort($haystack, function($a, $b) use ($needle){
				return similar_text($needle, $a) - similar_text($needle, $b);
			});
			return end($haystack);

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
		* Converts a part of a coordinate from the DMS format into the decimal format.
		*
		* @param float $deg Degrees
		* @param float $min Minutes
		* @param float $sec Seconds
		*
		* @return float The coordinate in the decimal format
		*/
		public static function DMStoDEC(float $deg = 0.0, float $min = 0.0, float $sec = 0.0)
		{
			return $deg + ($min * 60) + ($sec / 3600);
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

		public static function DECtoDMS(float $dec)
		{

			// Converts decimal longitude / latitude to DMS
			// ( Degrees / minutes / seconds )

			// This is the piece of code which may appear to
			// be inefficient, but to avoid issues with floating
			// point math we extract the integer part and the float
			// part by using a string function.

			$deg = floor($dec);
			$min = floor(($dec - $deg) * 60.0);
			$sec = ($dec - $deg - ($min/60.0)) * 3600.0;

			return ["deg" => $deg, "min" => $min, "sec" => $sec];
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
		public static function generateRandomString(int $length = 10)
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
		* @param  string|array $path      [description]
		* @param  string $delimiter [description]
		* @return [type]            [description]
		*/
		public static function resolve(array $array, $path = "", string $delimiter = '/')
		{
			if(is_array($path)){
				$keys = $path;
			} elseif(is_string($path)){
				$keys = explode($delimiter, $path);
			} else {
				throw new \Exception("The path parameter has an invalid format. String or array is accepted.");
			}

			foreach($keys as $key){
				$array = &$array[$key];
			}
			$value = $array ?? null;
			return $value;
		}

		/**
		* [createMethodStubs description]
		* @param  string $path [description]
		* @return [type]       [description]
		*/
		public static function createMethodStubs(string $path = "", string $format = "html"){

			$path = empty($path) ? __DIR__ : $path;
			$files = scandir($path);
			$attributes = [];
			foreach($files as $file_name){
				$is_not_itself = basename(__FILE__) !== $file_name;
				$is_php = pathinfo($file_name, PATHINFO_EXTENSION) == "php";
				if($is_not_itself && $is_php){
					$handle = fopen($path . '/' . $file_name, "r");
					if ($handle) {
						$class_name = str_replace('.php', '', $file_name);
						while (($line = fgets($handle)) !== false) {
							$found = preg_match('%^\s*protected\s+\$(\w+);%', $line, $matches);
							if($found){
								$attributes[$class_name][] = $matches[1];
							}
						}
						fclose($handle);
					}
				}
			}

			$insert_pre = count($attributes) !== 0 && $format == "html";
			$text = $insert_pre ? '<pre>' : '';
			foreach($attributes as $class_name => $attributes){
				$text .=  PHP_EOL . '###' . $class_name . '###' . PHP_EOL;
				foreach($attributes as $attribute){
					$text .=  'public function get' . ucfirst($attribute) ;
					$text .=  '(){return $this->' . $attribute . ';}' . PHP_EOL;

					$text .=  'public function set' . ucfirst($attribute) . '($';
					$text .=  $attribute;
					$text .= '){$this->' . $attribute . ' = $'. $attribute . ';}' . PHP_EOL;
				}
				$text .= '/** @PrePersist */' . PHP_EOL;
				$text .= 'public function prePersist(){$this->postUpdate();}' . PHP_EOL;
				$text .= '/** @PreUpdate */' . PHP_EOL;
				$text .= 'public function preUpdate(){}' . PHP_EOL; //} Horrible hack to ensure indentation
				$text .= '/** @PreRemove */' . PHP_EOL;
				$text .= 'public function preRemove(){}' . PHP_EOL; //} Horrible hack to ensure indentation
			}
			$text .= $insert_pre ? '</pre>' : '';
			echo $text;
		}

		/**
		* Calculates the quotient of two durations given in the format [value, "unit"]
		*
		* @param  array $numerator   The nominator.
		* @param  array $denominator The denominator.
		* @return float              The quotient.
		*/
		public static function divideDuration(array $numerator, array $denominator)
		{
			$num = self::convertDuration($numerator);
			$denom = self::convertDuration($denominator);
			return floatval($num/$denom);
		}

		/**
		*	Converts a time interval to another unit.
		*
		*  Possible units are *ms, s, m, h, d, w* and *y*.
		*
		* @param  array $value_and_unit An array representing a certain duration.
		*                               The first element is the value, the second element is the unit as a string.
		* @param  string $target_unit   The unit to convert to.
		* @return float                 The value in the new unit, given as float.
		*/
		public static function convertDuration(array $value_and_unit, string $target_unit = "s")
		{
			list($value, $unit) = $value_and_unit;
			if($unit == $target_unit){
				return floatval($value);
			}
			$to_second = ["ms" => 0.001, "s" => 1, "m" => "60",
			"h" => 3600, "d" => 86400, "w" => 604800, "y" => 31540000];

			$factor = $to_second[$unit] / $to_second[$target_unit];
			return floatval($value) * $factor;
		}

		/**
		* Will adjust an interval so that it becomes an exact integer multiple of the divisor interval.
		*
		* @param array $input_interval   The interval to adjust.
		* @param array $divisor_interval The interval to adjust to.
		* @param string $round	The rounding method to us. Can be "up" (uses ceil()),
		*                      "down" or "nearest" (standard rounding). Case insensitive.
		* @return array                   The adjusted interval in the form [value, "unit"].
		*/
		public static function adjustInterval(array $unadjusted_interval, array $divisor_interval, string $round = "UP")
		{
			$unadjusted_factor = self::divideDuration($unadjusted_interval, $divisor_interval);
			switch(strtolower($round)){
				case "up":
				$adjusted_factor = ceil($unadjusted_factor);
				break;
				case "down":
				$adjusted_factor = floor($unadjusted_factor);
				break;
				case "nearest":
				$adjusted_factor = round($adjusted_factor);
				break;
			}
			$div_value = floatval($divisor_interval[0]);
			$div_unit = $divisor_interval[1];

			return [$adjusted_factor * $div_value, $div_unit];
		}

		/**
		* Checks if a value is between two other values. Allows to specify how the
		* boundaries should be treated.
		*
		* @param  float|integer  $val        The value to check.
		* @param  float|integer  $lower      The lower boundary
		* @param  float|integer  $upper      The upper boundary
		* @param  string  $comparison One of STRICT_BOTH (default), EQUAL_LOWER, EQUAL_UPPER, EQUAL_BOTH
		* @return boolean             True if the value is between $lower and $upper (using the comparison modifier),
		*                             false otherwise.
		*/
		public static function isBetween(float $val, float $lower, float $upper, string $comparison = "STRICT_BOTH")
		{
			switch(strtolower($comparison)){
				case "strict_both":
				return $val > $lower && $val < $upper;
				break;

				case "equal_lower":
				return $val >= $lower && $val < $upper;
				break;

				case "equal_upper":
				return $val > $lower && $val <= $upper;
				break;

				case "equal_both":
				return $val >= $lower && $val <= $upper;
				break;

				default:
				throw new \Exception('The comparison string "'. $comparison . '" is invalid.');
				break;
			}

		}

		/**
		* Adds a duration to a DateTime
		*
		* @param array $duration Duration of the type [value, "unit"]
		* @param null|Carbon\Carbon $time_to_be_changed The point in time.
		*                                               If omitted, the current time is assumed.
		*
		* @return Carbon\Carbon The new time after addition.
		*/
		public static function addDuration(array $duration, Carbon $time_to_be_changed = null)
		{
			$t = $time_to_be_changed ?? Carbon::now();
			$seconds = self::convertDuration($duration, "s");
			return $t->addSeconds($seconds);
		}

		/**
		* Subtracts a duration from a DateTime
		*
		* @param array $duration Duration of the type [value, "unit"]
		* @param null|Carbon\Carbon $time_to_be_changed The point in time.
		*                                               If omitted, the current time is assumed.
		*
		* @return Carbon\Carbon The new time after subtraction.
		*/
		public static function subDuration(array $duration, $time_to_be_changed = null)
		{
			$duration[0] =  -1 * floatval($duration[0]);
			return self::addDuration($duration, $time_to_be_changed);
		}

	} // END OF CLASS
