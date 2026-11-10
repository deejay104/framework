<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$data=checkVar("data","array");
	$fonc=checkVar("fonc","varchar",10);

	require_once ("class/folder.inc.php");

// ----
	$result=array();

	if ($id==0)
	{
		apiError(402,"Id missing","bad_parameter");
	}

	$paper=new paper_core($id,$sql);
	$folder=new folder_core($paper->val("id_folder"),$sql);

// ---- Check permission
	if (!GetDroit($folder->val("group_write")))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Duplicate paper
	if ($fonc=="duplicate")
	{
		$newpaper=new paper_core(0,$sql);

		/*
		"id_folder"=>Array("type" => "number", "index"=>1),
		"title"=>Array("type" => "varchar", "len"=>100),
		"refid"=>Array("type" => "numeric","default"=>0),
		"version"=>Array("type" => "numeric","default"=>1),
		"controlled"=>Array("type" => "bool", "default"=>"non"),
		"nature"=>Array("type" => "enum", "default"=>"GEN"),
		"status"=>Array("type" => "enum", "default"=>"open"),
		"mandatory"=>Array("type" => "bool", "default"=>"non"),
		"dte_start"=>Array("type" => "date"),
		"dte_end"=>Array("type" => "date"),
		"dte_review"=>Array("type" => "date"),
		"uid_checked"=>Array("type" => "numeric"),
		"dte_checked"=>Array("type" => "datetime"),
		"uid_approved"=>Array("type" => "numeric"),
		"dte_approved"=>Array("type" => "datetime"),
*/

		$newpaper->valid("id_folder",$paper->val("id_folder"));
		$newpaper->valid("title",$paper->val("title"));
		$newpaper->valid("refid",$paper->val("refid"));
		$newpaper->valid("version",$paper->val("version")+1);
		$newpaper->valid("controlled",$paper->val("controlled"));
		$newpaper->valid("nature",$paper->val("nature"));
		$newpaper->valid("status","draft");
		$newpaper->valid("mandatory",$paper->val("mandatory"));
		$newpaper->Save();

		$lst=$paper->docs();

		foreach($lst as $i)
		{
			$doc=new paper_docs_core($i,$sql);
			$newdoc=new paper_docs_core(0,$sql);
			$newdoc->valid("id_paper",$newpaper->id);
			$newdoc->valid("id_doc",$doc->val("id_doc"));
			$newdoc->valid("name",$doc->val("name"));
			$newdoc->Save();
		}

		$result["id"]=$newpaper->id;
		$result["oldid"]=$id;
		$result["status"]="ok";
		$result["action"]="duplicate";

		echo json_encode($result);	
		exit;		
	}


// ---- Check approval
	if ( ($fonc=="approve") && ($paper->val("uid_approved")==0)  && ($paper->val("uid_checked")==$gl_uid) )
	{
		apiError(402,"Same reviewer and approver","bad_parameter");
	}


// ---- update data

	if (is_array($data))
	{
		foreach($data as $k=>$v)
		{
			$paper->valid($k,$v);
		}
	}

	if (($paper->val("refid")==0) && ($paper->val("controlled")=="oui") )
	{  
		$refid=$paper->getNextID();
		$paper->valid("refid",$refid); 
	}


	if (($paper->val("status")=="open") && ($paper->val("controlled")=="oui") )
	{  
		$paper->valid("status","draft"); 
	}
	if ( ($fonc=="submit") && ($paper->val("uid_checked")==0) )
	{  
		$paper->valid("status","submitted"); 
		$paper->valid("uid_checked",$gl_uid); 
		$paper->valid("dte_checked",now()); 
		$result["action"]="submit";
	}
	if ( ($fonc=="approve") && (GetDroit("ApproveDocument")) && ($paper->val("uid_approved")==0)  && ($paper->val("uid_checked")!=$gl_uid) )
	{
		$paper->valid("status","approved"); 
		$paper->valid("uid_approved",$gl_uid); 
		$paper->valid("dte_approved",now());

		$paper->ArchiveOlder();
		$result["action"]="approve";
	}

	$paper->Save();

	$result["id"]=$id;
	$result["status"]="ok";

	echo json_encode($result);	
?>