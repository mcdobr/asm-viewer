function enableTabChar(elementId) {
	var elem = document.getElementById(elementId);

	elem.onkeydown = function(e) {
		if (e.keyCode === 9 || e.which === 9) {
			e.preventDefault();


			var doc = this.ownerDocument.defaultView;
			var sel = doc.getSelection();
			var range = sel.getRangeAt(0);

			var tabNode = document.createTextNode("\u00a0\u00a0\u00a0\u00a0");
			range.insertNode(tabNode);

			/* Put the cursor after the tab */
			range.setStartAfter(tabNode);
			range.setEndAfter(tabNode);

			sel.removeAllRanges();
			sel.addRange(range);
		}
	};
}

function prepareCodeForSend(code)
{
	/* Replace &lt and &gt with angle brackets */
	code = code.replace(/&lt;/g, '<');
	code = code.replace(/&gt;/g, '>');

	/* Replace <br>s with \n */
	code = code.replace(/<br\s*\/?>/mg, "\n");

	/* Replace nbsp with spaces */
	code = code.replace(/&nbsp;/g, ' ');

	return code;
}

function sendCode() {

	/* Send the code with newlines instead of br tags */
	var code = document.getElementById("inputCodeArea").innerHTML;
	code = prepareCodeForSend(code);

	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("outputCodeArea").innerHTML = this.responseText.replace(/\n/g, "<br />");
		}
	};

	var formData = new FormData();
	formData.append("code", code);

	xhr.open("POST", "compile.php", true);
	xhr.send(formData);
}
