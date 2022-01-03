<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("AccesDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- List all folders
	require_once ("class/folder.inc.php");

	$result=array();
	
	$lst=ListActiveFolders($sql);

	$ii=0;
	foreach($lst as $i=>$d)
	{
		if (GetDroit($d["group_read"]))
		{
			$result["data"][$ii]=$d;
			$result["data"][$ii]["description"]=nl2br($d["description"]);
			$ii++;
		}
	}
	
	echo json_encode($result);	
?>