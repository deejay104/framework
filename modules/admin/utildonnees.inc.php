<?
// ---- Charge le template
	$tmpl_x = LoadTemplate("utildonnees");
	$tmpl_x->assign("path_module",$corefolder."/".$module."/".$mod);

// ---- Vérifie le droit d'accès
	if (!GetDroit("AccesConfigDonneesUser")) { FatalError($tabLang["lang_accessdenied"]." (AccesConfigDonneesUser)"); }

// ---- Affiche le menu
	$aff_menu="";
	require_once("modules/".$mod."/menu.inc.php");
	$tmpl_x->assign("aff_menu",$aff_menu);

// ---- Variables
	$id=checkVar("id","numeric");
	$form_nom=checkVar("form_nom","array");
	$form_type=checkVar("form_type","array");
	
// ---- Sauvegarde
	if (($fonc==$tabLang["lang_save"]) && (GetDroit("ModifUtilDonnees")))
	{
		if (is_array($form_nom))
		{
			foreach($form_nom as $id=>$n)
			{
				if ($n!="")
				{
					$sql->Edit("user",$MyOpt["tbl"]."_utildonneesdef",$id,array("nom"=>$n,"type"=>$form_type[$id]));
				}
			}
		}
	}
	
// ---- Supprime
	if (($fonc=="delete") && (GetDroit("SupprimeUtilDonnees")))
	{
		if ($id>0)
		{
			$sql->Edit("user",$MyOpt["tbl"]."_utildonneesdef",$id,array("actif"=>"non"));			
		}
	}

// ---- Charge les définitions
	$query="SELECT * FROM ".$MyOpt["tbl"]."_utildonneesdef WHERE actif='oui' ORDER BY ordre";
	$sql->Query($query);

	for($i=0; $i<$sql->rows; $i++)
	{
		$sql->GetRow($i);
		$tmpl_x->assign("form_id",$sql->data["id"]);
		$tmpl_x->assign("form_ordre",$sql->data["ordre"]);
		$tmpl_x->assign("form_nom",$sql->data["nom"]);
		$tmpl_x->assign("select_type_".$sql->data["type"],"selected");
		$tmpl_x->parse("corps.lst_donnees");
		$tmpl_x->parse("corps.lst_sort");
	}
	
// ---- Affecte les variables d'affichage
	$tmpl_x->parse("icone");
	$icone=$tmpl_x->text("icone");
	$tmpl_x->parse("infos");
	$infos=$tmpl_x->text("infos");
	$tmpl_x->parse("corps");
	$corps=$tmpl_x->text("corps");

	
?>