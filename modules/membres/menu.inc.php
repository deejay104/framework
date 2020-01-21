<?php
// ---- Affiche les menus
	addPageMenu($corefolder,$mod,$tabLang["lang_list"],geturl("membres","",""),"icn32_retour.png");

	if (GetDroit("CreeUser"))
	{
		$sel=false;
		if (($rub=="detail") && ($id==0))
		{
			$sel=true;
		}
		addPageMenu($corefolder,$mod,$tabLang["lang_add"],geturl("membres","detail","id=0"),"icn32_ajouter.png",$sel);
	}
	if ((GetMyId($id)) || (GetDroit("ModifUser")))
	{ 
		$sel=false;
		if (($rub=="detail") && ($fonc=="modifier") && ($id>0))
		{
			$sel=true;
		}

		addPageMenu($corefolder,$mod,$tabLang["lang_modify"],geturl("membres","detail","fonc=modifier&id=".$id),"icn32_editer.png",$sel);
	}
	if ((GetMyId($id)) || (GetDroit("ModifUserPassword")))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_changepwd"],geturl("membres","chgpwd","id=".$id),"icn32_password.png",($rub=="chgpwd") ? true : false);
	}

	// Désactive
	if ((GetDroit("DesactiveUser")) && ($usr->actif=="oui"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_disable"],geturl("membres","detail","id=".$id."&fonc=desactive"),"icn32_desactive.png");
	}
	// Active
  	if ((GetDroit("DesactiveUser")) && ($usr->actif=="off"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_enable"],geturl("membres","detail","id=".$id."&fonc=active"),"icn32_desactive.png");
	}
	// Supprime
	if ((GetDroit("SupprimeUser")) && ($usr->actif=="off"))
	{
		addPageMenu($corefolder,$mod,$tabLang["lang_delete"],geturl("membres","detail","id=".$id."&fonc=delete"),"icn32_supprime.png");
	}

// ---- Menu custom
	if (file_exists($appfolder."/modules/membres/menu.inc.php"))
	{
		require($appfolder."/modules/membres/menu.inc.php");
	}

?>