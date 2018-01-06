function enableTabChar(elementId) {
	/* This makes it possible to indent code because default behavior
	 * is to just shift focus from the input area
	 */
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

function prepareForJSON(str) {
	/* Because of the way html works a quote inside the input area
	 * is a \", so you have to escape both of them so that the website works
	 * with character arrays. Also you need to replace <br> tags to newlines
	 * because the compiler has no notion of <br> tags.
	 */
	str = str.replace(/\"/g, '\\\"');
	str = str.replace(/<br\s*\/?>/mg, "\\n");
	return str;

}

function newlineToBr(str) {
	return str.replace(/\n/g, "<br />");
}

function strip(html) {
	/* this function returns the text content of an element */
	var doc = new DOMParser().parseFromString(html, 'text/html');
	return doc.body.textContent || "";
}

function onReceiveCallback(response) {
	/* this function outputs the server response on the output area */
	response = JSON.parse(response);
	outputCode = document.getElementById("outputCodeArea");

	if (response) {
		outputCode.innerHTML = response.replace(/\n/g, "<br />");
		highlightMachineMnemonics();
	}
}

function sendCode() {
	/* Send the code with newlines instead of br tags */
	var codeArea = document.getElementById("inputCodeArea");
	var code = strip(prepareForJSON(codeArea.innerHTML));

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
			//document.getElementById("outputCodeArea").innerHTML = this.responseText;
			onReceiveCallback(this.responseText);
		}
	};


	xhr.open("POST", "compile.php", true);
	xhr.setRequestHeader("Content-Type", "application/json");
	xhr.send(requestBody);
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

function onEdit() {
	if (typeof onEdit.isPressed == 'undefined')
		onEdit.isPressed = false;

	onEdit.isPressed = !onEdit.isPressed;

	var inputCodeArea = document.getElementById("inputCodeArea");
	var editButton = document.getElementById("editButton");

	if (onEdit.isPressed) {
		editButton.innerHTML = "Stop";
		stripInputCodeSpans();
		inputCodeArea.contentEditable = "true";
	} else {
		editButton.innerHTML = "Edit";
		inputCodeArea.contentEditable = "false";
		highlightSyntax();
	}

}

function stripInputCodeSpans() {
	var inputCodeArea = document.getElementById("inputCodeArea");
	var content = inputCodeArea.innerHTML;
	content = content.replace(/<\/?span[^>]*>/g, "");

	inputCodeArea.innerHTML = content;
}
