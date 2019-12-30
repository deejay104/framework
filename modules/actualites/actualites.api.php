<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$fonc=checkVar("fonc","varchar");

	require_once ("class/document.inc.php");

// ----
	$result=array();

	if (($fonc=="get") || ($fonc==""))
	{
		$limit=checkVar("limit","numeric",5);
		$search=addslashes(checkVar("search","varchar",40));

		$q="";
		if ($search!="")
		  {
			$q=" AND (titre LIKE '%".$search."%' OR message LIKE '%".$search."%') ";
		  }

		$query="SELECT * FROM `".$MyOpt["tbl"]."_actualites` WHERE actif='oui' ".(($id>0) ? "AND id<'".$id."'" : "")." ".$q." ORDER BY dte_creat DESC LIMIT 0,$limit";
error_log($query);
		$sql->Query($query);
		$news=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$news[$i]=$sql->data;
		}

		$result["lastid"]=(isset($sql->data["id"])) ? $sql->data["id"] : "0";

		$idprev=0;
		foreach($news as $id=>$d)
		{
			$resusr=new user_core($d["uid_creat"],$sql,false,false);

			// $txt=nl2br(htmlentities($d["message"],ENT_HTML5,"ISO-8859-1"));
			$txt=nl2br($d["message"]);
			$txt=preg_replace("/((http|https|ftp):\/\/[^ \n\r<]*)/si","<a href='$1' target='_blank'>$1</a>",$txt);
			$txt=preg_replace("/ (www\.[^ |\/]*)/si","<a href='http://$1' target='_blank'>$1</a>",$txt);

			$result["news"][$id]["id"]=$d["id"];
			$result["news"][$id]["title"]=utf8_encode($d["titre"]);
			$result["news"][$id]["message"]=utf8_encode($txt);
			$result["news"][$id]["author"]=utf8_encode($resusr->Aff("fullname"));
			$result["news"][$id]["date"]=utf8_encode(DisplayDate($d["dte_creat"]));

			$lstdoc=ListDocument($sql,$d["uid_creat"],"avatar");

			if (count($lstdoc)>0)
			{
				$img=new document_core($lstdoc[0],$sql);
				$result["news"][$id]["avatar"]=utf8_encode($img->GenerePath(64,64));
			}
			else
			{
				$result["news"][$id]["avatar"]=utf8_encode("static/images/icn64_membre.png");
			}

			if (GetDroit("SupprimeActualite"))
			{
				$result["news"][$id]["delete"]="ok";
			}
			if ( (($gl_uid==$d["uid_creat"]) && (time()-strtotime($d["dte_creat"])<3600)) || (GetDroit("ModifActualite")) )
			{
				$result["news"][$id]["edit"]="ok";
			}
		}
		$result["status"]="ok";
	}

	else if (($fonc=="del") && ($id>0))
	{
		if (GetDroit("SupprimeActualite"))
		{
			$td=array("actif"=>"non","uid_maj"=>$gl_uid,"dte_maj"=>now());
			$sql->Edit("actualites",$MyOpt["tbl"]."_actualites",$id,$td);
		}
		$result["status"]="deleted";
	}
	
	echo json_encode($result);	
?>