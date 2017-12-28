<!DOCTYPE html>

<html>

<head>
	<title>ASM Viewer</title>
	<meta charset="UTF-8">

	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script type="text/javascript" src="js/script.js"></script>
</head>


<body onload="enableTabChar('inputCodeArea');">

	<form action="compile.php" method="post" onsubmit="sendCode();return false;">
		<select name="compiler" id="compilerSelect">
			<option value="avr-gcc">gcc 4.9.2 AVR</option>
			<option value="gcc" selected="selected">gcc 5.4.0 x86</option>
			<option value="arm-none-eabi-gcc">gcc 4.9.3 ARM (none)</option>
			<option value="arm-linux-gnueabi-gcc">gcc 4.9.3 ARM (Linux)</option>
			<option value="mips-linux-gnu-gcc">gcc 5.4.0 MIPS (Linux)</option>
		</select>

		Compiler options: <input type="text" name="additionalOptions" id="additionalOptions"> 

	</form>

	<main>
		<div class="codeArea" id="inputCodeArea" contenteditable="true"></div>
		<pre class="codeArea" id="outputCodeArea"></pre>
		<button type="button" onclick="sendCode();return false;">Compile it</button>
	</main>


</body>
</html>
