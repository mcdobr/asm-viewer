function enableTabChar(elementId) {
	var elem = document.getElementById(elementId);

	elem.onkeydown = function(e) {
		if (e.keyCode === 9 || e.which === 9) {
			e.preventDefault();


			var doc = this.ownerDocument.defaultView;
			var sel = doc.getSelection();
			var range = sel.getRangeAt(0);

			var tabNode = document.createTextNode("    ");
			range.insertNode(tabNode);

			/* Put the cursor after the tab */
			range.setStartAfter(tabNode);
			range.setEndAfter(tabNode);

			sel.removeAllRanges();
			sel.addRange(range);
		}
	};
}

function brToNewline(str) {
	return str.replace(/<br\s*\/?>/mg, "\n");
}

function brToJSONnl(str) {
	return str.replace(/<br\s*\/?>/mg, "\\n");
}

function newlineToBr(str) {
	return str.replace(/\n/g, "<br />");
}

function strip(html) {
	var doc = new DOMParser().parseFromString(html, 'text/html');
	return doc.body.textContent || "";
}

function prepareCodeForSend(code)
{
	/* Replace nbsp with spaces */
	code = code.replace(/&nbsp;/g, ' ');

	return code;
}

function sendCode() {
	/* Send the code with newlines instead of br tags */
	var codeArea = document.getElementById("inputCodeArea");
	var code = strip(brToJSONnl(codeArea.innerHTML));
	code = prepareCodeForSend(code);


	var compiler = document.getElementById("compilerSelect").value;
	var additional = document.getElementById("additionalOptions").value;
	var interleave = document.getElementById("interleave").checked;

	var requestBody = JSON.stringify({
		"code": code,
		"compiler": compiler,
		"additional": additional,
		"interleave": interleave
	});

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("outputCodeArea").innerHTML = this.responseText.replace(/\n/g, "<br />");
			highlightMachineMnemonics();
		}
	};


	xhr.open("POST", "compile.php", true);
	xhr.setRequestHeader("Content-Type", "application/json");
	xhr.send(requestBody);

	/* To be removed in production (aka later) */
	highlightSyntax();
}

function highlightSyntax() {
	/* This function highlights keywords by grabbing their text and wrapping them with span tags */
	var keywords = ["auto", "break", "case", "char", "const", "continue",
		"default", "do", "double", "else", "enum", "extern", "float",
		"for", "goto", "if", "int", "long", "register", "return",
		"short", "signed", "sizeof", "static", "struct", "switch",
		"typedef", "union", "unsigned", "void", "volatile", "while"
	];

	/* Match the whole word with \\b from the () set */
	var keywordRegex = new RegExp("\\b(" + keywords.join("|") + ")\\b", 'gi');
	var inputCodeArea = document.getElementById('inputCodeArea');

	inputCodeArea.innerHTML = brToNewline(inputCodeArea.innerHTML);

	var text = inputCodeArea.textContent;
	text = text.replace(keywordRegex, '<span class="keyword">$&</span>');
	text = newlineToBr(text);

	inputCodeArea.innerHTML = text;
}

function highlightMachineMnemonics() {
	var machineInstructions = document.getElementsByClassName("machineInstruction");

	var instructionRegex = /^[a-zA-Z0-9]+\b/g;

	for (var instr of machineInstructions) {
		var insideOfSpan = instr.innerHTML;
		insideOfSpan = insideOfSpan.replace(instructionRegex, '<span class="keyword">$&</span>');
		instr.innerHTML = insideOfSpan;
	}
}

//setInterval(highlightSyntax, 10000);
