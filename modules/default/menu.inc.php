<?
// ---- Desktop
	$tabMenu["membres"]["icone"]=$corefolder."/static/modules/membres/img/icn32_membres.png";
	$tabMenu["membres"]["nom"]="Membres";
	$tabMenu["membres"]["droit"]="AccesMembres";
	$tabMenu["membres"]["url"]="mod=membres";

	$tabMenu["mesinfos"]["icone"]=$corefolder."/static/modules/membres/img/icn32_detail.png";
	$tabMenu["mesinfos"]["nom"]="Mes informations";
	$tabMenu["mesinfos"]["droit"]="AccesMesInformations";
	$tabMenu["mesinfos"]["url"]="mod=membres&rub=detail&id=".$gl_uid;

	if ($MyOpt["module"]["documents"]=="on")
	{
		$tabMenu["docs"]["icone"]=$corefolder."/static/modules/docs/img/icn32_titre.png";
		$tabMenu["docs"]["nom"]="Documents";
		$tabMenu["docs"]["droit"]="AccesDocuments";
		$tabMenu["docs"]["url"]="mod=docs";
	}
	
// ---- Phone
	$tabMenuPhone["membres"]["icone"]=$corefolder."/static/modules/membres/img/icn48_membres.png";
	$tabMenuPhone["membres"]["nom"]="";
	$tabMenuPhone["membres"]["droit"]="AccesMembres";
	$tabMenuPhone["membres"]["url"]="mod=membres";

	if ($MyOpt["module"]["documents"]=="on")
	{
		$tabMenuPhone["docs"]["icone"]=$corefolder."/static/modules/docs/img/icn48_titre.png";
		$tabMenuPhone["docs"]["nom"]="";
		$tabMenuPhone["docs"]["droit"]="AccesDocuments";
		$tabMenuPhone["docs"]["url"]="mod=docs";
	}
?>