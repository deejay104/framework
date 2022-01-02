<?php

	if (!GetDroit("AccesMigrateDocuments"))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	$src=checkVar("src","numeric");
	$dst=checkVar("dst","numeric");


	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

	$result=array();
	$result["status"]=500;
	if (($src>0) && ($dst>0))
	{
		$query = "SELECT forum.id AS id,";
		$query.= "forum.fid AS fid,";
		$query.= "forum.message AS corps,";
		$query.= "forum.titre AS titre,";
		$query.= "forum.pseudo AS pseudo,";
		$query.= "forum.uid_creat AS uid_creat,";
		$query.= "forum.dte_creat AS dte_creat,";
		$query.= "forum.uid_maj AS uid_maj,";
		$query.= "forum.dte_maj AS dte_maj ";
		$query.= "FROM ".$MyOpt["tbl"]."_forums AS forum ";
		$query.= "WHERE forum.id=".$src;
		$res=$sql->QueryRow($query);

		$paper=new paper_core(0,$sql);
		$folder=new folder_core($paper->val("id_folder"),$sql);

		$paper->valid("title",$res["titre"]);
		$paper->valid("id_folder",$dst);
		$paper->uid_creat=$res["uid_creat"];
		$paper->dte_creat=$res["dte_creat"];
		$paper->uid_maj=$res["uid_maj"];
		$paper->dte_maj=$res["dte_maj"];

		$paper->settime=false;
		$paper->Save();

		if ($res["corps"]!="")
		{
			$comment=new comment_core(0,$sql);
			$comment->valid("id_paper",$paper->id);
			$comment->valid("description",$res["corps"]);
			$comment->uid_creat=$res["uid_creat"];
			$comment->dte_creat=$res["dte_creat"];
			$comment->uid_maj=$res["uid_maj"];
			$comment->dte_maj=$res["dte_maj"];
			$comment->settime=false;
			$comment->Save();
		}

		$lstdoc=ListDocument($sql,$src,"forum");

		if ((is_array($lstdoc)) && (count($lstdoc)>0))
		{
			foreach($lstdoc as $i=>$did)
			{
				$doc = new document_core($did,$sql);
				$doc->droit=$folder->val("group_read");
				$doc->uid=$paper->id;
				$doc->type="paper";
				$doc->Update();
			}
		}

		$query="UPDATE ".$MyOpt["tbl"]."_forums SET actif='non' WHERE id='".$src."'";
		$res=$sql->Update($query);

		$result["status"]=200;
		
	}
	
	
	echo json_encode($result);
?>