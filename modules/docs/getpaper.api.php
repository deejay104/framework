<?php

// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if (!GetDroit("AccesDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- List all papers from a folder
	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

	$id=checkVar("id","numeric");

	$folder=new folder_core($id,$sql);
	if (!GetDroit($folder->val("group_read")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	
	$paper = new paper_core($id,$sql);

	$result=array();
	$result=$paper->export();

	$usr = new user_core($paper->uid_creat,$sql,false);

	$result["data"]["author"]=$usr->fullname;
	$result["data"]["dte_create"]=sql2date($paper->dte_creat,"jour");
	$result["data"]["created"]=DisplayDate($paper->dte_creat);

	$read = new paper_read_core($id,$sql);
	$read->loadFromField(array("id_paper"=>$id,"uid"=>$gl_uid));
	$result["data"]["read"]=($read->val("dte_read")!="0000-00-00 00:00:00") ? true : false;
	$result["data"]["dte_read"]=$read->val("dte_read");
	
	echo json_encode($result);	
?>