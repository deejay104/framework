<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$ret=array();
	$ret["result"]="OK";
	$ret["data"]="";

	$sql->show=true;

	$ret["result"]="OK";
	$ret["data"]="";

	$tabMails=array();

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

	if (file_exists($appfolder."/modules/admin/emails.inc.php"));
	{
		require_once($appfolder."/modules/admin/emails.inc.php");
	}

// *****************************************************************

	foreach($tabMails as $name=>$d)
	{
		$query="SELECT id FROM ".$MyOpt["tbl"]."_mailtmpl WHERE nom='".$name."'";
		$res=$sql->QueryRow($query);

		if ($res["id"]>0)
		{
			$ret["data"].=AjoutLog("- Update: ".$name);
			if ($fonc=="init")
			{
				$q="UPDATE ".$MyOpt["tbl"]."_mailtmpl SET titre='".addslashes($d["titre"])."', corps='".addslashes($d["mail"])."', balise='".$d["balise"]."',uid_maj='".$gl_uid."', dte_maj='".now()."' WHERE id=".$res["id"];
				$sql->Update($q);
			}
			else
			{
				$q="UPDATE ".$MyOpt["tbl"]."_mailtmpl SET balise='".$d["balise"]."',uid_maj='".$gl_uid."', dte_maj='".now()."' WHERE id=".$res["id"];
				$sql->Update($q);
			}
		}
		else
		{
			$ret["data"].=AjoutLog("- Insert: ".$name);
			$q="INSERT INTO ".$MyOpt["tbl"]."_mailtmpl SET nom='".$name."',titre='".addslashes($d["titre"])."', corps='".addslashes($d["mail"])."', balise='".$d["balise"]."', uid_creat='".$gl_uid."', dte_creat='".now()."',uid_maj='".$gl_uid."', dte_maj='".now()."'";
			$sql->Insert($q);
		}
	}


// ---- Renvoie le log
	echo json_encode($ret);
  
  
function AjoutLog($txt)
{
	return utf8_encode(htmlentities($txt,ENT_HTML5,"ISO-8859-1"))."<br />";
}
?>