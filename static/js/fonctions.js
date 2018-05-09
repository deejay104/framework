function GoTo()
  {
	var code = event.keyCode;
	key=String.fromCharCode(code) ;
	//alert("'"+code+"' "+key); 
	if (code==222) { key='*'; }
	if ((key=='*') || ((key>='A') && (key<='Z'))) { document.location="#"+key; }

  }

function ConfirmeClick(url,texte)
  {
	var is_confirmed = confirm(texte);
	if (is_confirmed) { document.location=url; }
  }
