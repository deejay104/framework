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
	require_once ("class/folder.inc.php");
	require_once ("class/document.inc.php");

// ---- Vérifie les variables

	$fid=checkVar("fid","numeric");
	$mid=checkVar("mid","numeric");
	$critere=checkVar("critere","varchar");

	if ($mid==0)
	{
		$mid=$fpars;
	}

// ---- On marque le message comme lu
	if ($gl_uid>0)
	  {
		$query="SELECT forum_msg AS id FROM ".$MyOpt["tbl"]."_forums_lus WHERE forum_msg=$mid AND forum_usr=$gl_uid";
		$res=$sql->QueryRow($query);
		if ($res["id"]>0)
		  {
				$query ="UPDATE ".$MyOpt["tbl"]."_forums_lus SET forum_date = '".now()."' WHERE forum_msg=$mid AND forum_usr=$gl_uid";
				$sql->Update($query);
		  }
		else
		  {	
				$query="INSERT INTO ".$MyOpt["tbl"]."_forums_lus SET forum_msg=$mid, forum_usr=$gl_uid, forum_date='".now()."'";
				$sql->Insert($query);
		  }
	  }

// ---- Récupère les données sur le message
	$query = "SELECT forum.id AS id,";
	$query.= "forum.fid AS fid,";
	$query.= "forum.message AS corps,";
	$query.= "forum.titre AS titre,";
	$query.= "forum.pseudo AS pseudo,";
	$query.= "forum.uid_creat AS uid_creat,";
	$query.= "forum.uid_maj AS uid_maj,";
	$query.= "forum.dte_maj AS date_maj ";
	$query.= "FROM ".$MyOpt["tbl"]."_forums AS forum ";
	$query.= "WHERE forum.id=$mid";
	$res=$sql->QueryRow($query);

	$query="SELECT titre,droit_w AS droit FROM ".$MyOpt["tbl"]."_forums WHERE id=".$res["fid"];
	$resb=$sql->QueryRow($query);


// ---- Initialisation des variables
	$tmpl_x->assign("fid", $fid);
	$tmpl_x->assign("mid", $mid);
	$tmpl_x->assign("idmsg", $res["id"]);

// ---- Titre de la page
	$usr = new user_core($res["uid_creat"],$sql,false);
	$tmpl_x->assign("buque", $usr->fullname);
	$tmpl_x->assign("titre", htmlentities($res["titre"],ENT_HTML5,"UTF-8"));
	$tmpl_x->assign("date", DisplayDate($res["date_maj"]));

	$usr = new user_core($res["uid_maj"],$sql,false);
	$tmpl_x->assign("usr_maj", $usr->fullname);

// ---- Affiche les infos
	$tmpl_x->parse("titre");

// ---- Affiche les boutons



	// <p><A href="index.php?mod=docs&rub=liste&fid={fid}" class=clsLien><IMG src="{path_module}/img/icn32_retour.png">Retour</A></p>
	addPageMenu($corefolder,$mod,"Retour",geturl("forum","liste","fid=".$fid),"icn32_retour.png");

	// <!-- BEGIN: ecrire -->
	// <p><A href="index.php?mod=docs&rub=editer&fid={fid}&fpars={mid}&fprec=detail" title="Répondre à ce message."><IMG src="{path_module}/img/icn32_comment.png" />Répondre</A></p>
	// <!-- END: ecrire -->
	if (GetDroit($resb["droit"]))
	{
		addPageMenu($corefolder,$mod,"Ecrire",geturl("forum","editer","fid=".$fid."&fpars=".$mid."&fprec=detail"),"icn32_comment.png");
	}

	// <!-- BEGIN: modifier -->
	// <p><A HREF="index.php?mod=docs&rub=editer&fid={fid}&fpars={mid}&mid={mid}&fprec=detail" title="Modifier ce message."><IMG src="{path_module}/img/icn32_modifier.png" />Modifier</A></p>
	// <!-- END: modifier -->
	if ((($res["uid_creat"] == $uid) && ($uid>0)) || (GetDroit("ModifMessage")))
	{
		addPageMenu($corefolder,$mod,"Modifier",geturl("forum","editer","fid=".$fid."&fpars=".$mid."&mid=".$mid."&fprec=detail"),"icn32_modifier.png");
	}

	// <!-- BEGIN: supprimer -->
	// <p><A HREF="index.php?mod=docs&rub=liste&fid={fid}&opt={idmsg}&anc=visu" title="Effacer ce message."><IMG src="{path_module}/img/icn32_supprimer.png" />Supprimer</A></p>
	// <!-- END: supprimer -->


	// Boutons de réponse à un message
	if (GetDroit("SupprimeMessage"))
	{
		addPageMenu($corefolder,$mod,"Supprimer",geturl("forum","liste","fid=".$fid."&opt=".$mid."&anc=visu"),"icn32_supprimer.png");
	}

	if (GetDroit("AccesMigrateDocuments"))
	{

		$lst=ListActiveFolders($sql);

		foreach($lst as $i=>$d)
		{
			$tmpl_x->assign("folder_id",$d["id"]);
			$tmpl_x->assign("folder_name",$d["title"]);

			$tmpl_x->parse("corps.migrate.lst_folder");
		}


		 $tmpl_x->parse("corps.migrate");
	}



// ---- Affiche le corps du message
	if ((!preg_match("/<BR>/i",$res["corps"])) && (!preg_match("/<P>/i",$res["corps"])) && (!preg_match("/<DIV>/i",$res["corps"])) && (!preg_match("/<IMG/i",$res["corps"])) && (!preg_match("/<TABLE/i",$res["corps"])))
	  { $msg = nl2br(htmlentities($res["corps"],ENT_HTML5,"UTF-8")); }
	else
	  { $msg = $res["corps"]; }

	$msg=preg_replace("/<\/?SCRIPT[^>]*>/i","",$msg);

	$msg=preg_replace("/((http|https|ftp):\/\/[^ |<]*)/si","<a href='$1' target='_blank'>$1</a>",$msg);
	$msg=preg_replace("/ (www\.[^ |\/]*)/si","<a href='http://$1' target='_blank'>$1</a>",$msg);

	
// ---- Mets en relief les critères de recherche
	$critere = trim($critere);
	if ($critere!="")
	  {
			$tabcrit=explode(" ",$critere);
			foreach($tabcrit as $crit)
			  { $msg=preg_replace("/".$crit."/si","<span class='forum_Message_Selection'>$crit</span>",$msg); }
		}

	$tmpl_x->assign("msg", $msg);

// ---- Affiche les pièces jointes au message
	$lstdoc=ListDocument($sql,$mid,"forum");
	  	
	if ((is_array($lstdoc)) && (count($lstdoc)>0))
	  {
		foreach($lstdoc as $i=>$did)
		  {
			$doc = new document_core($did,$sql);
			$tmpl_x->assign("form_document",$doc->Affiche());
			$tmpl_x->parse("corps.pieces_jointes.lst_document");
		  }
		  $tmpl_x->parse("corps.pieces_jointes");
	  }

// ---- Affiche les réponses
	$query ="SELECT * ";
	$query.="FROM ".$MyOpt["tbl"]."_forums AS forum ";
	$query.="WHERE forum.fil = $mid";
	$sql->Query($query);

	// Charge les messages
	$rep=array();
	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$rep[$sql->data["id"]]=$sql->data;
	}
	
	foreach($rep as $i=>$d)
	{
		$usr = new user_core($d["uid_creat"],$sql,false);

		$lstdoc=ListDocument($sql,$d["uid_creat"],"avatar");
		if (count($lstdoc)>0)
		{
			$doc = new document_core($lstdoc[0],$sql);
			$tmpl_x->assign("rep_usrid",$lstdoc[0]);
		}
		else
		{
			$tmpl_x->assign("rep_usrid","-1");
		}				
		$tmpl_x->assign("rep_usr_creat", $usr->fullname);
		$tmpl_x->assign("rep_titre", $d["titre"]);
		$tmpl_x->assign("rep_dte_creat", DisplayDate($d["dte_creat"]));
		$tmpl_x->assign("rep_mid", $d["id"]);
		$tmpl_x->assign("rep_message", $d["message"]);

		$tmpl_x->parse("corps.lst_reponse");  
	}

// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>
