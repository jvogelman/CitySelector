<?php

function extractArrayFromSelect($stmt) {
	$parameters = array();
	$results = array();
	$meta = $stmt->result_metadata();
	while ( $field = $meta->fetch_field() ) {
		$parameters[] = &$row[$field->name];
	}
	call_user_func_array(array($stmt, 'bind_result'), $parameters);
	while ( $stmt->fetch() ) {
		$x = array();
		foreach( $row as $key => $val ) {
			$x[$key] = $val;
		}
		$results[] = $x;
	}
	return $results;
}