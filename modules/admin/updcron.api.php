<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Vérifie les paramètres
	$res=array();
	if (!isset($_GET["id"]))
	{
		$res["result"]="NOK";
		echo json_encode($res);
		error_log("id not provided.");
	  	exit;
	}
	$id=$_GET["id"];

	if (!isset($_GET["var"]))
	{
		$res["result"]="NOK";
		echo json_encode($res);
		error_log("var not provided.");
	  	exit;
	}
	$var=$_GET["var"];

	if (!isset($_GET["val"]))
	{
		$res["result"]="NOK";
		echo json_encode($res);
		error_log("val not provided.");
	  	exit;
	}
	$val=$_GET["val"];

	$res["result"]="NOK";

	if ($var=="schedule")
	{
		// $val=preg_replace("/[ ]+/","+",substr($val,0,10));
		// $val=preg_replace("/[^0-9]*j/","*1440",$val);
		// $val=preg_replace("/[^0-9]*h/","*60",$val);
		// $val=preg_replace("/[^0-9]*m/","",$val);

		$val=CalcTemps(substr($val,0,10));
		error_log("\$sched=".$val.";");

		// eval("\$sched=".$val.";");
		
		$q="UPDATE ".$MyOpt["tbl"]."_cron SET schedule='".$val."' WHERE id='".$id."'";
		$sql->Update($q);
		$res["result"]="OK";
		$res["value"]=AffTemps($val,"full");
	}
	else if ($var=="actif")
	{
		if ($val=="oui")
		{
			$val="non";
			$q="UPDATE ".$MyOpt["tbl"]."_cron SET actif='$val' WHERE id='".$id."'";
			$sql->Update($q);
			$res["result"]="OK";
			$res["value"]=$val;
		}
		else if ($val=="non")
		{
			$val="oui";
			$q="UPDATE ".$MyOpt["tbl"]."_cron SET actif='$val' WHERE id='".$id."'";
			$sql->Update($q);
			$res["result"]="OK";
			$res["value"]=$val;
		}
	}
	
	echo json_encode($res);

?>