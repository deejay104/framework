<?
	if (GetDroit("AccesMembres"))
	{
	  	$tmpl_prg->parse("main.menu_membres"); 
	  	$tmpl_prg->parse("main.menu_membres_sm"); 
	}
	if (GetDroit("AccesMembre"))
	{
	  	$tmpl_prg->parse("main.menu_mesinfos"); 
	  	$tmpl_prg->parse("main.menu_mesinfos_sm"); 
	}
	if (GetDroit("AccesDocuments"))
	{
	  	$tmpl_prg->parse("main.menu_docs"); 
	  	$tmpl_prg->parse("main.menu_docs_sm"); 
	}
	if (GetDroit("AccesConfiguration"))
	{
	  	$tmpl_prg->parse("main.menu_configuration"); 
	  	$tmpl_prg->parse("main.menu_configuration_sm"); 
	}
	if (GetDroit("AccesAmeliorations"))
	{
	  	$tmpl_prg->parse("main.menu_problemes"); 
	  	$tmpl_prg->parse("main.menu_problemes_sm"); 
	}
?>