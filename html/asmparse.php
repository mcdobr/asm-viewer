<?php

function isHighLevelCode($listing_line) {
	return preg_match('/\*{4}/', $listing_line);
}

function isMachineInstruction($listing_line) {
	return preg_match('/\b[0-9a-f]{4}\b/', $listing_line);
}

function isLabel($listing_line) {
	return preg_match('/[0-9]+\s+[\.\$]?[A-Za-z0-9]+:/', $listing_line);
}

function isRelevantToHumanReading($listing_line) {
	return  isHighLevelCode($listing_line) ||
			isMachineInstruction($listing_line) ||
			isLabel($listing_line);
}


function stripHighLevelCode($listing_line) {
	$line = trim($listing_line);
	$line = preg_replace('/[0-9]+:\/tmp\/temp.c\s+\*{4}\s/', '#', $line);
	return $line;
}

function stripMachineInstruction($listing_line) {
	$line = trim($listing_line);
	$line = preg_replace('/[0-9]+\s[0-9a-f]{4}\s[0-9A-F]+\s+/', '', $line);
	return $line;
}

function stripLabel($listing_line) {
	$line = trim($listing_line);
	$line = preg_replace('/[0-9]+\s+/', '', $line);
	return $line;
}

?>
