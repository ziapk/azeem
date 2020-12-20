<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$category = new  Categories();
$categoryData = $category->getOwnerCategories($userData['created_by']);
echo mainHeader();
?>

<div class="container">
    <a href="javascript:void(0)" onclick="createCategory()" class="btn btn-success btn-sm pull-right">Add New</a>    
    <h3 style="margin-top: 0">Customers</h3>
    <table class="table">
        <thead>
            <tr>
                <th width="100">Sr.#</th>
                <th>Name</th>
                <th width="100"></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($categoryData as $key => $cust) { ?>
            <tr>
                <td><?php echo $key + 1;?></td>
                <td><?php echo $cust['full_name'];?></td>
                <td><a onclick="deleteCategory(<?php echo $cust['id'];?>)" class="btn btn-primary btn-xs" href="javascript:void(0)">Delete</a></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
function createCategory () {
    window.open("http://localhost/tea/pages/category/create.php", "", "width=300,height=400"); 
}

function deleteCategory(id) {
    if(confirm('Are you sure ?')) {
        window.open("http://localhost/tea/pages/category/delete.php?id="+id, "", "width=300,height=400"); 
        window.location.reload();
    }
}
</script>