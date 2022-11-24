<?
// ---- Desktop
	$tabMenu["membres"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/membres/img/icn32_membres.png";
	$tabMenu["membres"]["icone"]="mdi-account-multiple";
	$tabMenu["membres"]["nom"]=$tabLang["core_membres"];
	$tabMenu["membres"]["droit"]="AccesMembres";
	$tabMenu["membres"]["url"]=geturl("membres","","");

	// $tabMenu["mesinfos"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/membres/img/icn32_detail.png";
	// $tabMenu["mesinfos"]["nom"]=$tabLang["core_myinfos"];
	// $tabMenu["mesinfos"]["droit"]="AccesMesInformations";
	// $tabMenu["mesinfos"]["url"]=geturl("membres","detail","id=".$gl_uid);

	if ($MyOpt["module"]["documents"]=="on")
	{
		$tabMenu["docs"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/docs/img/icn32_titre.png";
		$tabMenu["docs"]["icone"]="mdi-file-document";
		$tabMenu["docs"]["nom"]=$tabLang["core_documents"];
		$tabMenu["docs"]["droit"]="AccesDocuments";
		$tabMenu["docs"]["url"]=geturl("docs","","");
	}
	
// ---- Phone
	$tabMenuPhone["membres"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/membres/img/icn48_membres.png";
	$tabMenuPhone["membres"]["icone"]="mdi-account-multiple";
	$tabMenuPhone["membres"]["nom"]="";
	$tabMenuPhone["membres"]["droit"]="AccesMembres";
	$tabMenuPhone["membres"]["url"]=geturl("membres","","");

	if ($MyOpt["module"]["documents"]=="on")
	{
		$tabMenuPhone["docs"]["icone"]=$MyOpt["host"]."/".$corefolder."/static/modules/docs/img/icn48_titre.png";
		$tabMenuPhone["docs"]["icone"]="mdi-file-document";
		$tabMenuPhone["docs"]["nom"]="";
		$tabMenuPhone["docs"]["droit"]="AccesDocuments";
		$tabMenuPhone["docs"]["url"]=geturl("docs","","");
	}



?>