<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Vérifie les paramètres
	$id=checkVar("id","numeric");
	$fonc=checkVar("fonc","varchar");

// ---- Récupère les infos
	$ret=array();
	$ret["type"]=$fonc;

	if (($fonc=="get") && ($id>0))
	{
		$query = "SELECT * FROM ".$MyOpt["tbl"]."_mailtmpl WHERE id='".$id."'";
		$res=$sql->QueryRow($query);

		if ($res["id"]>0)
		{
			$ret["id"]=$id;
			$ret["titre"]=utf8_encode($res["titre"]);
			$ret["balise"]=utf8_encode($res["balise"]);
			$ret["corps"]=utf8_encode($res["corps"]);
			$ret["result"]="OK";
		}
	}
	
	else if (($fonc=="post") && ($id>0))
	{
	
		$td=array();
		$td["titre"]=addslashes(utf8_decode($_POST["titre"]));
		$td["corps"]=addslashes(utf8_decode($_POST["corps"]));
		$td["uid_maj"]=$gl_uid;
		$td["dte_maj"]=now();
		$sql->Edit("mailtmpl",$MyOpt["tbl"]."_mailtmpl",$id,$td);

		$ret["result"]="OK";
	}
	
// ---- Renvoie le résultat
	echo json_encode($ret);
?>