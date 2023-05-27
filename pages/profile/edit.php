<?php

include_once dirname(__FILE__) . '/../../include/settings.php';
echo mainHeader(['page' => 'profile']);
$id = $_GET['id'];
$users = new Users();
$userSelected = $users->getUserSelected($id);
$stores = new Store();
$ownerStores = $stores->getStores();

?>
<div class="container" ng-controller="profileController">
    <div class="alert alert-success" ng-if="message">{{message}}</div>
    <div class="profile-section">
        <form class="form-vertical" ng-submit="updateProfile()">
            <div class="row">
                <div class="col-sm-4 text-center">
                    <div class="profile-img-section">
                        <img src="<?php echo SITE_URL; ?>assets/img/avatar/{{form.photo || 'avatar1.jpg'}}" alt="" />
                    </div>
                    <a href="#" class="btn btn-primary" ng-click="changeAvatar()">Change Avatar</a>
                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <input type="hidden" name="photo" ng-model="form.photo">
                        <label for="full_name" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Full Name</label>
                        <input type="text" class="form-control" id="full_name" ng-model="form.full_name" placeholder="Full Name">
                    </div>
                    <div class="form-group">
                        <label for="email" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Username</label>
                        <div class="form-control">{{form.email}}</div>
                    </div>
                    <div class="form-group">
                        <label for="city" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">City</label>
                        <input type="text" class="form-control" id="city" placeholder="City" ng-model="form.city">
                    </div>
                    <div class="form-group">
                        <label for="designation" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Designation</label>
                        <div class="form-control">{{form.designation}}</div>
                    </div>
                    <div class="form-group">
                        <label for="cnic" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">CNIC</label>
                        <input type="text" class="form-control" id="cnic" placeholder="CNIC" ng-model="form.cnic">
                    </div>
                    <div class="form-group">
                        <label for="role" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Role</label>
                        <input type="text" class="form-control" id="role" placeholder="role" ng-model="form.role">
                    </div>
                    <div class="form-group">
                        <label for="shopId" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">shopId</label>
                        <select type="text" class="form-control" id="shopId" placeholder="shopId" ng-model="form.shopId">
                            <?php foreach ($ownerStores as $key => $value) { ?>
                                <option value="<?php echo $value['id']; ?>"><?php echo $value['full_name']; ?></option>
                            <?php } ?>
                        </select>
                    </div>

                </div>
                <div class="col-sm-4">
                    <div class="form-group">
                        <label for="cnic" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Date of Joining</label>
                        <div class="form-control">{{form.doj}}</div>
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber1" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Mobile Number</label>
                        <input type="text" class="form-control" id="phoneNumber1" placeholder="Phone Number" ng-model="form.phoneNumber1">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber2" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">Contact Number</label>
                        <input type="text" class="form-control" id="phoneNumber2" placeholder="Phone Number" ng-model="form.phoneNumber2">
                    </div>
                    <div class="form-group">
                        <label for="phoneNumber3" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">EMG. Number</label>
                        <input type="text" class="form-control" id="phoneNumber3" placeholder="Phone Number" ng-model="form.phoneNumber3">
                    </div>
                    <div class="form-group">
                        <label for="created_by" class="control-label" style="font-size: 0.7em; font-weight: bold; letter-spacing: 1px">created_by</label>
                        <input type="text" class="form-control" id="created_by" placeholder="created_by" ng-model="form.created_by">
                    </div>
                </div>
            </div>
            <div class="form-group text-right">
                <button type="submit" class="btn btn-primary"><span class="fa fa-save"></span> Update</button>
            </div>
        </form>
    </div>
</div>
<script>
    app.controller("profileController", function($scope, $http, $uibModal, $httpParamSerializerJQLike) {
        $scope.form = <?php echo json_encode($userSelected); ?>;
        $scope.updateProfile = function() {
            $scope.message = "";
            $http.post("<?php echo SITE_URL ?>api/updateProfile.php", $httpParamSerializerJQLike($scope.form), {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(function(response) {
                    $scope.message = response.data.message;
                })
        };

        $scope.message = "";

        $scope.changeAvatar = function(size, parentSelector) {

            $uibModal.open({
                ariaLabelledBy: 'modal-title',
                ariaDescribedBy: 'modal-body',
                templateUrl: 'changeAvatar.html',
                controller: 'ModalInstanceCtrl',
                size: size,
                resolve: {
                    avatar: function() {
                        return $scope.form.photo || 'avatar1.jpg'
                    }
                }
            }).result.then(function(selectedItem) {
                $scope.form.photo = selectedItem;
            });
        };

    });

    app.controller('ModalInstanceCtrl', function($scope, $uibModalInstance, $http, $httpParamSerializerJQLike, avatar) {
        $scope.avatar = avatar;

        console.log(avatar)

        $scope.ok = function() {
            $uibModalInstance.close($scope.avatar);
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss('cancel');
        };
    });
</script>
<script type="text/ng-template" id="changeAvatar.html">
    <form ng-submit="ok()"> 
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title">Change Avatar</h3>
        </div>
        <div class="modal-body" id="modal-body">
            <div class="row">
                <div class="col-sm-3 col-xs-12 form-group text-center">
                    <label>
                        <img class="img-responsive" src="<?php echo SITE_URL; ?>assets/img/avatar/avatar1.jpg" alt="">
                        <input type="radio" value="avatar1.jpg" name="avatar" ng-model="avatar">
                    </label>
                </div>
                <div class="col-sm-3 col-xs-12 form-group text-center">
                    <label>
                        <img class="img-responsive" src="<?php echo SITE_URL; ?>assets/img/avatar/avatar2.jpg" alt="">
                        <input type="radio" value="avatar2.jpg" name="avatar" ng-model="avatar">
                    </label>
                </div>
                <div class="col-sm-3 col-xs-12 form-group text-center">
                    <label>
                        <img class="img-responsive" src="<?php echo SITE_URL; ?>assets/img/avatar/avatar3.jpg" alt="">
                        <input type="radio" value="avatar3.jpg" name="avatar" ng-model="avatar">
                    </label>
                </div>
                <div class="col-sm-3 col-xs-12 form-group text-center">
                    <label>
                        <img class="img-responsive" src="<?php echo SITE_URL; ?>assets/img/avatar/avatar4.jpg" alt="">
                        <input type="radio" value="avatar4.jpg" name="avatar" ng-model="avatar">
                    </label>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-default" type="button" ng-click="cancel()">Close</button>
            <button class="btn btn-primary" type="submit">Submit Form</button>
        </div>
    </form>
</script>

<?php echo mainFooter();
