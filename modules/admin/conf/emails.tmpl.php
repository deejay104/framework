<?

// *****************************************************************
	$tabMails["echeance"]=Array
	(
		"titre"=>"",
		"balise"=>"description,type,date",
		"mail"=>
"Cher(e) ami(e) pilote,

L'échéance {description} {type} {date}.

Je t'invite à faire le nécessaire pour la renouveler sans oublier de m'envoyer une copie pour mise à jour de ton profil sur le site.

A bientôt au club

Le Président"
	);
// *****************************************************************

// *****************************************************************
	$tabMails["chgpwd"]=Array
	(
		"titre"=>"Changement de votre mot de passe",
		"balise"=>"username,initiales,url",
		"mail"=>
"Bonjour,

Votre mot de passe a été modifié :
Utilisateur : {username}
Initiales : {initiales}

Rendez-vous sur {url} pour vous connecter.

Cordialement"
	);
// *****************************************************************


// *****************************************************************
	$tabMails["amelioration"]=Array
	(
		"titre"=>"",
		"balise"=>"description,num,status,id,url",
		"mail"=>
"
Notification de mise à jour de l'amélioration {num} :
Status : {status}

Description :
{description}


<a href='{url}/index.php?mod=ameliorations&rub=detail&id={id}'>-Détail-</a>

"
	);
// *****************************************************************


?>