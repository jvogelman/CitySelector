<?php
// return list of acronyms in json format

$fh1 = fopen("output.txt", "w");

// read in our acronym list
$fh = fopen("StateAbbreviations.csv", "r") or die("couldn\'t open file: StateAbbreviations.csv");


fwrite($fh1, 'opened file\n');

$map = array();

while (!feof($fh1)) {

	fwrite($fh1, "reading file\n");
	$line = fgets($fh);
	
	// line is formatted: state,acronym
	$comma = strpos($line, ',');
	if (!$comma) {
		break;
	}
	$stateName = trim(substr($line, 0, $comma));	
	$acronym = trim(substr($line, $comma + 1));
	
	//list ($stateName, $acronym) = fscanf($fh, "%s,%s");
	fwrite($fh1, "found state " . $stateName . " and acronym " . $acronym . "\n");
	$map[$acronym] = $stateName;
}
fclose($fh);

echo json_encode($map);

fclose($fh1);
?>