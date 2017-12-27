<?php
	error_reporting(E_ALL);
	ini_set('display_errors', '1');

	$c_path = '/tmp/temp.c';
	$asm_path = '/tmp/temp.s';

	$in = json_decode(stripslashes(file_get_contents("php://input")), true);
	var_dump($in);
	
	
	/* Compile the file and return the asm code */
	
	
	
	
	file_put_contents($c_path, $in['code']);

	$compiler = $in['compiler'];
	$additional = $in['additional'];



	echo $additional . PHP_EOL;

	/* Create the command line string, validate it and execute it  */	
	$cmdline_string = "$compiler $c_path -S -o $asm_path -fno-asynchronous-unwind-tables " . $additional;	
	$cmdline_string = escapeshellcmd($cmdline_string);	
	exec($cmdline_string, $exec_output, $ret_code);

	//header("Content-Type: application/json");

	var_dump($exec_output);
	
	if ($ret_code !== 0) {
		echo "Compilation failed";
	} else {
		$asm_code = file_get_contents($asm_path);

		echo $asm_code;

		/* Delete the temporary files */
		//unlink($asm_path);
	}
	//unlink($c_path);
?>
