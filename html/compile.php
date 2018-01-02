<?php
	function addDefaultAdditionalFlags(&$additional, $compiler) {
		/* Set to the intel syntax */
		if ($compiler === "gcc" && preg_match('/^-masm=/', $additional) == 0) {
			$additional = $additional . " -masm=intel ";
		}
		if (preg_match('/^-fno-asynchronous-unwind-tables/', $additional) == 0) {
			$additional = $additional . " -fno-asynchronous-unwind-tables ";
		}
	}

	function printListing($listing) {
		echo PHP_EOL;
		foreach ($listing as $listing_line) {
			echo $listing_line . PHP_EOL;
		}
	}

	function isCompilerGood($compiler) {
		$compilers = array(
			'avr-gcc',
			'gcc',
			'arm-none-eabi-gcc',
			'arm-linux-gnueabi-gcc',
			'mips-linux-gnu-gcc'
		);

		return in_array($compiler, $compilers, true);
	}

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	require('asmparse.php');


	$c_path = '/tmp/temp.c';
	$asm_path = '/tmp/temp.lst';

	$in = json_decode(stripslashes(file_get_contents("php://input")), true);

	//var_dump($in);

	/* Compile the file and return the asm code */
	file_put_contents($c_path, $in['code']);
	$compiler = $in['compiler'];
	$additional = $in['additional'];
	$mustInterleave = $in['interleave'];

	/* Check the user input */
	if (!isCompilerGood($compiler)) {
		die("Stop being so sneaky!");
	}

	addDefaultAdditionalFlags($additional, $compiler);

	/* Create the command line string, validate it and execute it  */

	$cmdline_string = escapeshellcmd("$compiler $c_path $additional -Wa,-adhln -g");
	$status = exec($cmdline_string, $exec_output, $ret_code);
	//echo $cmdline_string . PHP_EOL;


	//header("Content-Type: application/json");

	foreach ($exec_output as $listing_line) {

		if (isRelevantToHumanReading($listing_line)) {
			if (isHighLevelCode($listing_line)) {
				$listing_line = stripHighLevelCode($listing_line);
				$spanClass = "highLevelCode";
				if (!$mustInterleave || isEmptyLine($listing_line))
					continue;
				//continue;
			} else if (isMachineInstruction($listing_line)) {
				$listing_line = stripMachineInstruction($listing_line);
				$spanClass = "machineInstruction";
			} else if (isLabel($listing_line)) {
				$listing_line = stripLabel($listing_line);
				$spanClass = "label";
				if (isUselessLabel($listing_line)) {
					continue;
				}
			}
			//echo $listing_line . PHP_EOL;
			echo "<span class='$spanClass'>$listing_line</span>" . PHP_EOL;
			//echo isHighLevelCode($listing_line) . ' ' . isMachineInstruction($listing_line) . PHP_EOL;
		}
	}


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
