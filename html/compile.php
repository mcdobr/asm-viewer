<?php

	function consoleLog($data) {
		echo '<script>';
		echo 'console.log('. json_encode($data) .')';
		echo '</script>';
	}

	function addDefaultAdditionalFlags(&$additional, $compiler, $filePrefix) {

	  $additional = $additional . " -o /tmp/$filePrefix";
	  /* Set to the intel syntax if not already set*/
	  if ($compiler === "gcc" && strpos($additional, '-masm=') === false) {
	    $additional = $additional . " -masm=intel ";
	  }
	  if (strpos($additional, '-fno-asynchronous-unwind-tables') === false) {
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
	function highlightAndStripErrorsAndWarnings($compilerStderr, $cPath) {
		$cPath = str_replace('/', '\/', $cPath);

		/* Strip the filename so the client can't see it */
		$compilerStderr = preg_replace('/\/[^:]+: ?/', '', $compilerStderr);
		$compilerStderr = preg_replace('/[0-9]+:[0-9]+:/', "line $0", $compilerStderr);

		/* Highlight the warnings and errors */
		$compilerStderr = str_replace('error', '<span class="error">error</span>' , $compilerStderr);
		$compilerStderr = str_replace('warning', '<span class="warning">warning</span>' , $compilerStderr);
		return $compilerStderr;
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

		/* Append the errors and warnings to the end */
		$compilerStderr = file_get_contents($cLog);
		$compilerStderr = highlightAndStripErrorsAndWarnings($compilerStderr, $cPath);

		if ($response !== '')
			$response = $response . PHP_EOL;
		if ($compilerStderr !== '')
			$response = $response . "COMPILER ERRORS AND WARNINGS" . PHP_EOL . $compilerStderr;

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


	$contents = file_get_contents("php://input");
	$in = json_decode(stripslashes($contents), true);

	/*echo "CONTENTS" . PHP_EOL;
	echo $contents . PHP_EOL . PHP_EOL;

	echo "STRIPPED" . PHP_EOL;
	echo stripslashes($contents) . PHP_EOL . PHP_EOL;*/

	//consoleLog($contents);
	//consoleLog($in);

	//echo json_encode(var_dump($in));

	/* Compile the file and return the asm code */
	file_put_contents($cPath, $in['code']);
	//consoleLog($in['code']);
	$compiler = $in['compiler'];
	$additional = $in['additional'];
	$mustInterleave = $in['interleave'];

	/* Check the user input */
	//echo json_encode(var_dump($in));
	if (!isCompilerGood($compiler)) {
		die(json_encode('Stop being so sneaky!'));
	}

	addDefaultAdditionalFlags($additional, $compiler, $cPrefix);

	/* Create the command line string, validate it and execute it  */
	$cmdline_string = escapeshellcmd("$compiler $cPath $additional -Wa,-adhln -g") . " 2> $cLog";
	$status = exec($cmdline_string, $execOutput, $ret_code);

	//consoleLog($cmdline_string);
	/* Create the response */
	$response = createResponse($execOutput, $compiler, $cPath, $cLog, $mustInterleave);

	/* Send the response */
	echo json_encode($response);

	cleanUp($cPath, $cPrefix, $cLog);
?>
