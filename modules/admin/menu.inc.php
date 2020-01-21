<?php
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }
	
// ---- Affiche les menus

	if (GetDroit("AccesConfigVar"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_configuration"],geturl("admin","config",""),"icn32_config.png",($rub=="config") ? true : false);
	}

	if (GetDroit("AccesConfigBase"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_database"],geturl("admin","base",""),"icn32_database.png",($rub=="base") ? true : false);
	}

	if (GetDroit("AccesConfigGroupes"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_groups"],geturl("admin","groupes",""),"icn32_groupes.png",($rub=="groupes") ? true : false);
	}

	if ((GetDroit("AccesConfigEcheances")) && ($MyOpt["module"]["echeances"]=="on"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_deadline"],geturl("admin","echeances",""),"icn32_echeances.png",($rub=="echeances") ? true : false);
	}

	if (GetDroit("AccesConfigDonneesUser"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_userdata"],geturl("admin","utildonnees",""),"icn32_param.png",($rub=="utildonnees") ? true : false);
	}

	if (GetDroit("AccesConfigCrontab"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_crontab"],geturl("admin","crontab",""),"icn32_cron.png",($rub=="crontab") ? true : false);
	}	

	if (GetDroit("AccesConfigEmails"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_emails"],geturl("admin","emails",""),"icn32_emails.png",($rub=="emails") ? true : false);
	}

// ---- Menu custom
	if (file_exists($appfolder."/modules/admin/menu.inc.php"))
	{
		require($appfolder."/modules/admin/menu.inc.php");
	}

	
?>