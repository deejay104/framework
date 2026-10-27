<?php

$query="SELECT value FROM ".$MyOpt["tbl"]."_config WHERE param='version'";
$res=$sql->QueryRow($query);

$status=false;
$version="0";

if (isset($res["value"]))
{
    $status=true;
    $version=$res["value"];
}

$result=array(
    "status"=>$status,
    "version"=>$version,
    "maintenance"=>($MyOpt["maintenance"]=="on") ? true : false,
);

echo json_encode($result);

?>