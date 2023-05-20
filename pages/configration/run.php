<?php
include_once dirname(__FILE__) . '/../../include/settings.php';
$result = [];
if (!empty($_POST['stmt'])) {
    $queryBox = new Users();
    $query = $queryBox->runQuery($_POST['stmt']);
    $result = $query;
}
echo mainHeader();
?>
<div class="container">
    <h4>Query BOX</h4>
    <form method="POST">
        <div class="form-group">
            <textarea name="stmt" id="stmt" rows="10" class="form-control"><?php echo $_POST['stmt']; ?></textarea>
        </div>
        <p class="text-right">
            <input class="btn btn-primary" type="submit" value="Submit" />
        </p>
    </form>
    <?php if (!empty($result)) { ?>
        <h3>Summery</h3>
        <table class="table">
            <tr>
                <th>Records Count</th>
                <th class="text-danger">(<strong><?php echo $result['update']; ?></strong>)</th>
                <th>Last Inserted ID</th>
                <th class="text-danger">(<strong><?php echo $result['lastId']; ?></strong>)</th>
            </tr>
        </table>
        <h3>Records</h3>

        <div class="table-responsive">
            <table class="table">
                <?php
                $rows = $result['rows'];
                $keys = array_keys($rows[0]);
                ?>
                <tr>
                    <?php foreach ($keys as $key) { ?>
                        <th><strong><?php echo $key; ?></strong></th>
                    <?php } ?>
                </tr>
                <tbody>
                    <?php foreach ($rows as $col) { ?>
                        <tr>
                            <?php foreach ($keys as $key) { ?>
                                <td><?php echo $col[$key]; ?></td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    <?php } ?>
</div>