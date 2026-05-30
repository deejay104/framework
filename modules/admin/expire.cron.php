<?php
	if ($gl_mode!="batch")
	  { FatalError("Acces refuse","Ne peut etre execute qu'en arriere plan"); }

	myPrint("Expiration des données");

// ---- Token
	$query="DELETE FROM `".$MyOpt["tbl"]."_token` WHERE actif='non'";
	$sql->Delete($query);

// ---- Anonymise les données
// Liste les objects dont le champs anonymize est défini
// Applique pour chaque champs la règle

// - Baptème (passager, mail, tel)
// - Utilisateurs (adresse, mail, tel, FFA)


// ----
    $gl_res="OK";

?>