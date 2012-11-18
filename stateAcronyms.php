<?php
// return list of acronyms in json format


// read in our acronym list
$fh = fopen("StateAbbreviations.csv", "r") or die("couldn\'t open file: StateAbbreviations.csv");

$mapByAcronym = array();
$mapByStateName = array();

while (!feof($fh)) {

	$line = fgets($fh);
	
	// line is formatted: state,acronym
	$comma = strpos($line, ',');
	if (!$comma) {
		break;
	}
	$stateName = trim(substr($line, 0, $comma));	
	$acronym = trim(substr($line, $comma + 1));
	
	$mapByAcronym[$acronym] = $stateName;
	$mapByStateName[$stateName] = $acronym;
}
fclose($fh);

$returnMap = array();
$returnMap['MappedByAcronym'] = $mapByAcronym;
$returnMap['MappedByStateName'] = $mapByStateName;
echo json_encode($returnMap);

?>