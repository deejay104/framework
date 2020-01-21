<?php
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
	
	$MyOpt=array();
	$MyOpt["tbl"]=$gl_tbl;
	$q="SELECT * FROM ".$MyOpt["tbl"]."_config WHERE param='variable'";
	$sql->Query($q);
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		if ($sql->data["name2"]=="")
		{
			$tabMyOpt[$sql->data["name1"]]["valeur"]=$sql->data["value"];
		}
		else
		{
			$tabMyOpt[$sql->data["name1"]][$sql->data["name2"]]=$sql->data["value"];
		}
	}

	$tabExport["param"]=$tabMyOpt;

// ---- Exporte les groupes et droits associés
	$query="SELECT * FROM ".$MyOpt["tbl"]."_groupe";
	$sql->Query($query);
	for($i=0; $i<$sql->rows; $i++)
	{ 
		$sql->GetRow($i);
		$tabExport["group"][$sql->data["groupe"]]["description"]=$sql->data["description"];
		$tabExport["group"][$sql->data["groupe"]]["principal"]=$sql->data["principale"];
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