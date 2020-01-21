<?
// ---- Refuse l'accès en direct
	if ((!isset($token)) || ($token==""))
	  { header("HTTP/1.0 401 Unauthorized"); exit; }

	if ($rub=="detail")
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_list"],geturl("ameliorations","",""),"icn32_retour.png");
	}
// ---- Affiche les menus
	if (GetDroit("CreeAmelioration"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_new"],geturl("ameliorations","detail","id=0"),"icn32_ajouter.png");
	}
	if ((GetDroit("ModifAmelioration")) && ($id>0))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_modify"],geturl("ameliorations","detail","fonc=modifier&id=".$id),"icn32_modifier.png");
	}	
	if ((GetDroit("SupprimeAmelioration")) && ($id>0))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_delete"],geturl("ameliorations","detail","fonc=supprimer&id=".$id),"icn32_supprimer.png");
	}

		
?>