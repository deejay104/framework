<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");

	require_once ("class/amelioration.inc.php");
	
	$res=array();

	$lst=ListeObjets($sql,"ameliorations",array("id"),array("actif"=>"oui","uid_creat"=>$gl_uid));
	foreach($lst as $i=>$d)
	{
		$res["lst"][$i]=$d;
	}

	// Send JSON to the client.
	echo json_encode($res);

?>