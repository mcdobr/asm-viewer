<!DOCTYPE html>

<html>

<head>
	<title>ASM Viewer</title>
	<meta charset="UTF-8">

	<link rel="stylesheet" type="text/css" href="css/style.css">
	<script type="text/javascript" src="js/script.js"></script>
</head>


<body onload="enableTabChar('inputCodeArea');">

	<div class="codeArea" id="inputCodeArea" contenteditable="true"></div>

	<pre class="codeArea" id="outputCodeArea"></pre>

	<button type="button" onclick="sendCode();return false;">Compile it</button>

</body>
</html>
