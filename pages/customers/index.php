<?php 
include_once dirname(__FILE__).'/../../include/settings.php';
$customers = new  Customers();
$customersData = $customers->getCustomers($userData['shopId']);
echo mainHeader();
?>

<div class="container">
    <a href="javascript:void(0)" onclick="createCustomer()" class="btn btn-success btn-sm pull-right">Add New</a>    
    <h3 style="margin-top: 0">Customers</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Sr.#</th>
                <th>Id</th>
                <th>Name</th>
                <th>Phone Number</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($customersData as $key => $cust) { ?>
            <tr>
                <td><?php echo $key + 1;?></td>
                <td><?php echo $cust['id'];?></td>
                <td><?php echo $cust['full_name'];?></td>
                <td><?php echo $cust['phoneNumber'];?></td>
                <td><a onclick="deleteCustomer(<?php echo $cust['id'];?>)" class="btn btn-primary btn-xs" href="javascript:void(0)">Delete</a></td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>
<?php
echo mainFooter();
?>
<script type="text/javascript">
function createCustomer () {
    window.open("http://localhost/tea/pages/customers/create.php", "", "width=300,height=400"); 
}

function deleteCustomer(id) {
    if(confirm('Are you sure ?')) {
        window.open("http://localhost/tea/pages/customers/delete.php?id="+id, "", "width=300,height=400"); 
        window.location.reload();
    }
}
</script>