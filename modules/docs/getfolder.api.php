<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("AccesDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- List all papers from a folder
	require_once ("class/folder.inc.php");

	$id=checkVar("id","numeric");

	$folder=new folder_core($id,$sql);

	if (!GetDroit($folder->val("group_read")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	
	$result=array();
	$result=$folder->export();

	echo json_encode($result);	
?>