<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
if (!empty($_POST) && $userData['role'] === 'owner') {
    $queryBox = new Users();
    $query = $queryBox->runQuery($_POST['stmt']);
    var_dump($query);
    exit;
}
echo mainHeader();
?>
<div class="container">
    <h4>Query BOX</h4>
    <form method="POST">
        <label for="stmt">Prepare Query</label>
        <textarea name="stmt" id="stmt" rows="10" class="form-control"></textarea>
        <input type="submit" value="Submit" />
    </form>
</div>