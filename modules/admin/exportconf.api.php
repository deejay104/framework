<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Droit d'export
	if (!GetDroit("AccesConfigExport"))
	{
		header("HTTP/1.0 401 Unauthorized"); exit;		
	}

  
// ---- Génére le tableau des variables
	$tabExport=array();
	$tabMyOpt=array();
	
	foreach($MyOpt as $k=>$v)
	{
		if (is_array($v))
		{
			foreach($v as $kk=>$vv)
			{
				$tabMyOpt[$k][$kk]=utf8_encode($vv);
			}
		}
		else
		{
			$tabMyOpt[$k]["valeur"]=utf8_encode($v);
		}
	}

	$tabExport["param"]=$tabMyOpt;

// ---- Exporte les groupes et droits associés
	$query="SELECT * FROM ".$MyOpt["tbl"]."_groupe";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabExport["group"][$sql->data["groupe"]]["description"]=utf8_encode($sql->data["description"]);
		$tabExport["group"][$sql->data["groupe"]]["principal"]=utf8_encode($sql->data["principale"]);
	}

	$query="SELECT * FROM ".$MyOpt["tbl"]."_roles";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabExport["group"][$sql->data["groupe"]]["roles"][$sql->data["id"]]["role"]=$sql->data["role"];
		$tabExport["group"][$sql->data["groupe"]]["roles"][$sql->data["id"]]["autorise"]=$sql->data["autorise"];
	}

	echo json_encode($tabExport);
	
?>