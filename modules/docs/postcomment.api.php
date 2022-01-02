<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$description=checkVar("comment","text");

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

	if (!GetDroit($folder->val("group_write")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	$comment=new comment_core(0,$sql);
	$comment->valid("id_paper",$id);
	$comment->valid("description",$description);
	$comment->Save();

	$result["id"]=$id;
	$result["status"]="ok";
	$result["description"]=nl2br($description);

	$usr = new user_core($gl_uid,$sql,false);

	$result["author"]=$usr->fullname;
	$result["created"]=DisplayDate(now());

	$lstdoc=ListDocument($sql,$gl_uid,"avatar");

	if (count($lstdoc)>0)
	{
		$img=new document_core($lstdoc[0],$sql);
		$result["avatar"]=$img->GenerePath(64,64);
	}
	else
	{
		$result["avatar"]="static/images/icn64_membre.png";
	}


	echo json_encode($result);	
?>