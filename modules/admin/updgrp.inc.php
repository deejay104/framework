<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Vérifie les paramètres

	$grp=checkVar("grp","varchar",5);
	$aut=checkVar("aut","varchar",3);

	if ($grp=="")
	{
		$ret["status"]=500;
		$ret["result"]=utf8_encode("GRP not provided");
		echo json_encode($ret);
		error_log("GRP not provided.");
	  	exit;
	}


	if (($aut!="oui") && ($aut!="non"))
	{
		$aut="oui";
	}

	$ret=array();
	$ret["status"]=200;

	if (GetDroit("ModifGroupe"))
	{
		$id=checkVar("id","array");
$ret["query"]=array();

		if (count($id)>0)
		{
			$q="DELETE FROM ".$MyOpt["tbl"]."_roles WHERE groupe='".$grp."' AND autorise='".$aut."'";
			$res=$sql->Delete($q);
				$ret["query"][]=$q;

			foreach ($_POST['id'] as $role)
			{
				$q="INSERT INTO ".$MyOpt["tbl"]."_roles SET groupe='$grp',role='$role',autorise='".$aut."', uid_creat=".$gl_uid.",uid_maj='".$gl_uid."',dte_creat='".now()."',dte_maj='".now()."'";
				$sql->Insert($q);
				$ret["query"][]=$q;
			}
		}
		else
		{
			$ret["result"]=utf8_encode("Pas de données à mettre à jour");
		}
	}
	else
	{
		$ret["status"]=401;
		$ret["result"]=utf8_encode("Accès non authorisé");
	}
  	echo json_encode($ret);

?>