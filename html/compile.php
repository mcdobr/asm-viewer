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

	error_reporting(E_ALL);
	ini_set('display_errors', '1');
	require('asmparse.php');


	$c_path = '/tmp/temp.c';
	$asm_path = '/tmp/temp.lst';

	$in = json_decode(stripslashes(file_get_contents("php://input")), true);
	
	/* Compile the file and return the asm code */
	file_put_contents($c_path, $in['code']);
	$compiler = $in['compiler'];
	$additional = $in['additional'];

	addDefaultAdditionalFlags($additional, $compiler);

	/* Create the command line string, validate it and execute it  */	
	/*if (!file_exists($asm_path)) {
		touch($asm_path);
		chmod($asm_path, 0666);
	}*/

	$cmdline_string = escapeshellcmd("$compiler $c_path $additional -Wa,-adhln -g");
	$status = exec($cmdline_string, $exec_output, $ret_code);
	//echo $cmdline_string . PHP_EOL;


	foreach ($exec_output as $listing_line) {

		if (isHighLevelCode($listing_line) || isMachineInstruction($listing_line)) {
			
			if (isHighLevelCode($listing_line)) {
				$listing_line = stripHighLevelCode($listing_line);
			} else if (isMachineInstruction($listing_line)) {
				$listing_line = stripMachineInstruction($listing_line);
			}


			echo $listing_line . PHP_EOL;
			//echo isHighLevelCode($listing_line) . ' ' . isMachineInstruction($listing_line) . PHP_EOL;
		}
	}
	/*
	foreach ($exec_output as $listing_line) {
		echo $listing_line . PHP_EOL;
	}*/

	//var_dump($status);

	//asta
	//var_dump($exec_output);
	//var_dump($ret_code);
	//header("Content-Type: application/json");

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
