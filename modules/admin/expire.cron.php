<?php
	if ($gl_mode!="batch")
	  { FatalError("Acces refuse","Ne peut etre execute qu'en arriere plan"); }

	myPrint("Expiration des données");

// ---- Token
	$query="DELETE FROM `".$MyOpt["tbl"]."_token` WHERE actif='non' OR dte_expire<'".now()."'";
	$sql->Delete($query);

	$query="DELETE FROM `".$MyOpt["tbl"]."_token_post` WHERE actif='non' OR dte_expire<'".now()."'";
	$sql->Delete($query);

	$query="DELETE FROM `".$MyOpt["tbl"]."_token_resetpwd` WHERE actif='non' OR dte_expire<'".now()."'";
	$sql->Delete($query);

// ---- Historique
	$query="DELETE FROM `".$MyOpt["tbl"]."_historique` WHERE dte_maj<'".date('Y-m-d', strtotime('-3 years'))."' OR dte_creat<'".date('Y-m-d', strtotime('-3 years'))."'";
	$sql->Delete($query);

// ---- Historique
	$query="DELETE FROM `".$MyOpt["tbl"]."_login` WHERE dte_maj<'".date('Y-m-d', strtotime('-3 years'))."'";
	$sql->Delete($query);

// ---- Anonymise les données
// Liste les objects dont le champs anonymize est défini
// Applique pour chaque champs la règle

// - Baptème (passager, mail, tel)
// - Utilisateurs (adresse, mail, tel, FFA)


// ----
    $gl_res="OK";

?>