/* 
 * Random password generator (JavaScript)
 * 
 * Copyright (c) 2015 Project Nayuki
 * All rights reserved. Contact Nayuki for licensing.
 * http://www.nayuki.io/page/random-password-generator-javascript
 */

/**
 * The one and only function called from the HTML code
 * @param length
 * @param enable_symbols
 */
function generate_password(length,enable_symbols) {
	var charset = "";
	charset += "0123456789";   //numbers
	charset += "abcdefghijklmnopqrstuvwxyz";   //lowercase
	charset += "ABCDEFGHIJKLMNOPQRSTUVWXYZ";   //uppercase
	if(enable_symbols) charset += "!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";   //symbols
	//charset += " ";    //space
	//charset += 'custom chars';
	charset = removeDuplicates(charset);
	charset = charset.replace(/ /, "\u00A0");  // Replace space with non-breaking space
	
	var password = "";
	var statistics = "";
	if (charset == "") {
		alert("Error: Character set is empty");
	} else {
		if (typeof length == 'undefined') length = 10;

		length = parseInt(length, 10);
		/*else if (document.getElementById("by-entropy").checked)
			length = Math.ceil(parseFloat(document.getElementById("entropy").value) * Math.log(2) / Math.log(charset.length));
		else
			throw "Assertion error";*/
		
		if (length < 0 || length > 10000)
			alert("Invalid password length");
		else {
			for (var i = 0; i < length; i++)
				password += charset.charAt(randomInt(charset.length));
			
			var entropy = Math.log(charset.length) * length / Math.log(2);
			var entropystr;
			if (entropy < 70)
				entropystr = entropy.toFixed(2);
			else if (entropy < 200)
				entropystr = entropy.toFixed(1);
			else
				entropystr = entropy.toFixed(0);
			statistics = "Length = " + length + " chars, Charset size = " + charset.length + " symbols, Entropy = " + entropystr + " bits";
		}
	}
	return password;
	//statisticsText.data = statistics;
}


// e.g. "daabcccd" -> "dabc"
function removeDuplicates(s) {
	var result = "";
	for (var i = 0; i < s.length; i++) {
		var c = s.charAt(i);
		if (result.indexOf(c) == -1)
			result += c;
	}
	return result;
}


// Returns a random integer in the range [0, n) using a variety of methods
function randomInt(n) {
	var x = randomIntMathRandom(n);
	x = (x + randomIntBrowserCrypto(n)) % n;
	return x;
}


// Not secure or high quality, but always available
function randomIntMathRandom(n) {
	var x = Math.floor(Math.random() * n);
	if (x < 0 || x >= n)
		throw "Arithmetic exception";
	return x;
}


// Uses a secure, unpredictable random number generator if available; otherwise returns 0
function randomIntBrowserCrypto(n) {
	if (typeof Uint32Array == "function" && "crypto" in window && "getRandomValues" in window.crypto) {
		// Generate an unbiased sample
		var x = new Uint32Array(1);
		do window.crypto.getRandomValues(x);
		while (x[0] - x[0] % n > 4294967296 - n);
		return x[0] % n;
	} else
		return 0;
}
