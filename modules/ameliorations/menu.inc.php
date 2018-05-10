<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

// ---- Charge le template
  	$tmpl_menu = new XTemplate (MyRep("menu.htm"));
	$tmpl_menu->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Sélectionne le menu courant
	$tmpl_menu->assign("class_".$rub,"class='pageTitleSelected'");
	
// ---- Affiche les menus
	$tmpl_menu->assign("form_id",$id);

	if (GetDroit("CreeAmelioration"))
	{
		$tmpl_menu->parse("infos.ajouter");
	}
	if ((GetDroit("ModifAmelioration")) && ($id>0))
	{
		$tmpl_menu->parse("infos.modifier");
	}	
	if ((GetDroit("SupprimeAmelioration")) && ($id>0))
	{
		$tmpl_menu->parse("infos.supprimer");
	}

	
	
	$tmpl_menu->parse("infos");
	$aff_menu=$tmpl_menu->text("infos");
	
?>