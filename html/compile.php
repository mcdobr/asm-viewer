<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	require('asmparse.php');
	require('compilerHandling.php');
	header('Content-Type: application/json');

	//$c_path = '/tmp/temp.c';
	$c_path = tempnam('/tmp', 'cfile') . '.c';

	$in = json_decode(stripslashes(file_get_contents("php://input")), true);

	//var_dump($in);

	/* Compile the file and return the asm code */
	file_put_contents($c_path, $in['code']);
	$compiler = $in['compiler'];
	$additional = $in['additional'];
	$mustInterleave = $in['interleave'];

	/* Check the user input */
	if (!isCompilerGood($compiler)) {
		die('Stop being so sneaky!');
	} else {
		$assemblerCommentChar = getAssemblerCommentCharacter($compiler);
	}

	addDefaultAdditionalFlags($additional, $compiler);

	/* Create the command line string, validate it and execute it  */
	$cmdline_string = escapeshellcmd("$compiler $c_path $additional -Wa,-adhln -g");
	$status = exec($cmdline_string, $exec_output, $ret_code);
	//echo $cmdline_string . PHP_EOL;


	$response = '';
	foreach ($exec_output as $listing_line) {

		if (isRelevantToHumanReading($listing_line)) {
			if (isHighLevelCode($listing_line)) {
				$listing_line = stripHighLevelCode($listing_line, $assemblerCommentChar, $c_path);
				$spanClass = 'highLevelCode';
				if (!$mustInterleave || isEmptyLine($listing_line))
					continue;
				//continue;
			} else if (isMachineInstruction($listing_line)) {
				$listing_line = stripMachineInstruction($listing_line);
				$spanClass = 'machineInstruction';
			} else if (isLabel($listing_line)) {
				$listing_line = stripLabel($listing_line);
				$spanClass = 'label';
				if (isUselessLabel($listing_line)) {
					continue;
				}
			}
			//echo $listing_line . PHP_EOL;
			$response = $response . "<span class='$spanClass'>$listing_line</span>" . PHP_EOL;

			//echo "<span class='$spanClass'>$listing_line</span>" . PHP_EOL;
			//echo isHighLevelCode($listing_line) . ' ' . isMachineInstruction($listing_line) . PHP_EOL;
		}
	}

	echo json_encode($response);

	//printListing($exec_output);


	//var_dump($status);

	//asta
	//var_dump($exec_output);
	//var_dump($ret_code);

	/*
	if ($ret_code !== 0) {
		echo "Compilation failed";
	} else {
		$asm_code = file_get_contents($asm_path);

		echo $asm_code;

		 Delete the temporary files/
		//unlink($asm_path);
	}*/
	unlink($c_path);
?>
