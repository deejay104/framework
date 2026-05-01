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


  function getAvatar(avatar, displayname)
  {
	console.log(avatar);
    if (avatar)
    {
      avatarHtml = '<img src="/'+avatar+'" alt="avatar">';
    } 
    else 
    {
      var initials = displayname
        .trim()
        .split(/\s+/)
        .map(function(w) { return w.charAt(0).toUpperCase(); })
        .slice(0, 2)
        .join('');
      // Couleur déterministe basée sur le nom (toujours la même pour un même utilisateur)
      var colors = ['#4a6fa5','#2e7d32','#c0392b','#7b1fa2','#e67e22','#0288d1','#00796b','#c62828'];
      var colorIndex = displayname.charCodeAt(0) % colors.length;
      var bg = colors[colorIndex];
      avatarHtml = '<div class="feed-initials" style="background:'+bg+';">'+initials+'</div>';

	}
    return avatarHtml;
  }
