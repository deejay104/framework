<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");

	require_once ("class/document.inc.php");
	require_once ("class/folder.inc.php");

// ----
	$result=array();

	if ($id==0)
	{
		$result["status"]=500;
		$result["error"]="Id missing";
		echo json_encode($result);	
		exit;
	}

	$paper=new paper_core($id,$sql);
	$folder=new folder_core($paper->val("id_folder"),$sql);

	if (!GetDroit($folder->val("group_read")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	$read=new paper_read_core(0,$sql);
	$read->valid("id_paper",$id);
	$read->valid("uid",$gl_uid);
	$read->valid("dte_read",now());
	$read->Save();

	$result["id"]=$id;
	$result["status"]="signed";

	echo json_encode($result);	
?>