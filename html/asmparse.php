<?php

function isHighLevelCode($listing_line) {
	/* if it contains 4 stars */
	return preg_match('/\*{4}/', $listing_line);
}

function isMachineInstruction($listing_line) {
	/* if it has a 4 digit hex number (the program counter) */
	return preg_match('/\b[0-9a-f]{4}\b/', $listing_line);
}

function isLabel($listing_line) {
	/* fluff handling AND if it starts with . or $(on MIPS) and ends with :
	 * \h is for horizontal whitespace
	 */
	return preg_match('/[0-9]+[ \t]+[\.\$]?[A-Za-z0-9]+:/', $listing_line);
}

function isUselessLabel($label) {
	/* just check if it hinders human readability */
	$useless_labels = array(
		'Ltext[0-9]+',
		'Letext[0-9]+',
		'LFE[0-9]+'
	);

	$useless_regex = '/[\.\$]{1}(' . join('|', $useless_labels) . ')/';
	return preg_match($useless_regex, $label);
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
