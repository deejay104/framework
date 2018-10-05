<?
// ---- Charge le template
  	// $tmpl_menu = new XTemplate("modules/membres/tmpl/menu.htm");
  	$tmpl_menu = LoadTemplate("menu");
	$tmpl_menu->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Sélectionne le menu courant
	if (($rub=="detail") && ($fonc=="modifier") && ($id>0))
	{
		$tmpl_menu->assign("class_".$rub,"class='pageTitleSelected'");
	}
	else if (($rub=="detail") && ($id==0))
	{
		$tmpl_menu->assign("class_add","class='pageTitleSelected'");
	}
	else if ($rub!="detail")
	{
		$tmpl_menu->assign("class_".$rub,"class='pageTitleSelected'");
	}
	$tmpl_menu->assign("id", $id);

// ---- Affiche les menus
	if ((GetMyId($id)) || (GetDroit("ModifUser")))
	  { $tmpl_menu->parse("infos.modification"); }

	if ((GetMyId($id)) || (GetDroit("ModifUserPassword")))
	  { $tmpl_menu->parse("infos.password"); }

	if (GetDroit("CreeUser"))
	  { $tmpl_menu->parse("infos.ajout"); }

	if ((GetDroit("DesactiveUser")) && ($usr->actif=="oui"))
	  { $tmpl_menu->parse("infos.desactive"); }

  	if ((GetDroit("DesactiveUser")) && ($usr->actif=="off"))
	  { $tmpl_menu->parse("infos.active"); }

	if ((GetDroit("SupprimeUser")) && ($usr->actif=="off"))
	  { $tmpl_menu->parse("infos.suppression"); }

// ---- Affiche le menu	
	$tmpl_menu->parse("infos");
	$aff_menu=$tmpl_menu->text("infos");

	// ---- Menu custom
	if (file_exists($appfolder."/modules/membres/menu.inc.php"))
	{
		require($appfolder."/modules/membres/menu.inc.php");
	}

?>