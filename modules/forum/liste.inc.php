<?php
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

<?php
	require_once ("class/document.inc.php");

	
// ---- Vérifie les variables
	$critere=checkVar("critere","varchar");
  	$opt=checkVar("opt","numeric");
  	$fid=checkVar("fid","numeric");
  	$max=checkVar("max","numeric",0,20);

  
// ---- Test si l'on doit supprimer un message
	if ((is_numeric($opt)) && ($opt >0))
	{
	  $query = "SELECT fil"." AS fil, uid_creat"." AS uid_creat, uid_maj"." AS uid_maj ";
	  $query.= "FROM ".$MyOpt["tbl"]."_forums"." WHERE id"."=$opt";
	  $res=$sql->QueryRow($query);
	  if ((($uid==$res["uid_creat"]) || ($uid==$res["uid_maj"]) || (GetDroit("SupprimeMessage"))) && ($res["fil"]>0))
	    {
				$query = "UPDATE ".$MyOpt["tbl"]."_forums SET fil=".$res["fil"]." WHERE fil=$opt";
				$sql->Update($query);
				$query ="DELETE FROM ".$MyOpt["tbl"]."_forums_lus WHERE forum_msg=$opt";
				$sql->Delete($query);
				$query ="UPDATE ".$MyOpt["tbl"]."_forums SET actif='non' WHERE id=$opt";
				$sql->Delete($query);
	    }
	}

// ---- On marque tous les messages de ce forum comme lu
	if ($fonc=='marquer')
	  {
		$query = "SELECT forum.id AS mid, forum.fid AS fid ";
		$query.= "FROM ".$MyOpt["tbl"]."_forums AS forum ";
		$query.= "LEFT JOIN ".$MyOpt["tbl"]."_forums_lus AS forumlus ON forum.id = forumlus.forum_msg AND forumlus.forum_usr = $gl_uid ";
		$query.= "WHERE forumlus.forum_msg  IS NULL AND forum.fid = $fid AND forum.actif = 'oui'";
 	  	$sql->Query($query);

		$tablus=array();
		for($i=0; $i<$sql->rows; $i++)
		  {
			$sql->GetRow($i);
			$tablus[$i]=$sql->data["mid"];
		  }

		foreach($tablus as $id)
		  {
			$query ="INSERT INTO ".$MyOpt["tbl"]."_forums_lus SET forum_id='".$fid."', forum_msg=$id, forum_usr=$gl_uid, forum_date='".now()."'"; 
			$sql->Insert($query);
		  }
	  }


// ---- Titre de la fenetre -----------------------
	$tmpl_x->assign("fid",$fid);

	if ($fid>0)
	{
		$query="SELECT titre, message as corps, droit_w AS droit FROM ".$MyOpt["tbl"]."_forums WHERE id=$fid";
		$res=$sql->QueryRow($query);
	
		// Affiche la description du forum
		$tmpl_x->assign("infos",$res["titre"]);
		$tmpl_x->assign("description",nl2br($res["corps"]));
	}
	else if ($critere!="")
	{
		$tmpl_x->assign("infos","Résultat de la recherche");		
		$tmpl_x->assign("description","Terme(s) recherché(s) : ".$critere);
	}



	// <p><A href="index.php?mod=docs" ><IMG src="{path_module}/img/icn32_retour.png">Liste</A></p>
	addPageMenu($corefolder,$mod,"Liste",geturl("forum","",""),"icn32_retour.png");

	// <!-- BEGIN: ecrire -->
	// <p><A href="index.php?mod=docs&rub=editer&fid={fid}&fpars={fid}&fprec=liste" ><IMG src="{path_module}/img/icn32_nouveau.png" title="Ajouter un document à ce classeur." align="absmiddle">Nouveau</A></p>
	// <!-- END: ecrire -->

	// Boutons de réponse à un message
	if ((GetDroit($res["droit"])) && ($critere==""))
	{
		addPageMenu($corefolder,$mod,"Nouveau",geturl("forum","editer","fid=".$fid."&fpars=".$fid."&fprec=liste"),"icn32_nouveau.png");
			// $tmpl_x->parse("infos.ecrire");
	}

	// <p><A href="index.php?mod=docs&rub=liste&fid={fid}&fprec=index&fonc=marquer"><IMG src="{path_module}/img/icn32_valider.png" title="Marquer tous les messages de ce forum comme lus." align="absmiddle">Marquer comme lus</A></p>
	addPageMenu($corefolder,$mod,"Marquer comme lus",geturl("forum","liste","fid=".$fid."&fprec=index&fonc=marquer"),"icn32_valider.png");
	// <p><A href="index.php?mod=docs&rub=recherche&critere={critere}&fid={fid}" ><IMG src="{path_module}/img/icn32_rechercher.png" title="Effectuer une recherche sur l'ensemble des messages des forums." align="absmiddle">Rechercher</A></p>
	addPageMenu($corefolder,$mod,"Rechercher",geturl("forum","recherche","critere=".$critere."&fid=".$fid),"icn32_rechercher.png");


// ---- Affiche la liste des messages

	$critere = trim($critere);
	$tabcrit=explode(" ",$critere);

	$query ="SELECT forum.id AS id, forum.fid AS fid, forum.uid_creat AS usr_id, forum.dte_maj AS date_maj, forum.titre AS titre, forum.message AS message, forumlus.id AS lu, COUNT(reponse.id) AS nbrep ";
	$query.="FROM ".$MyOpt["tbl"]."_forums AS forum ";
	$query.="LEFT JOIN ".$MyOpt["tbl"]."_forums AS reponse ON forum.id = reponse.fil ";
	$query.="LEFT JOIN ".$MyOpt["tbl"]."_forums_lus AS forumlus ON forum.id = forumlus.forum_msg AND forumlus.forum_usr = $gl_uid ";
	$query.="WHERE forum.actif='oui' ";

	if ($fonc=="Rechercher")
	{
		$tabcrit=explode(" ",$critere);
  		foreach($tabcrit as $i=>$t)
	  	{
			$query.=" AND (forum.titre LIKE '%".$t."%' OR  forum.message LIKE '%".$t."%') ";
		}

		if ($fid>0)
		{
			$query.="AND forum.fil = $fid ";
		}
	}
	else
	{
		$query.="AND forum.fil = $fid ";
	}
	$query.="GROUP BY forum.id ORDER BY forum.dte_creat DESC ";
	$query.="LIMIT 0,$max";
	$sql->Query($query);

	// Charge les messages
	$tabmsg=array();
	for($i=0; $i<$sql->rows; $i++)
	  {
		$sql->GetRow($i);
		$tabmsg[$sql->data["id"]]=$sql->data;
	  }

	// Affiche la liste
	$lst=array();
	$col = 50;

	foreach ($tabmsg as $id=>$msg)
	{
		if ($msg["nbrep"]==1)
		{
			$tmpl_x->assign("msg_reponse","(1 réponse)");
			$tmpl_x->parse("corps.lst_msg.aff_reponse");
		}
		else if ($msg["nbrep"]>1)
		{
			$tmpl_x->assign("msg_reponse","(".$msg["nbrep"]." réponses)");
			$tmpl_x->parse("corps.lst_msg.aff_reponse");
		}
		else
		{
			$tmpl_x->assign("msg_reponse","&nbsp;");
		}

		// Etoile si non lu
		if (($msg["lu"]>0) || ($uid==0))
		{
			$tmpl_x->assign("msg_class","forum_Liste_TitreLu");
		}
		else
		{
			$tmpl_x->assign("msg_class","forum_Liste_TitreNonlu");
		}

		// Affiche les pièces jointes au message
		$lstdoc=ListDocument($sql,$msg["id"],"forum");

		if ((is_array($lstdoc)) && (count($lstdoc)>0))
		{
			foreach($lstdoc as $i=>$did)
			{
				$doc = new document_core($did,$sql);
				$tmpl_x->assign("form_document",$doc->Affiche());
				$tmpl_x->parse("corps.lst_msg.aff_piecejointe.lst_document");
			}
			$tmpl_x->parse("corps.lst_msg.aff_piecejointe");
		}


		// Mets en relief les critères de recherche
		//			$txt=GetFirstLine(nl2br(strip_tags($msg["message"], "<br>")));
		$txt=nl2br(strip_tags($msg["message"], '<br>'));
		$txt=preg_replace("/((http|https|ftp):\/\/[^ |<]*)/si","<a href='$1' target='_blank'>$1</a>",$txt);
		$txt=preg_replace("/ (www\.[^ |\/]*)/si","<a href='http://$1' target='_blank'>$1</a>",$txt);

		$critere = trim($critere);
		if ($critere!="")
		{
			$tabcrit=explode(" ",$critere);
			foreach($tabcrit as $crit)
			{
				$txt=preg_replace("/".$crit."/si","<span class='forum_Message_Selection'>$crit</span>",$txt);
			}
		}

		// Paramètres du message
		$usr = new user_core($msg["usr_id"],$sql,false);
		$tmpl_x->assign("id_msg",$msg["id"]);
		$tmpl_x->assign("id_forum",$msg["fid"]);
		$tmpl_x->assign("msg_titre",htmlentities(($msg["titre"]!="") ? $msg["titre"] : " - ",ENT_HTML5,"UTF-8"));
		$tmpl_x->assign("msg_auteur",$usr->fullname);
		$tmpl_x->assign("msg_texte",$txt);
		$tmpl_x->assign("msg_dte",DisplayDate($msg["date_maj"]));
		$tmpl_x->assign("crit",$critere);


		// Affiche l'icone de suppression
		if (GetDroit("SupprimeMessage"))
		{
			$tmpl_x->parse("corps.lst_msg.aff_supprimer");
		}

		$tmpl_x->parse("corps.lst_msg");
	}


	$tmpl_x->assign("fid_forum",$fid);
	$tmpl_x->assign("max_forum",$max+20);

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>
