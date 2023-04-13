<?
// ---- Desktop
	$tabMenu["membres"]["icone"]="mdi-account-multiple";
	$tabMenu["membres"]["nom"]=$tabLang["core_membres"];
	$tabMenu["membres"]["droit"]="AccesMembres";
	$tabMenu["membres"]["url"]=geturl("membres","index","");

	if ($MyOpt["module"]["documents"]=="on")
	{
		$tabMenu["docs"]["icone"]="mdi-file-document";
		$tabMenu["docs"]["nom"]=$tabLang["core_documents"];
		$tabMenu["docs"]["droit"]="AccesDocuments";
		$tabMenu["docs"]["url"]=geturl("docs","index","");
	}
	
// ---- Phone
	$tabMenuPhone["membres"]["icone"]="mdi-account-multiple";
	$tabMenuPhone["membres"]["nom"]="";
	$tabMenuPhone["membres"]["droit"]="AccesMembres";
	$tabMenuPhone["membres"]["url"]=geturl("membres","","");

	if ($MyOpt["module"]["documents"]=="on")
	{
		$tabMenuPhone["docs"]["icone"]="mdi-file-document";
		$tabMenuPhone["docs"]["nom"]="";
		$tabMenuPhone["docs"]["droit"]="AccesDocuments";
		$tabMenuPhone["docs"]["url"]=geturl("docs","","");
	}



?>