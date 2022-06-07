<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$title=checkVar("title","varchar",100);
	$dte_start=checkVar("dte_start","date");
	$dte_end=checkVar("dte_end","date");

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

	if (!GetDroit($folder->val("group_write")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }


	if ($title!="") { $paper->valid("title",$title); }
	if ($dte_start!='0000-00-00') { $paper->valid("dte_start",$dte_start); }
	if ($dte_end!='0000-00-00') {  $paper->valid("dte_end",$dte_end); }

	$paper->Save();

	$result["id"]=$id;
	$result["status"]="ok";

	echo json_encode($result);	
?>