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

	$tabExport["MyOpt"]=$tabMyOpt;

// ---- Exporte les groupes et droits associés
	

	
	echo json_encode($tabExport);
	
?>