function JS_trimLeft(s) {
	var whitespaces = " \t\n\r";
	for(n = 0; n < s.length; n++) { 
		if (whitespaces.indexOf(s.charAt(n)) == -1) 
			return (n > 0) ? s.substring(n, s.length) : s; 
	}
	return("");
}
function JS_trimRight(s){
	var whitespaces = " \t\n\r";
	for(n = s.length - 1; n  > -1; n--) { 
		if (whitespaces.indexOf(s.charAt(n)) == -1) 
			return (n < (s.length - 1)) ? s.substring(0, n+1) : s; 
	}
	return("");
}
function JS_trim(s) {
	return ((s == null) ? "" : JS_trimRight(JS_trimLeft(s))); 
}
function JS_isBlank(field, strBodyHeader) {
	strTrimmed = JS_trim(field.value);
	if (strTrimmed.length > 0) return false;
	alert("\"" + strBodyHeader + "\" is a required field. Please type in a value.");
	field.focus();
	return true;
}
function JS_isBadURL(field, strBodyHeader) {
	strTrimmed = JS_trim(field.value);
	if (strTrimmed.length == 0 || 
		strTrimmed.substring(0,7) == 'http://' || 
		strTrimmed.substring(0,6) == 'ftp://' || 
		strTrimmed.substring(0,7) == 'mailto:' || 
		strTrimmed.substring(0,8) == 'https://') return false;
	alert("\"" + strBodyHeader + "\" is an invalid url. Please remedy.");
	field.focus();
	return true;
}
function JS_isNumber(field, strBodyHeader) {
	var strVal = JS_trim(field.value);
	if (strVal.length == 0 || strVal.length > 999) return false;
	var 	x = 0;
	for (i=0;i < strVal.length; i++) { 
		if (strVal.charAt(i) > '0' && strVal.charAt(i) < '9') x++;
	}
	if (strVal.length > x) {
		alert("Invalid value for field \""+ strBodyHeader + "\". Please type in a valid integer.");
		field.focus();
		return false;
	} else {
		return true;
	}
}
function JS_isEmail(field, strBodyHeader) {
	var strMsg = ""; 
	var chAt  = '@'; 
	var chDot = '.'; 
	var strEmailAddr = JS_trim(field.value);
	   if (strEmailAddr.length == 0) return true;
	   if (strEmailAddr.indexOf(" ") == -1)
	   {
	       var iFirstAtPos = strEmailAddr.indexOf(chAt);
	       var iLastAtPos = strEmailAddr.lastIndexOf(chAt);
	       if (iFirstAtPos > 0 && iFirstAtPos < (strEmailAddr.length - 1) &&iFirstAtPos == iLastAtPos) {
		   // look for '.' there must be at least one char between '@' and '.'
		   var iDotPos = strEmailAddr.indexOf(chDot, iFirstAtPos + 1);
		   if (iDotPos > (iFirstAtPos + 1) && iDotPos < (strEmailAddr.length -1)) return true;
	       }
	   }
	   alert("Invalid email address. Please type in a valid email address for field \"" + strBodyHeader + "\"");
	   field.focus();
	   return false;
}
function JS_makeParent(rid) { 
	document.Com.ParentRid.value = rid;
}
function validateNews() {
	   field = document.News.Author; 
	   if (JS_isBlank(field, "Name")) return false;
	   
	   field = document.News.AuthorEmail; 
	   if (JS_isBlank(field, "Email Address")) return false;
	   if (!JS_isEmail(field, "Email Address")) return false;
	   
	   field = document.News.AuthorURL;
	   if (JS_isBadURL(field, "URL")) return false;

	   field = document.News.Heading;
	   if (JS_isBlank(field, "Title")) return false;
	       
	   field = document.News.Content;
	   if (JS_isBlank(field, "Story")) return false;

	   field = document.News.StoryURL;
	   if (JS_isBadURL(field, "Story URL")) return false;

	return true;
}
function validatePreview() {
	   field = document.Preview.Heading; 
	   if (JS_isBlank(field, "Title")) return false;
	   
//	   field = document.Preview.Summary; 
//	   if (JS_isBlank(field, "Summary")) return false;
	   
	   field = document.Preview.Content;
	   if (JS_isBlank(field, "Story")) return false;

	   field = document.Preview.StoryURL;
	   if (JS_isBadURL(field, "Story URL")) return false;

	return true;
}
function validateComment() {
	if (document.Com.anon && !document.Com.anon.checked) {

	   field = document.Com.Author; 
	   if (JS_isBlank(field, "Name")) return false;
	   
	   field = document.Com.AuthorEmail; 
	   if (JS_isBlank(field, "Email Address")) return false;
	   if (!JS_isEmail(field, "Email Address")) return false;

	   field = document.Com.AuthorURL;
	   if (JS_isBadURL(field, "URL")) return false;

	}
	field = document.Com.Content;
	if (JS_isBlank(field, "Comment")) return false;

	if (document.Com.save &&
		document.Com.save.checked &&
		document.Com.anon.checked) {
		alert('You may not save Anonymous information.');
		return false;
	}

	return true;
}
function validateContact() {
	   field = document.Contact.Author; 
	   if (JS_isBlank(field, "Name")) return false;
	   
	   field = document.Contact.AuthorEmail; 
	   if (JS_isBlank(field, "Email Address")) return false;
	   if (!JS_isEmail(field, "Email Address")) return false;
	   
	   field = document.Contact.Subject;
	   if (JS_isBlank(field, "Subject")) return false;

	   field = document.Contact.Message;
	   if (JS_isBlank(field, "Message")) return false;

	return true;
}
function validateLink() {
	   field = document.Link.linkname; 
	   if (JS_isBlank(field, "Site Name")) return false;
	   
	   field = document.Link.url; 
	   if (JS_isBlank(field, "URL")) return false;
	   if (JS_isBadURL(field, "URL")) return false;

	   field = document.Link.subname; 
	   if (JS_isBlank(field, "Name")) return false;
	   
	   field = document.Link.subemail; 
	   if (JS_isBlank(field, "Email Address")) return false;
	   if (!JS_isEmail(field, "Email Address")) return false;

	return true;
}
function validateBlocks() {
	field = document.Blocks.Heading; 
	if (JS_isBlank(field, "Heading")) return false;

	if (document.Blocks.Type[0].checked) {
	   field = document.Blocks.Content; 
	   if (JS_isBlank(field, "Content")) return false;
	}

	if (document.Blocks.Type[1].checked) {
	   field = document.Blocks.RDF; 
	   if (JS_isBlank(field, "RDF")) return false;
	   if (JS_isBadURL(field, "RDF")) return false;
	}

	if (document.Blocks.Type[2].checked) {
	   field = document.Blocks.URL;
	   if (JS_isBlank(field, "URL")) return false;
	   if (JS_isBadURL(field, "URL")) return false;
	}
	return true;
}
function validatePages() {
	   field = document.Pages.Name; 
	   if (JS_isBlank(field, "Name")) return false;

	   field = document.Pages.Heading; 
	   if (JS_isBlank(field, "Heading")) return false;
	   
	   field = document.Pages.Content; 
	   if (JS_isBlank(field, "Content")) return false;

	return true;
}
function validateSugNode() {
	   field = document.SUGNODE.newnode; 
	   if (JS_isBlank(field, "Category")) return false;

	return true;
}
function validateFriend() {
	field = document.Friend.Author;
	if (JS_isBlank(field, "Name")) return false;

	field = document.Friend.AuthorEmail;
	if (JS_isBlank(field, "Your Email Address")) return false;
	if (!JS_isEmail(field, "Your Email Address")) return false;

	field = document.Friend.MailTo;
	if (JS_isBlank(field, "Friend's Name")) return false;

	field = document.Friend.MailToEmail;
	if (JS_isBlank(field, "Friend's Email")) return false;
	if (!JS_isEmail(field, "Friend's Email")) return false;

	return true;
}
function getconfirm() {
	if(confirm("Kill this item and any associated records?")) {
		return true;
	} else {
		return false;
	}
}
function JS_swapLayout(form) {
	var idx = form.name.selectedIndex;
	form.Layout.value = form.name.options[idx].value;
	return 1;
}
function JS_swapTopic(form) {
	var idx = form.name.selectedIndex;
	form.Topic.value = form.name.options[idx].value;
	return 1;
}
