<?php
	$q="INSERT INTO ".$MyOpt["tbl"]."_doc_docs (id_paper,id_doc,name) SELECT uid,id,originalname FROM ".$MyOpt["tbl"]."_document WHERE type='paper' AND actif='oui'";
	$sql->Insert($q);

?>

