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
	require_once ("class/amelioration.inc.php");
	if (!GetDroit("AccesAmelioration")) { FatalError("Accès non autorisé (AccesAmelioration)"); }

// ---- Charge le template
	$tmpl_x = new XTemplate (MyRep("detail.htm"));
	$tmpl_x->assign("path_module",$corefolder."/".$module."/".$mod);
	$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);

	$newmsg="Ecrivez votre message...";
	
// ---- Enregistrer
	$msg_erreur="";
	$msg_confirmation="";
	if (($fonc=="Enregistrer") && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$pb=new amelioration_class($id,$sql);
		if (count($form_data)>0)
		{
			foreach($form_data as $k=>$v)
		  	{
		  		$msg_erreur.=$pb->Valid($k,$v);
		  	}
			$msg_confirmation.="Vos données ont été enregistrées.<BR>";
		}

		$pb->Save();
		if ($id==0)
		{
			$id=$pb->id;
		}

		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}
// ---- Supprimer
	if (($fonc=="supprimer") && ($id>0) && (GetDroit("SupprimeAmelioration")))
	{
		$pb=new amelioration_class($id,$sql);
		$pb->Delete();
		$mod="ameliorations";
		$affrub="index";
	}

// ---- Sauver une réponse
	if (($fonc=="Poster") && ($id>0) && (GetDroit("CreeAmeliorationCommentaire")) && ($form_desc!="") && ($form_desc!=$newmsg) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$pb=new amelioration_class($id,$sql);
		$pb->AddCommentaire($form_desc);
		$_SESSION['tab_checkpost'][$checktime]=$checktime;
	}

// ---- Affiche le menu
	$aff_menu="";
	require("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);


// ---- Initialise les variables
	if (!is_numeric($id))
	{
		$id=0;
	}
	
// ---- Modifie les infos
	if (($id==0) && (GetDroit("CreeAmelioration")))
	{
		$typeaff="form";
	}
	else if (($fonc=="modifier") && (GetDroit("ModifAmelioration")))
	{
		$typeaff="form";
	}
	else
	{
		$typeaff="html";
	}

// ---- Charge le problème

	$pb = new amelioration_class($id,$sql);

	$pb->Render("form",$typeaff);
	$tmpl_x->assign("form_numid",$pb->aff("id"));

	$lstdoc=ListDocument($sql,$pb->uid_creat,"avatar");

	if (count($lstdoc)>0)
	{
		$img=new document_core($lstdoc[0],$sql);
		$tmpl_x->assign("form_avatar",$img->GenerePath(64,64));
	}
	else
	{
		$tmpl_x->assign("form_avatar",$corefolder."/static/images/icn64_membre.png");
	}
	
	if ($typeaff=="form")
	{
		$tmpl_x->parse("corps.photos");
		$tmpl_x->parse("corps.submit");
	}

// ---- Réponses

	$tmpl_x->assign("reponse_vide",$newmsg);

	$lst=$pb->ListeCommentaire();

	foreach($lst as $i=>$d)
	{
		if ($d["uid_creat"]>0)
		{
			$lstdoc=ListDocument($sql,$d["uid_creat"],"avatar");

			if (count($lstdoc)>0)
			{
				$img=new document_core($lstdoc[0],$sql);
				$tmpl_x->assign("msg_avatar",$img->GenerePath(64,64));
			}
			else
			{
				$tmpl_x->assign("msg_avatar",$corefolder."/static/images/icn64_membre.png");
			}
		}
		else
		{
			$tmpl_x->assign("msg_avatar",$corefolder."/static/images/icn64_dev.png");
		}

		$tmpl_x->assign("msg_autheur",$d["usr_creat"]->aff("fullname"));
		$tmpl_x->assign("msg_date",DisplayDate($d["dte_creat"]));
		$tmpl_x->assign("msg_message",$d["description"]);
		$tmpl_x->parse("corps.aff_commentaire");
	}
	
// ---- Messages
	if ($msg_erreur!="")
	{
		affInformation($msg_erreur,"error");
	}		

	if ($msg_confirmation!="")
	{
		affInformation($msg_confirmation,"ok");
	}
	
// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

?>