<?php 
include_once dirname(__FILE__).'/../include/settings.php';
$programs = new Programs();
if(!empty($_POST['program'])) {
    $programs->deleteProgramBooks(['program_id' => $_POST['program']]);
    if(!empty($_POST['books'])) {
        foreach($_POST['books'] as $book) {
            $programs->createProgramBook(['program_id' => $_POST['program'], 'product_id' => $book['id']]);
        }
    }
}
?>