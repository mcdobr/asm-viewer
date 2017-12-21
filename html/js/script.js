function sendCode() {

	/* Send the code with newlines instead of br tags */
	var code = document.getElementById("inputCodeArea").innerHTML;
	code = str.replace(/<br\s*\/?>/mg, "\n");



	var xhr = new XMLHttpRequest();

	xhr.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			document.getElementById("outputCodeArea").innerHTML = this.responseText.replace(/\n/g, "<br />");
		}
	}

	var formData = new FormData();
	formData.append("code", code);

	xhr.open("POST", "compile.php", true);
	xhr.send(formData);
}
