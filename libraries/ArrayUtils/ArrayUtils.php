<?php

/*
	ArrayUtils
	Added by Hieu Nguyen on 2018-12-09
	Source: http://eosrei.net/articles/2011/11/php-arrayinsertafter-arrayinsertbefore
*/

/*
 * Inserts a new key/value before the key in the array.
 *
 * @param $key
 *   The key to insert before.
 * @param $array
 *   An array to insert in to.
 * @param $new_key
 *   The key to insert.
 * @param $new_value
 *   An value to insert.
 *
 * @return
 *   The new array if the key exists, FALSE otherwise.
 *
 * @see array_insert_after()
 */
function array_insert_before($key, array &$array, $new_key, $new_value) {
	if (array_key_exists($key, $array)) {
		$new = array();
		foreach ($array as $k => $value) {
			if ($k === $key) {
				$new[$new_key] = $new_value;
			}
			$new[$k] = $value;
		}
		return $new;
	}
	return FALSE;
}

/*
* Inserts a new key/value after the key in the array.
*
* @param $key
*   The key to insert after.
* @param $array
*   An array to insert in to.
* @param $new_key
*   The key to insert.
* @param $new_value
*   An value to insert.
*
* @return
*   The new array if the key exists, FALSE otherwise.
*
* @see array_insert_before()
*/
function array_insert_after($key, array &$array, $new_key, $new_value) {
	if (array_key_exists ($key, $array)) {
		$new = array();
		foreach ($array as $k => $value) {
			$new[$k] = $value;
			if ($k === $key) {
				$new[$new_key] = $new_value;
			}
		}
		return $new;
	}
	return FALSE;
}

// Implemented by Hieu Nguyen on 2019-07-16 to support insert new value before an index in ordered array
function array_insert_before_index(array &$array, int $index, $insertValue) {   // Use this function name style for consistence with this util
	$head = array_splice($array, 0, $index);
	$tail = $array;

	$newArray = array_merge($head, [$insertValue], $tail);
	$array = $newArray;
}

// Implemented by Hieu Nguyen on 2019-07-16 to support insert new value after an index in ordered array
function array_insert_after_index(array &$array, int $index, $insertValue) {   // Use this function name style for consistence with this util
	$head = array_splice($array, 0, $index + 1);
	$tail = $array;

	$newArray = array_merge($head, [$insertValue], $tail);
	$array = $newArray;
}

// Added by Hieu Nguyen on 2021-06-28 to merge nested array naturally which array_merge_recursive() can not handle
// Source: https://github.com/drupal/core/blob/8.9.x/lib/Drupal/Component/Utility/NestedArray.php
// Credit to: Drupal Team
function merge_deep_array(array $arrays, $preserve_integer_keys = true) {
	$result = [];

	foreach ($arrays as $array) {
		foreach ($array as $key => $value) {
			// Renumber integer keys as array_merge_recursive() does unless
			// $preserve_integer_keys is set to TRUE. Note that PHP automatically
			// converts array keys that are integer strings (e.g., '1') to integers.
			if (is_int($key) && !$preserve_integer_keys) {
				$result[] = $value;
			}
			// Recurse when both values are arrays.
			elseif (isset($result[$key]) && is_array($result[$key]) && is_array($value)) {
				$result[$key] = merge_deep_array([$result[$key], $value], $preserve_integer_keys);
			}
			// Otherwise, use the latter value, overriding any previous value.
			else {
				$result[$key] = $value;
			}
		}
	}

	return $result;
}