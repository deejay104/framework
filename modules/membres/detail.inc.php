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
	$id=checkVar("id","numeric");
	if ( (!GetDroit("AccesMembre")) && (!GetMyId($id)) )
	  { FatalError("Accès non autorisé (AccesMembre)"); }

	require_once ("class/document.inc.php");
	require_once ("class/echeance.inc.php");

// ---- Charge le template
	$tmpl_x->assign("path_module",$MyOpt["host"]."/".$corefolder."/".$module."/".$mod);

// ---- Annule
	if ($fonc==$tabLang["lang_cancel"])
	{
		header('Location: /membres/detail?id='.$id, true, 303);
		exit;
	}

// ---- Initialisation des variables

	$update=checkVar("update","numeric");

	$form_data=checkVar("form_data","array");
	$form_droits=checkVar("form_droits","array");
	$form_donnees=checkVar("form_donnees","array");
	$form_echeance=CheckVar("form_echeance","array");
	$form_echeance_type=CheckVar("form_echeance_type","array");
	$form_echeance_doc=CheckVar("form_echeance_doc","array");

	$msg_erreur="";
	$msg_confirmation="";

	if ($id>0)
	  { $usr = new user_core($id,$sql,((GetMyId($id)) ? true : false)); }
	else
	  { $usr = new user_core(0,$sql,false); }

// ---- Sauvegarde les infos
	if ( ($fonc==$tabLang["lang_save"]) && (($id=="") || ($id==0)) && ((GetDroit("CreeUser"))) )
	{
		$usr->Create();
		$id=$usr->id;
	}
	else if ( ($fonc==$tabLang["lang_save"]) && ($id==0) )
	{
		header('Location: /membres', true, 303);
    	exit;
	}

	$ok=0;
	if ( ($fonc==$tabLang["lang_save"]) && ((GetMyId($id)) || (GetDroit("ModifUser"))) )
	{
		// Sauvegarde les données
		if (count($form_data)>0)
		{
			foreach($form_data as $k=>$v)
		  	{
				if ($usr->isFields($k))
				{
					//$err=$usr->Valid($k,$v);
					$err=$usr->Valid($k,$form_data[$k]);
					if ($err!="")
					{
						affInformation($err,"error");
					}
				}
		  	}
		}
		$usr->Save();
		if ($id==0)
		{
			$id=$usr->id;
		}

		// Sauvegarde la photo
		if (isset($_FILES["form_photo"]))
		{
			$form_photo=$_FILES["form_photo"];
			if ((isset($form_photo["name"][0])) && ($form_photo["name"][0]!=""))
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
				$err=$doc->Save($id,$_FILES["form_photo"]["name"],$_FILES["form_photo"]["tmp_name"]);
				$doc->Resize(200,240);

				affInformation($err,"error");
			}			
		}

		// Sauvegarde un document
		if ((isset($_FILES["form_adddocument"])) && (is_array($_FILES["form_adddocument"]["name"])))
		{
			foreach($_FILES["form_adddocument"]["name"] as $i=>$n)
			{
				if ($n!="")
				{
					$doc = new document_core(0,$sql);
					$doc->Save($id,$_FILES["form_adddocument"]["name"][$i],$_FILES["form_adddocument"]["tmp_name"][$i]);
				}
			}
		}

		// Sauvegarde des échéances
		if ((isset($form_echeance)) && (is_array($form_echeance)))
		{
			foreach($form_echeance as $i=>$d)
			{
				$dte = new echeance_core($i,$sql);
				if ((!is_numeric($i)) || ($i==0))
				{
					$dte->typeid=$form_echeance_type[$i];
					$dte->doc=0;
					$dte->uid=$id;
				}
				if (($d!='') && ($d!='0000-00-00'))
				{
					$dte->dte_echeance=$d;
					$dte->doc=((isset($form_echeance_doc[$i])) && (is_numeric($form_echeance_doc[$i]))) ? $form_echeance_doc[$i] : 0;
					$dte->Save();
				}
				else
				{
					$dte->Delete();
				}
			}
		}
		$ok=1;
	}

	// Sauvegarde les droits
	if (($fonc==$tabLang["lang_save"]) && ($id>0) && (GetDroit("ModifUserDroits")) && (is_array($form_droits)))
	{
		$err=$usr->SaveDroits($form_droits);
		affInformation($err,"error");
		$ok=1;
	}
	if (($fonc==$tabLang["lang_save"]) && ($id>0) && (GetDroit("ModifUserGroupe")) && ($usr->data["groupe"]!=""))
	{
		$err=$usr->AddGroupe($usr->data["groupe"]);
		affInformation($err,"error");
		$ok=1;
	}

	// Sauvegarde les données utilisateurs
	if (($fonc==$tabLang["lang_save"]) && ($id>0) && (GetDroit("ModifUserDonnees")) && (is_array($form_donnees)))
	{
		$usr->LoadDonneesComp();
		$err=$usr->SaveDonneesComp($form_donnees);
		affInformation($err,"error");
		$ok=1;
	}

	if ($ok==1)
	{
		header('Location: /membres/detail?id='.$id.'&update=1', true, 303);
		exit;
	}


// ---- Supprimer l'utilisateur
	if (($fonc=="delete") && ($id>0) && (GetDroit("SupprimeUser")))
	{
		$usr->Delete();
		header('Location: /membres', true, 303);
		exit;
	}

	if (($fonc=="desactive") && ($id>0) && (GetDroit("DesactiveUser")))
	{
		$usr->Desactive();
	}

  	if (($fonc=="active") && ($id>0) && (GetDroit("DesactiveUser")))
	{
		$usr->Active();
	}

// ---- Message d'update
	if ($update==1)
	{
		affInformation($tabLang["lang_datasaved"],"ok");
	}
	else if ($update==2)
	{
		affInformation("Votre mot de passe a été mis à jour.","ok");
	}


// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);


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

		$tmpl_x->assign("id", $id);

		$usrmaj = new user_core($usr->uid_maj,$sql);
		$tmpl_x->assign("info_maj", $usrmaj->aff("fullname")." ".$usr->LastUpdate());
		$tmpl_x->assign("info_connect", sql2date($usr->data["dte_login"]));
	}
	else if (GetDroit("CreeUser"))
	{
		$tmpl_x->assign("titre", "Saisie d'un nouvel utilisateur");

		$usr = new user_core("0",$sql,false);
		$usrmaj = new user_core($usr->uid_maj,$sql);

		$usr->LoadDonneesComp();

		$tmpl_x->assign("id", $id);
		$tmpl_x->assign("info_maj", $usrmaj->aff("fullname")." ".$usr->LastUpdate());

		$typeaff="form";
	}
	else
	{
		FatalError("Paramètre d'id non valide");
	}

// ---- Affiche toutes les donnees
	foreach($usr->data as $k=>$v)
	{
		$tmpl_x->assign("form_".$k, $usr->aff($k,$typeaff));
	}

	if ($typeaff=="form")
	{
		if ((GetMyId($id)) || (GetDroit("ModifUserInfos")))
		{
			$tmpl_x->parse("corps.photos");
		}
		$tmpl_x->parse("corps.submit");
	}

	if (($typeaff=="form") && ((GetMyId($id)) || (GetDroit("ModifUserPassword"))))
	{
		$tmpl_x->parse("corps.modif_mdp");
	}

	$tmpl_x->parse("corps.type");

	if (GetDroit("ModifUserDroits"))
	{
		$tmpl_x->parse("corps.droits");
	}

  	if ( GetDroit("ModifUserVirtuel") )
	{
	  	$tmpl_x->parse("corps.virtuel");
	}
 
  	if ((is_numeric($id)) && ($id>0))
	{ 
		// Affiche la photo
		$lstdoc=ListDocument($sql,$id,"avatar");
		if (count($lstdoc)>0)
		{
			$doc=new document_core($lstdoc[0],$sql);
			$tmpl_x->assign("aff_avatar",$MyOpt["host"]."/".$doc->GenerePath(200,240));
		}
		else
		{
			$tmpl_x->assign("aff_avatar",$MyOpt["host"]."/".$corefolder."/static/images/none.gif");
		}	

		// Affiche les données utilisateurs
		if (count($usr->donnees)>0)
		{
			foreach($usr->donnees as $i=>$d)
			{
				$tmpl_x->assign("form_donnees",$usr->AffDonneesComp($i,$typeaff));
				$tmpl_x->parse("corps.aff_donnees.aff_donnees_util.lst_donnees");
			}
			$tmpl_x->parse("corps.aff_donnees.aff_donnees_util");
		}
	}

	// Affiche les documents

	// Devrait être VisuDocument pour afficher le bloc
	// ModifUserDocument pour passer en mode édition
	
	if ( (GetMyId($id)) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserDocument")) || (GetDroit("VisuUserDocument")) || (GetDroit("VisuDocument")) )
	{
		if (($typeaff=="form") && ((GetDroit("ModifUserDocument")) || (GetDroit("ModifUserAll"))))
		{
			$doc = new document_core(0,$sql);
			$doc->editmode="form";
			$tmpl_x->assign("form_document",$doc->Affiche());
			$tmpl_x->parse("corps.aff_documents.lst_document");
		}

		if ((is_numeric($id)) && ($id>0))
		{
			$lstdoc=ListDocument($sql,$id,"document");
		
			if (is_array($lstdoc))
			{
				foreach($lstdoc as $i=>$did)
				{
					$doc = new document_core($did,$sql);
					$doc->editmode=($typeaff=="form") ? "edit" : "std";
					$tmpl_x->assign("form_document",$doc->Affiche("large","name"));
					$tmpl_x->parse("corps.aff_documents.lst_document");
				}
			}
		}

		$tmpl_x->parse("corps.aff_documents");		
	}

	// Echéances
	if ($MyOpt["module"]["echeances"]=="on")
	{
		if ( (GetMyId($id)) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserEcheance")) || (GetDroit("VisuUserEcheance")) )
		{
			if (($typeaff=="form") && ((GetDroit("ModifUserEcheance")) || (GetDroit("ModifUserAll"))))
			{
				$dte = new echeance_core(0,$sql,$id);
				$dte->editmode="form";
				$dte->context="utilisateurs";
				$tmpl_x->assign("form_echeance",$dte->Affiche());
				$tmpl_x->parse("corps.aff_echeances.lst_echeance");
			}
				
			$lstdte=ListEcheance($sql,$id);
			if ((is_numeric($id)) && ($id>0))
			{ 
				if (is_array($lstdte))
				{
					foreach($lstdte as $i=>$did)
					{
						$dte = new echeance_core($did,$sql,$id);
						$dte->editmode=($typeaff=="form") ? "edit" : "html";
						$tmpl_x->assign("form_echeance",$dte->Affiche());
						$tmpl_x->parse("corps.aff_echeances.lst_echeance");
					}
				}
			}
			
			$tmpl_x->parse("corps.aff_echeances");			

		}
	}

// ---- Données spécifique

	if (file_exists($appfolder."/modules/membres/custom.inc.php"))
	{
		$left="";
		$right="";
		require($appfolder."/modules/membres/custom.inc.php");
		
		if ($left!="")
		{
			$tmpl_x->assign("aff_data_left",$left);
			$tmpl_x->parse("corps.aff_donnees.aff_donnees_left");
		}
		if ($right!="")
		{
			$tmpl_x->assign("aff_data_right",$right);
		}
	}

// ---- Affiche le bloc de données		
	if ( (((isset($left)) && ($left!='')) || (count($usr->donnees)>0)) && (($usr->data["aff_infos"]=="oui") || (GetMyId($id)) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserInfos")) ) )
	{
		$tmpl_x->parse("corps.aff_donnees");
	}

	if ( (($usr->data["aff_mail"]=="oui") || (GetMyId($id)) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserInfos")) ) )
	{
		$tmpl_x->parse("corps.aff_mail");
	}
	if ( (GetMyId($id)) || (GetDroit("ModifUserAll")) || (GetDroit("ModifUserInfos")) )
	{
		$tmpl_x->parse("corps.aff_infos");
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
