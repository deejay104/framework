<?
/*
    MnMs Framework
    Copyright (C) 2018 Matthieu Isorez

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>

<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Charge les dépendances
	require_once ("class/document.inc.php");
	require_once ("class/echeance.inc.php");


// ---- Variables
	$id=checkVar("id","numeric");
	$form_titre=checkVar("form_titre","varchar");
	$form_message=checkVar("form_message","text");
	
// ---- Enregistre le post
	$txtnewmsg="Ecrivez votre message...";

	if ( ($fonc=="Poster") && (!isset($_SESSION['tab_checkpost'][$checktime])) )
	{
		$_SESSION['tab_checkpost'][$checktime]=$checktime;

		if ($form_message!=$txtnewmsg)
		{
			$td=array(
				"titre"=>addslashes(strip_tags($form_titre)),
				"message"=>addslashes(strip_tags($form_message)),
				"uid_maj"=>$gl_uid,
				"dte_maj"=>now()
				);		
			if ($id>0)
			{
				$query="SELECT uid_creat FROM `".$MyOpt["tbl"]."_actualites` WHERE id='$id'";
				$res = $sql->QueryRow($query);

				if ( (GetDroit("ModifActualite")) || ( ($gl_uid==$res["uid_creat"]) && (time()-strtotime($d["dte_creat"])<3600) ) )
				{
					$sql->Edit("actualites",$MyOpt["tbl"]."_actualites",$id,$td);
				}
			}
			else
			{
				$td["uid_creat"]=$gl_uid;
				$td["dte_creat"]=now();	
				$sql->Edit("actualites",$MyOpt["tbl"]."_actualites",0,$td);
			}
			$id=0;

		}
	}

// ---- url de l'api
	$tmpl_x->assign("site_title",$MyOpt["site_title"]);
	$tmpl_x->assign("apiurlget",geturlapi("actualites","actualites","get","q=1"));
	$tmpl_x->assign("apiurldel",geturlapi("actualites","actualites","del","q=1"));

// ---- Affiche les échéances
	$lstdte=ListEcheance($sql,$gl_uid);
		
	if (is_array($lstdte))
	{
		foreach($lstdte as $i=>$did)
		  {
			$dte = new echeance_core($did,$sql,$gl_uid);
			$dte->editmode="html";
			$tmpl_x->assign("form_echeance",$dte->Affiche());
			$tmpl_x->parse("corps.lst_echeance");
		  }
	}

// ---- Derniers message des forums

	$query = "SELECT COUNT(forums.id) AS nb FROM ".$MyOpt["tbl"]."_forums AS forums LEFT JOIN ".$MyOpt["tbl"]."_forums_lus AS forums_nonlus ON forums_nonlus.forum_usr=$uid AND forums.id=forums_nonlus.forum_msg WHERE forums_nonlus.forum_msg IS NULL";
	$res=$sql->QueryRow($query);
	$tmpl_x->assign("nb_nonlus",(($res["nb"]>1) ? $res["nb"]." messages" : (($res["nb"]==1) ? $res["nb"]." message" : "Aucun")));
	$tmpl_x->assign("color_nonlus",($res["nb"]>0) ? "red" : "black");


// ---- Derniers documents

	if ($id>0)
	{
		$query="SELECT titre,message FROM `".$MyOpt["tbl"]."_actualites` WHERE id='$id'";
		$res = $sql->QueryRow($query);
		$tmpl_x->assign("news_title", $res["titre"]);
		$tmpl_x->assign("news_message", $res["message"]);
		$tmpl_x->assign("new_color", "000000");	
	}
	else
	{
		$tmpl_x->assign("news_title", "Nouvelle actualité");
		$tmpl_x->assign("news_message", $txtnewmsg);
		$tmpl_x->assign("new_color", "bbbbbb");	
	}

	$tmpl_x->assign("news_title_clear", "Nouvelle actualité");
	$tmpl_x->assign("news_message_clear", $txtnewmsg);
	$tmpl_x->assign("form_id", $id);
	
// ---- Personalisation
  	
	if (file_exists($appfolder."/modules/actualites/custom.inc.php"))
	{
		$custom="";
		require($appfolder."/modules/actualites/custom.inc.php");
		
		if ($custom!="")
		{
			$tmpl_x->assign("aff_custom",$custom);
		}
	}

// ---- Affecte les variables d'affichage

	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>