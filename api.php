<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json; charset=utf-8');
include_once('skosmanager.php');



if($_GET["action"] ==="load"){


        getChildren($_GET["source"],$_GET["id"]);
}
elseif ($_GET["action"] ==="info"){
    getInfo($_GET["source"],$_GET["id"]);
}

function autocomplete($source, $word){

    $labels = SKOS_Manager::getLabels($source);
    $suggestions = array();
    foreach ($labels as $label) {
        if (strpos($label, $word) !== false){
            $suggestions[]= $label;

        }


    }

    echo json_encode($suggestions);

}


function getInfo($source, $uri){
    $info = SKOS_Manager::getConcept($source,$uri);
    $data = array("uri"=>$info->toString(), "label"=>$info->label());
    echo json_encode($data);
}


function getChildren($source, $uri){
    if(isset($uri))
        $children = SKOS_Manager::getChildren($source, $uri);
    else
        $children = SKOS_Manager::getRoots($source);

    $output = array();
    foreach ($children as $child) {
        $hasChildren = SKOS_Manager::hasChildren($child);
        $outputChild = array("data"=>$child->label(), "attr"=>array("id"=>$child->toString()) );
        if($hasChildren){
            $outputChild["state"]="closed";
        }
        $output [] = $outputChild;
    }
    echo json_encode($output,JSON_UNESCAPED_UNICODE);
}


