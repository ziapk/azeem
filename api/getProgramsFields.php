<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$programs = new  Programs();
$field = !empty($_GET['view']) ? $_GET['view'] : "";
if(in_array($field, ['degree', 'program', 'class'])) {
    $search = $programs->searchProgamField($field, !empty($_GET['term']) ? $_GET['term'] : "");
    echo json_encode($search);
}
else {
    echo json_encode([]);
}
?>