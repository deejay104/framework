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
	if (GetDroit("AccesConfigVar"))
	{
		$tmpl_menu->parse("infos.config");
	}
	if (GetDroit("AccesConfigBase"))
	{
		$tmpl_menu->parse("infos.base");
	}
	if (GetDroit("AccesConfigGroupes"))
	{
		$tmpl_menu->parse("infos.groupes");
	}
	if (GetDroit("AccesConfigDonneesUser"))
	{
		$tmpl_menu->parse("infos.utildonnees");
	}
	if (GetDroit("AccesConfigCrontab"))
	{
		$tmpl_menu->parse("infos.crontab");
	}

	$tmpl_menu->parse("infos");
	$aff_menu=$tmpl_menu->text("infos");
	
?>