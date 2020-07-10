<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- 
	$sql->show=false;
	
	$id=checkVar("id","numeric");
	$fonc=checkVar("fonc","varchar");
	$type=checkVar("type","varchar");

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
		$sql->Query($query);
		$news=array();
		for($i=0; $i<$sql->rows; $i++)
		{ 
			$sql->GetRow($i);
			$news[$i]=$sql->data;
		}

		$result["lastid"]=(isset($sql->data["id"])) ? $sql->data["id"] : "0";

		$idprev=0;
		foreach($news as $nid=>$d)
		{
			$resusr=new user_core($d["uid_creat"],$sql,false,false);

			// $txt=nl2br(htmlentities($d["message"],ENT_HTML5,"ISO-8859-1"));
			$txt=nl2br($d["message"]);
			$txt=preg_replace("/((http|https|ftp):\/\/[^ \n\r<]*)/si","<a href='$1' target='_blank'>$1</a>",$txt);
			$txt=preg_replace("/ (www\.[^ |\/]*)/si","<a href='http://$1' target='_blank'>$1</a>",$txt);

			$lstdoc=ListDocument($sql,$d["id"],"actualite");
			if (is_array($lstdoc))
			{
				foreach($lstdoc as $i=>$did)
				{
					$doc = new document_core($did,$sql);
					$doc->editmode="regular";
					if ($doc->isImage())
					{
						list($w, $h) = $doc->getSize();
						
						if ($w>500)
						{
							$w=500;
						}
						if ($h>400)
						{
							$h=400;
						}
												
						$txt.="<p><img src='doc.php?id=".$doc->id."&type=image&width=".$w."&height=".$h."'></p>";
					}
					else
					{
						$txt.=$doc->Affiche();
					}
				}
			}


			$result["news"][$nid]["id"]=$d["id"];
			$result["news"][$nid]["title"]=$d["titre"];
			// $result["news"][$nid]["message"]=utf8_encode($txt);
			$result["news"][$nid]["message"]=$txt;
			// $result["news"][$nid]["message"]="éèà €";
			$result["news"][$nid]["author"]=$resusr->Aff("fullname");
			$result["news"][$nid]["date"]=DisplayDate($d["dte_creat"]);

			$lstdoc=ListDocument($sql,$d["uid_creat"],"avatar");

			if (count($lstdoc)>0)
			{
				$img=new document_core($lstdoc[0],$sql);
				$result["news"][$nid]["avatar"]=$img->GenerePath(64,64);
			}
			else
			{
				$result["news"][$nid]["avatar"]="static/images/icn64_membre.png";
			}

			if (GetDroit("SupprimeActualite"))
			{
				$result["news"][$nid]["delete"]="ok";
			}
			if ( (($gl_uid==$d["uid_creat"]) && (time()-strtotime($d["dte_creat"])<3600)) || (GetDroit("ModifActualite")) )
			{
				$result["news"][$nid]["edit"]="ok";
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
	else if (($fonc=="post") && ($type=="file") && ($_FILES['file']['name']))
	{
		// $filename = $_FILES['file']['name'];
		$doc = new document_core(0,$sql,"actualite");

		if ($id==0)
		{
			$td=array();
			$td["titre"]="Nouvelle actualité";
			$td["mail"]="draft";
			$td["uid_creat"]=$gl_uid;
			$td["dte_creat"]=now();	
			$id=$sql->Edit("actualites",$MyOpt["tbl"]."_actualites",0,$td);
		}

		$r=$doc->Save($id,$_FILES["file"]["name"],$_FILES["file"]["tmp_name"]);
		$result["id"]=$id;
		$result["status"]="ok";
		if ($r!="")
		{
			$result["error"]=$r;
			$result["status"]="nok";
		}
		$doc->editmode="regular";

		if ($doc->isImage())
		{
			list($w, $h) = $doc->getSize();
			
			if ($w>500)
			{
				$w=500;
			}
			if ($h>400)
			{
				$h=400;
			}
			$result["type"]="image";
			$result["link"]="<img src='doc.php?id=".$doc->id."&type=image&width=".$w."&height=".$h."'>";
		}
		else
		{
			$result["type"]="file";
			$result["link"]=$doc->Affiche();
		}
		
	}

	echo json_encode($result);	
?>