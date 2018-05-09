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
	if (!is_numeric($id))
      { $id=0; }

	if ( (!GetDroit("AccesMembre")) && (!GetMyId($id)) )
	  { FatalError("Accès non autorisé (AccesMembre)"); }

	require_once ("class/document.inc.php");

// ---- Charge le template
	$tmpl_x = new XTemplate (MyRep("detail.htm"));
	$tmpl_x->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Initialisation des variables
	$tmpl_x->assign("form_checktime",$_SESSION['checkpost']);

	$msg_erreur="";
	$msg_confirmation="";

	if ($id>0)
	  { $usr = new user_core($id,$sql,((GetMyId($id)) ? true : false)); }
	else
	  { $usr = new user_core(0,$sql,false); }


// ---- Sauvegarde les infos
	if (($fonc=="Enregistrer") && (($id=="") || ($id==0)) && ((GetDroit("CreeUser"))) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$usr->Create();
		$id=$usr->uid;
	}
	else if (($fonc=="Enregistrer") && ($id=="") && (isset($_SESSION['tab_checkpost'][$checktime])))
	{
		$mod="membres";
		$affrub="index";
	}

	if (($fonc=="Enregistrer") && ((GetMyId($id)) || (GetDroit("ModifUserSauve"))) && (!isset($_SESSION['tab_checkpost'][$checktime])))
	{
		// Sauvegarde les données
		if (count($form_info)>0)
		{
			foreach($form_info as $k=>$v)
		  	{
		  		$msg_erreur.=$usr->Valid($k,$v);
		  	}
		}

		$usr->Save();
		$msg_confirmation.="Vos données ont été enregistrées.<BR>";

		// Sauvegarde la photo
		$form_photo=$_FILES["form_photo"];
		if ($form_photo["name"]!="")
		{
			$lstdoc=ListDocument($sql,$id,"avatar");
		  	
			if (count($lstdoc)>0)
			{
				foreach($lstdoc as $i=>$did)
				{
					$doc = new document_core($did,$sql);
					$doc->Delete();
				}
			}
		  	$doc = new document_core(0,$sql,"avatar");
		  	$doc->droit="ALL";
		  	$msg_erreur.= $doc->Save($id,$_FILES["form_photo"]);
			$doc->Resize(200,240);

			//$msg_erreur.=$usr->SavePicture($form_photo["name"],$form_photo["tmp_name"],$form_photo["type"]);
		}

		// Sauvegarde un document
		if ($_FILES["form_adddocument"]["name"]!="")
		{
		  	$doc = new document_core(0,$sql);
		  	$doc->Save($id,$_FILES["form_adddocument"]);
		}

		$_SESSION['tab_checkpost'][$checktime]=$checktime;		
	}

	// Sauvegarde les droits
	if (($fonc=="Enregistrer") && ($id>0) && (GetDroit("ModifUserDroits")) && (is_array($form_droits)))
	{
		$msg_erreur.=$usr->SaveDroits($form_droits);

	}
	// Sauvegarde les données utilisateurs
	if (($fonc=="Enregistrer") && ($id>0) && (GetDroit("ModifUserDonnees")) && (is_array($form_donnees)))
	{
		$msg_erreur.=$usr->SaveDonnees($form_donnees);
	}

// ---- Supprimer l'utilisateur
	if (($fonc=="delete") && ($id>0) && (GetDroit("SupprimeUser")))
	  {
		$usr->Delete();
		$mod="membres";
		$affrub="index";
	  }

	if (($fonc=="desactive") && ($id>0) && (GetDroit("DesactiveUser")))
	  {
		$usr->Desactive();
	  }

  	if (($fonc=="active") && ($id>0) && (GetDroit("DesactiveUser")))
	  {
		$usr->Active();
	  }

// ---- Modifie les infos
	if (($fonc=="modifier") && ((GetMyId($id)) || (GetDroit("ModifUser"))))
	  {
		$typeaff="form";
	  }
	else
	  {
		$typeaff="html";
	  }
	
// ---- Affiche les infos
	if ((is_numeric($id)) && ($id>0))
	  {
		$usr = new user_core($id,$sql,((GetMyId($id)) ? true : false));
		$usr->LoadDonneesComp();

		$usrmaj = new user_core($usr->uidmaj,$sql);

		$tmpl_x->assign("id", $id);
		$tmpl_x->assign("info_maj", $usrmaj->prenom." ".$usrmaj->nom." le ".sql2date($usr->dtemaj));
		$tmpl_x->assign("info_connect", sql2date($usr->data["dte_login"]));
	
	  }
	else if (GetDroit("CreeUser"))
	  {
		$tmpl_x->assign("titre", "Saisie d'un nouvel utilisateur");

		$usr = new user_core("0",$sql,false);
		$usrmaj = new user_core($usr->uidmaj,$sql);

		$usr->LoadDonneesComp();

		$tmpl_x->assign("id", $id);
		$tmpl_x->assign("info_maj", $usrmaj->prenom." ".$usrmaj->nom." le ".sql2date($usr->dtemaj));

		$typeaff="form";
	  }
	else
	  {
		FatalError("Paramètre d'id non valide");
	  }

// ---- Affiche les menus
	if ((GetMyId($id)) || (GetDroit("ModifUser")))
	  { $tmpl_x->parse("infos.modification"); }

	if ((GetMyId($id)) || (GetDroit("ModifUserPassword")))
	  { $tmpl_x->parse("infos.password"); }

	if (GetDroit("CreeUser"))
	  { $tmpl_x->parse("infos.ajout"); }

	if ((GetDroit("DesactiveUser")) && ($usr->actif=="oui"))
	  { $tmpl_x->parse("infos.desactive"); }

  	if ((GetDroit("DesactiveUser")) && ($usr->actif=="off"))
	  { $tmpl_x->parse("infos.active"); }

	if ((GetDroit("SupprimeUser")) && ($usr->actif=="off"))
	  { $tmpl_x->parse("infos.suppression"); }

  	if (GetDroit("ModifDisponibilite"))
	  { $tmpl_x->parse("infos.disponibilite"); }

// ---- Affiche toutes les donnees
	foreach($usr->data as $k=>$v)
	  { $tmpl_x->assign("form_$k", $usr->aff($k,$typeaff)); }

	if ($typeaff=="form")
	{
		$tmpl_x->parse("corps.photos");
		$tmpl_x->parse("corps.submit");
	}

	if (($typeaff=="form") && ((GetMyId($id)) || (GetDroit("ModifUserPassword"))))
	{
		$tmpl_x->parse("corps.modif_mdp");
	}

	$tmpl_x->parse("corps.type");


	if (GetDroit("ModifUserDroits"))
	  { $tmpl_x->parse("corps.droits"); }

  	if ( GetDroit("ADM") )
	{
	  	$tmpl_x->parse("corps.virtuel");
	}
  	$tmpl_x->parse("corps.disponibilite");

  	if ((is_numeric($id)) && ($id>0))
	  { 
		$lstdoc=ListDocument($sql,$id,"avatar");
		if (count($lstdoc)>0)
		{
			$doc=new document_core($lstdoc[0],$sql);
			$tmpl_x->assign("aff_avatar",$doc->GenerePath(200,240));
		}
		else
		{
			$tmpl_x->assign("aff_avatar",$corefolder."/static/images/none.gif");
		}	


		// ---- Affiche les documents
		$lstdoc=ListDocument($sql,$id,"document");

		if ($typeaff=="form")
		  {
			$doc = new document_core(0,$sql);
			$doc->editmode="form";
			$tmpl_x->assign("form_document",$doc->Affiche());
			$tmpl_x->parse("corps.lst_document");
		  }
		  	
		if (is_array($lstdoc))
		  {
			foreach($lstdoc as $i=>$did)
			  {
				$doc = new document_core($did,$sql);
				$doc->editmode=($typeaff=="form") ? "edit" : "std";
				$tmpl_x->assign("form_document",$doc->Affiche());
				$tmpl_x->parse("corps.lst_document");
			  }
		  }


		// ---- Affiche les données utilisateurs
		if (count($usr->donnees)>0)
		{
			foreach($usr->donnees as $i=>$d)
			{
				$tmpl_x->assign("form_donnees",$usr->AffDonnees($i,$typeaff));
				$tmpl_x->parse("corps.aff_donnees.lst_donnees");
			}
			$tmpl_x->parse("corps.aff_donnees");
		}
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
