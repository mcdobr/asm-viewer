<?php

	error_reporting(E_ALL);
	ini_set('display_errors', '1');


	$c_path = '/tmp/temp.c';
	$asm_path = '/tmp/temp.s';

//	var_dump($_POST);
//	echo $_POST['code'];

	/* Compile the file and return the asm code */
	file_put_contents($c_path, $_POST['code']);

	//echo "gcc $c_path -S -o $asm_path";
	exec("gcc $c_path -S -masm=intel -o $asm_path");

	$asm_code = file_get_contents($asm_path);

	echo $asm_code;

	/* Delete the temporary files */
	unlink($c_path);
	unlink($asm_path);
?>
