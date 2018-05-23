<?
if ($MyOpt["module"]["actualites"]=="on")
{
	$mod="actualites";
	$affrub="index";
}
else if (GetDroit("AccesMembres"))
{
	$mod="membres";
	$affrub="index";
}
else
{
	$mod="membres";
	$affrub="detail";
	$id=$gl_uid;
}

?>