<?php
?>
<li uib-dropdown auto-close="outsideClick" on-toggle="refreshList()">
  <a href="javascript:void(0)" uib-dropdown-toggle tooltip-placement="bottom" uib-tooltip="Shopping Cart"><img width="22" height="22" src="<?php echo SITE_URL; ?>assets/img/svg/010-shopping-bag-white.svg" alt="" /></a>
  <div class="dropdown-menu cart-menu" uib-dropdown-menu>
    <div class="cart-list">
      <table width="100%">
        <tr ng-repeat="li in finalList" class="cart-list-item">
          <td width="60">
            <img class="cart-list-image" src={{'<?php echo SITE_URL; ?>/uploads/products/'+li.image}} alt="" />
          </td>
          <td colspan="2">
            <span class="cart-item-title">{{li.full_name}}<span>
                <div class="cart-item-controls">
                  <a class="decrease-value" ng-click="decreaseValue(li); $event.stopPropagation()">
                    <span class="glyphicon glyphicon-menu-left"></span>
                  </a>
                  <span class="qty">{{li.qty}}</span>
                  <a href="javascript:void(0);" class="increase-value" ng-click="increaseValue(li); $event.stopPropagation()">
                    <span class="glyphicon glyphicon-menu-right"></span>
                  </a>
                </div>
          </td>
          <td class="cart-right">
            <del ng-if="li.discount" class="text-danger">{{li.qty * li.price}}</del>
            <span class="text-success">{{li.qty * (li.price - li.discount)}}</span>
          </td>
        </tr>
        <tr class="cart-total-row" ng-if="finalList.length">
          <td colspan="2" style="text-align: left">
            <a href="<?php echo SITE_URL; ?>pages/recipt" class="btn btn-success btn-xs">Checkout</a>
            <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="clear()">Clear</a>
          </td>
          <td>
            Total
          </td>
          <td class="cart-right text-success">
            &nbsp;&nbsp;{{totalPrice}}
          </td>
        </tr>
        <tr ng-if="!finalList.length">
          <th class="text-center">
            <h3 style="margin-bottom: 20px">NOTHING IN CART </h3> <a class="btn btn-primary" href="<?php echo SITE_URL; ?>pages/product">Add Now</a>
          </th>
        </tr>
      </table>
    </div>
  </div>
</li>
<script>
  // Create the event
  var event = new CustomEvent("ProdcutAdded");

  app.controller('headerController', function($scope, $http, $httpParamSerializerJQLike, $filter, $window, toaster) {
    $scope.fontsize = 13;

    $('html').css('font-size', $scope.fontsize + 'px');

    $scope.updateFont = size => {
      $('html').css('font-size', size + 'px');
    }

    // $scope.list = <?php echo safe_json_encode($list); ?>;
    // sessionStorage.setItem('list', JSON.stringify($scope.list));
    $scope.exp = {};
    $scope.supplier = {};
    $scope.customer = {};
    $scope.payment = {
      id: null
    }
    $scope.customersList = <?php echo json_encode($customersList); ?>;
    $scope.ocustomersList = <?php echo json_encode($customersList); ?>;
    $scope.expensesList = <?php echo json_encode($categories); ?>;
    $scope.oexpensesList = <?php echo json_encode($categories); ?>;
    $scope.suppliersList = <?php echo json_encode($suppliersList); ?>;
    $scope.osuppliersList = <?php echo json_encode($suppliersList); ?>;
    $scope.authorsList = <?php echo json_encode($authorsList); ?>;
    $scope.oauthorsList = <?php echo json_encode($authorsList); ?>;
    $scope.createExpense = () => {
      const cat_id = $scope.exp.expense.id;
      if (cat_id && $scope.exp.price) {
        $http.post('<?php echo SITE_URL; ?>api/createExpense.php', $httpParamSerializerJQLike({
          ...$scope.exp,
          cat_id
        }), {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        }).then((response) => {
          alert(response.data.message);
          if (response.data.status == 200) {
            $scope.exp.expense = '';
            $scope.exp.description = '';
            $scope.exp.price = '';
          }
        })
      }
    }

    $scope.refreshCustomers = search => {
      $scope.customersList = $scope.ocustomersList.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
    }
    $scope.refreshExpenses = search => {
      $scope.expensesList = $scope.oexpensesList.filter(r => r.full_name.toLowerCase().includes(search.toLowerCase()));
    }
    $scope.refreshSuppliers = search => {
      $scope.suppliersList = $scope.osuppliersList.filter(r => r.name.toLowerCase().includes(search.toLowerCase()));
    }
    $scope.refreshAuthors = search => {
      $scope.authorsList = $scope.oauthorsList.filter(r => r.name.toLowerCase().includes(search.toLowerCase()));
    }
    $scope.directPayment = (type) => {
      const id = $scope.payment.supplier.account_id;
      $scope.payment.supplier.account_id;
      if (id && $scope.payment.amount) {
        $http.post('<?php echo SITE_URL; ?>api/directPayment.php', $httpParamSerializerJQLike({
          ...$scope.payment,
          type,
          id,
        }), {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        }).then((response) => {
          alert(response.data.message);
          if (response.data.status == 200) {
            $scope.payment.summery = '';
            $scope.payment.amount = '';
            $scope.printRecipt(response.data.supply.id);
          }
        })
      }
    }
    $scope.directReceiving = () => {
      const id = $scope.payment.customer.account_id;
      if (id && $scope.payment.amount) {
        $http.post('<?php echo SITE_URL; ?>api/directReceiving.php', $httpParamSerializerJQLike({
          ...$scope.payment,
          id
        }), {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        }).then((response) => {
          alert(response.data.message);
          if (response.data.status == 200) {
            $scope.printRecipt(response.data.supply.id);
            $scope.payment.customer = '';
            $scope.payment.summery = '';
            $scope.payment.amount = '';
          }


        })
      }
    }
    $scope.printRecipt = (id, detail, largeView) => {
      if (detail) {
        detail = true
      } else {
        detail = false
      }
      $window.open("<?php echo SITE_URL; ?>print/receiving.php?id=" + id + "&detail=" + detail + '&largeView=' + largeView, "", "width=600,height=900");
    }
    $scope.refreshList = function() {
      $scope.cart = JSON.parse($window.sessionStorage.getItem('shopping'));
      $scope.totalPrice = 0;
      $scope.finalList = [];
      $scope.cart.map(row => {
        const obj = window.mainList.records.find(r => r.id === row.id);
        $scope.finalList.push({
          ...obj,
          price: row.price || obj.price,
          discount: row.discount,
          qty: row.qty
        })
        $scope.totalPrice += parseFloat(row.qty) * (parseFloat(row.price || obj.price || 0) - parseFloat(row.discount || 0))
      })
      document.dispatchEvent(event);
    }

    $scope.clear = () => {
      $scope.cart = $window.sessionStorage.setItem('shopping', JSON.stringify([]));
      $scope.totalPrice = 0;
      $scope.finalList = [];
    }

    $scope.addToCart = function(item, type) {
      if ($window.sessionStorage.getItem('shopping')) {
        const shopCart = JSON.parse($window.sessionStorage.getItem('shopping'));
        let found = false;
        shopCart.map(row => {
          if (type && type == 'list') {
            item.map(l => {
              if (row.id == l.id) {
                const rr = {
                  ...l,
                  ...row
                };
                found = true
                rr.qty++;
                return rr
              }
            })
          } else {
            if (row.id == item.id) {
              const rr = {
                ...item,
                ...row
              };
              found = true
              rr.qty++;
              row.qty++;
              return rr
            }
          }
        });
        if (!found) {
          $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart, ...(type && type == 'list' ? item.map(r => ({
            ...r,
            qty: 1
          })) : [{
            ...item,
            id: item.id,
            qty: 1
          }])]));
        } else {
          $window.sessionStorage.setItem('shopping', JSON.stringify([...shopCart]))
        }
      } else {
        $window.sessionStorage.setItem('shopping', JSON.stringify(type && type == 'list' ? item.map(r => ({
          ...r,
          qty: 1
        })) : [{
          ...item,
          id: item.id,
          qty: 1
        }]))
      }


      // Dispatch/Trigger/Fire the event
      document.dispatchEvent(event);

      toaster.success({
        body: 'Product added to Cart successfully!'
      });
    }

    $scope.toggleSidebar = () => {
      $('body').toggleClass('thumb-menu-screen')
    }

    $scope.increaseValue = row => {
      const cart = JSON.parse($window.sessionStorage.getItem('shopping'))
      cart.map(r => {
        if (row.id == r.id) {
          r.qty++
        }
      })
      $window.sessionStorage.setItem('shopping', JSON.stringify(cart));
      $scope.refreshList();
    }

    $scope.decreaseValue = (row) => {
      const cart = JSON.parse($window.sessionStorage.getItem('shopping'))

      cart.map(r => {
        if (row.id == r.id) {
          r.qty > 1 ? r.qty-- : r.qty
        }
      })
      $window.sessionStorage.setItem('shopping', JSON.stringify(cart));
      $scope.refreshList()

    }
    $scope.applyClosing = () => {
      if ($window.confirm('Are you sure you want to close to sale for Today')) {
        $http.post('<?php echo SITE_URL; ?>api/closing.php', $httpParamSerializerJQLike({
          sale_date: 'next'
        }), {
          headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
          }
        }).then((response) => {
          $window.location.reload();
        })
      }
    }

    $scope.getClass = () => {
      return new Date().getHours() >= 20 && new Date().getHours() <= 22
    }
  });
</script>