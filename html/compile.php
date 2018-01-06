<?php
	function addDefaultAdditionalFlags(&$additional, $compiler, $filePrefix) {

	  $additional = $additional . " -o /tmp/$filePrefix";
	  /* Set to the intel syntax */
	  if ($compiler === "gcc" && preg_match('/^-masm=/', $additional) == 0) {
	    $additional = $additional . " -masm=intel ";
	  }
	  if (preg_match('/^-fno-asynchronous-unwind-tables/', $additional) == 0) {
	    $additional = $additional . " -fno-asynchronous-unwind-tables ";
	  }
	}

	function isCompilerGood($compiler) {
		/* Checks if the compiler supplied by the client is valid */
	  $compilers = array(
	    'avr-gcc',
	    'gcc',
	    'arm-none-eabi-gcc',
	    'arm-linux-gnueabi-gcc',
	    'mips-linux-gnu-gcc'
	  );

	  return in_array($compiler, $compilers, true);
	}

	function getAssemblerCommentCharacter($compiler) {
		/* Returns comment character given the selected architecture */
	  if ($compiler === 'mips-linux-gnu-gcc') {
	    return '#';
	  } else {
	    return ';';
	  }
	}

	function createResponse($execOutput, $compiler, $cPath, $cLog, $mustInterleave) {
		$response = '';

		$assemblerCommentChar = getAssemblerCommentCharacter($compiler);
		foreach ($execOutput as $listingLine) {
			if (isRelevantToHumanReading($listingLine)) {
				if (isHighLevelCode($listingLine)) {
					$listingLine = stripHighLevelCode($listingLine, $assemblerCommentChar, $cPath);
					$spanClass = 'highLevelCode';
					if (!$mustInterleave || isEmptyLine($listingLine))
						continue;
					//continue;
				} else if (isMachineInstruction($listingLine)) {
					$listingLine = stripMachineInstruction($listingLine);
					$spanClass = 'machineInstruction';
				} else if (isLabel($listingLine)) {
					$listingLine = stripLabel($listingLine);
					$spanClass = 'label';
					if (isUselessLabel($listingLine)) {
						continue;
					}
				}
				$response = $response . "<span class='$spanClass'>$listingLine</span>" . PHP_EOL;
			}
		}

		/* If there is no assembly code, then there must be an error */
		if ($response === '') {
			$compilerStderr = file_get_contents($cLog);
			$response = $compilerStderr;
		}
		return $response;
	}

	function cleanUp($cPath, $cPrefix, $cLog) {
		/* Clean up the /tmp dir */
		if (file_exists($cPath))
			unlink($cPath);
		$objFile = "/tmp/$cPrefix";
		if (file_exists($objFile))
			unlink($objFile);
		if (file_exists($cLog))
			unlink($cLog);
	}

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	require('asmparse.php');
	header('Content-Type: application/json');

	/* Create the filenames for all relevant files */
	$cPath = tempnam('/tmp', 'cfile') . '.c';
	$cPrefix = basename($cPath, '.c');
	$cLog = '/tmp/' . $cPrefix . '.log';

	$in = json_decode(stripslashes(file_get_contents("php://input")), true);

	/* Compile the file and return the asm code */
	file_put_contents($cPath, $in['code']);
	$compiler = $in['compiler'];
	$additional = $in['additional'];
	$mustInterleave = $in['interleave'];

	/* Check the user input */
	if (!isCompilerGood($compiler)) {
		die('Stop being so sneaky!');
	}

	addDefaultAdditionalFlags($additional, $compiler, $cPrefix);

	/* Create the command line string, validate it and execute it  */
	$cmdline_string = escapeshellcmd("$compiler $cPath $additional -Wa,-adhln -g") . " 2> $cLog";
	$status = exec($cmdline_string, $execOutput, $ret_code);

	/* Create the response */
	$response = createResponse($execOutput, $compiler, $cPath, $cLog, $mustInterleave);

	/* Send the response */
	echo json_encode($response);

	cleanUp($cPath, $cPrefix, $cLog);
?>
