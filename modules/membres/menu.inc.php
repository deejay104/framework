<?php
// ---- Affiche les menus
	addPageMenu($corefolder,$mod,$tabLang["lang_list"],geturl("membres","",""),"mdi-backburger");

	// if (GetDroit("CreeUser"))
	// {
		// $sel=false;
		// if (($rub=="detail") && ($id==0))
		// {
			// $sel=true;
		// }
		// addPageMenu($corefolder,$mod,$tabLang["lang_add"],geturl("membres","detail","id=0"),"icn32_ajouter.png",$sel);
	// }
	if ((GetMyId($id)) || (GetDroit("ModifUser")))
	{ 
		$sel=false;
		if (($rub=="detail") && ($fonc=="modifier") && ($id>0))
		{
			$sel=true;
		}

		addPageMenu($corefolder,$mod,$tabLang["lang_modify"],geturl("membres","detail","fonc=modifier&id=".$id),"",$sel);
	}
	if ((GetMyId($id)) || (GetDroit("ModifUserPassword")))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_changepwd"],geturl("membres","chgpwd","id=".$id),"",($rub=="chgpwd") ? true : false);
	}

	// Désactive
	if ((GetDroit("DesactiveUser")) && ($usr->actif=="oui"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_disable"],geturl("membres","detail","id=".$id."&fonc=desactive"),"");
	}
	// Active
  	if ((GetDroit("DesactiveUser")) && ($usr->actif=="off"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_enable"],geturl("membres","detail","id=".$id."&fonc=active"),"");
	}
	// Supprime
	if ((GetDroit("SupprimeUser")) && ($usr->actif=="off"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_delete"],geturl("membres","detail","id=".$id."&fonc=delete"),"");
	}
	if ((GetDroit("SupprimeUser")) && ($usr->actif=="non"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_enable"],geturl("membres","detail","id=".$id."&fonc=active"),"");
	}

// ---- Menu custom
	if (file_exists($appfolder."/modules/membres/menu.inc.php"))
	{
		require($appfolder."/modules/membres/menu.inc.php");
	}

?>