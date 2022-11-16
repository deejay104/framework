<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Droit d'export
	if (!GetDroit("AccesConfigExport"))
	{
		header("HTTP/1.0 401 Unauthorized"); exit;		
	}

	
// ---- Import de la config
	$ret=array();
	$ret["status"]=200;
	$ret["result"]="OK";
	$ret["data"]="";

	if ((isset($_FILES)) && (count($_FILES)>0))
	{

		$tabImport=array();

		if (isset($_FILES))
		{
			foreach($_FILES as $t=>$d)
			{
				if (file_exists($d["tmp_name"]))
				{
					$f=file_get_contents($d["tmp_name"]);
					$tabImport=json_decode($f,true);
					error_log("Import ".$d["tmp_name"].", ".count($tabImport)." values");
				}
			}
		}
		

		//Import des variables
		if (($_REQUEST["importconf"]=="on") && (is_array($tabImport)) && (count($tabImport["param"])>0))
		{
			if (isset($MyOpt["tbl"]))
			{
				$tabImport["param"]["tbl"]["valeur"]=$MyOpt["tbl"];
			}
			if (isset($MyOpt["mydir"]))
			{
				$tabImport["param"]["mydir"]["valeur"]=$MyOpt["mydir"];
			}
			if (isset($MyOpt["host"]))
			{
				$tabImport["param"]["host"]["valeur"]=$MyOpt["host"];
			}

			foreach($tabImport["param"] as $k=>$v)
			{
				if (!is_array($v))
				{
					$tabImport["param"][$k]=$v;
				}
			}
			
			$ret=GenereVariables($tabImport["param"]);
			
			$ret["data"].=AjoutLog("Import variables terminé");
		}
		else
		{
			$ret["data"].=AjoutLog("Erreur de l'import des variables");
		}

		// Import des groupes
		if (($_REQUEST["importgroup"]=="on") && (is_array($tabImport)) && (count($tabImport["group"])>0))
		{
			error_log("Import groups");
			
			$q="DELETE FROM ".$MyOpt["tbl"]."_groupe";
			$sql->Delete($q);
			$q="DELETE FROM ".$MyOpt["tbl"]."_roles";
			$sql->Delete($q);

			foreach($tabImport["group"] as $g=>$d)
			{
				$ret["data"].=AjoutLog("Import ".$g);
				$q="INSERT INTO ".$MyOpt["tbl"]."_groupe SET groupe='".$g."',description='".$d["description"]."',principale='".$d["principal"]."'";
				$sql->Insert($q);
				foreach($d["roles"] as $i=>$dd)
				{
					$q="INSERT INTO ".$MyOpt["tbl"]."_roles SET groupe='".$g."',role='".$dd["role"]."',autorise='".$dd["autorise"]."'";
					$sql->Insert($q);
				}
			}
			
			$ret["data"].=AjoutLog("Import des groupes terminé");
		}
		else
		{
			$ret["data"].=AjoutLog("Erreur de l'import des groupes");
		}
		
		echo json_encode($ret);
	}

	
function AjoutLog($txt)
{
	return htmlentities($txt,ENT_HTML5,"UTF-8")."<br />";
}	

?>