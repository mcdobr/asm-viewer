<?php

function isHighLevelCode($listingLine) {
	/* if it contains 4 stars */
	return preg_match('/\*{4}/', $listingLine);
}

function isMachineInstruction($listingLine) {
	/* if it has a 4 digit hex number (the program counter) */
	return preg_match('/\b[0-9a-f]{4}\b/', $listingLine);
}

function isLabel($listingLine) {
	/* fluff handling AND if it starts with . or $(on MIPS) and ends with :
	 * \h is for horizontal whitespace
	 */
	return preg_match('/[0-9]+\s+[\.\$]?[A-Za-z0-9]+:/', $listingLine);
}

function isEmptyLine($cCode) {
	return $cCode === "#";
}

function isUselessLabel($label) {
	/* just check if it hinders human readability */
	$uselessLabels = array(
		'Ltext[0-9]+',
		'Letext[0-9]+',
		'LFE[0-9]+'
	);

	$uselessRegex = '/[\.\$]{1}(' . join('|', $uselessLabels) . ')/';
	return preg_match($uselessRegex, $label);
}

function isRelevantToHumanReading($listingLine) {
	return  isHighLevelCode($listingLine) ||
			isMachineInstruction($listingLine) ||
			isLabel($listingLine);
}

function stripHighLevelCode($listingLine, $assemblerCommentChar, $cFileName) {
	$cFileName = str_replace('/', '\/', $cFileName);
	$line = trim($listingLine);
	$line = preg_replace('/[0-9]+:' . $cFileName . '\s+\*{4}\s*/', $assemblerCommentChar, $line);
	return $line;
}

function stripMachineInstruction($listingLine) {
	$line = trim($listingLine);
	$line = preg_replace('/[0-9]+\s[0-9a-f]{4}\s[0-9A-F]+\s+/', '', $line);
	return $line;
}

function stripLabel($listingLine) {
	$line = trim($listingLine);
	$line = preg_replace('/[0-9]+\s+/', '', $line);
	return $line;
}

?>
