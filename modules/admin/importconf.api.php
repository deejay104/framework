<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Droit d'export
	if (!GetDroit("AccesConfigExport"))
	{
		header("HTTP/1.0 401 Unauthorized"); exit;		
	}

	
// ---- Import de la config
	if ((isset($_FILES)) && (count($_FILES)>0))
	{

		$tabImport=array();
		foreach($_FILES as $t=>$d)
		{
			$tabImport=json_decode(file_get_contents($d["tmp_name"]),true);
		}

		if (isset($MyOpt["tbl"]))
		{
			$tabImport["MyOpt"]["tbl"]["valeur"]=$MyOpt["tbl"];
		}
		if (isset($MyOpt["mydir"]))
		{
			$tabImport["MyOpt"]["mydir"]["valeur"]=$MyOpt["mydir"];
		}
		if (isset($MyOpt["host"]))
		{
			$tabImport["MyOpt"]["host"]["valeur"]=$MyOpt["host"];
		}
		
		$ret=GenereVariables($tabImport["MyOpt"]);
		
		error_log(print_r($tabImport["MyOpt"],true));
	}

?>