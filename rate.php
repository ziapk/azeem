<?php

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Calculator</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            text-align: center;
        }

        table {
            max-width: 620px;
            margin: auto;
        }

        th {
            background: #000;
            color: #fff;
            padding: 5px;
        }

        .total-row th {
            background: red;
        }

        table,
        td,
        th,
        table {
            border: 1px solid #ccc
        }

        td {
            padding: 0;
            text-align: center;
        }

        h2 {
            color: green
        }

        input {
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            font-size: 18px;
            height: 42px;
            border: 0;
        }
    </style>
</head>

<body ng-app="mainApp" ng-controller="calculator">
    <form>
        <h2>FOR PAPER PRICE</h2>
        <table cellspacing="0" cellpadding="0" width="100%" border="1">
            <tr>
                <th>R.SIZE</th>
                <th>CUTTING</th>
                <th>Gram</th>
            </tr>
            <tr>
                <td><input ng-change="calculatePrice()" ng-model="form.size" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.cutting" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.gram" type="number" /></td>
            </tr>
            <tr>
                <th colspan="2">RATE</th>
                <th>PAPER</th>
            </tr>
            <tr>
                <td colspan="2"><input ng-change="calculatePrice()" ng-model="form.rate" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.paper" type="number" /></td>
            </tr>
            <tr class="total-row">
                <th style="border: 0">Price</th>
                <th style="border: 0" colspan="4"><input ng-model="form.price" type="number" /></th>
            </tr>
        </table>
        <h2>SHEET PRICE</h2>
        <table cellspacing="0" cellpadding="0" width="100%" border="1">
            <tr>
                <th>R.RATE.1</th>
                <th>N.SHEET</th>
            </tr>
            <tr>
                <td><input ng-change="calculatePrice()" ng-model="form.sprate_1" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.spsheet_1" type="number" /></td>
            </tr>
            <tr class="total-row" class="total-row">
                <th style="border: 0" colspan="2"><input ng-model="form.spprice_1" type="number" /></th>
            </tr>
            <tr>
                <th>R.RATE.2</th>
                <th>N.SHEET</th>
            </tr>
            <tr>
                <td><input ng-change="calculatePrice()" ng-model="form.sprate_2" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.spsheet_2" type="number" /></td>
            </tr>
            <tr class="total-row">
                <th style="border: 0" colspan="2"><input ng-model="form.spprice_2" type="number" /></th>
            </tr>
        </table>
        <h2>SILICATE PRICE</h2>
        <table cellspacing="0" cellpadding="0" width="100%" border="1">
            <tr>
                <th>SILICATE</th>
                <th>N.SILICATE</th>
            </tr>
            <tr>
                <td><input ng-change="calculatePrice()" ng-model="form.slirate_1" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.slisheet_1" type="number" /></td>
            </tr>
            <tr class="total-row">
                <th style="border: 0" colspan="2"><input ng-model="form.sliprice_1" type="number" /></th>
            </tr>
        </table>
        <h2>PRINTING PRICE</h2>
        <table cellspacing="0" cellpadding="0" width="100%" border="1">
            <tr>
                <th>PRINT</th>
                <th>N.PRINT</th>
            </tr>
            <tr>
                <td><input ng-change="calculatePrice()" ng-model="form.pprate_1" type="number" /></td>
                <td><input ng-change="calculatePrice()" ng-model="form.ppsheet_1" type="number" /></td>
            </tr>
            <tr class="total-row">
                <th style="border: 0" colspan="2"><input ng-model="form.ppprice_1" type="number" /></th>
            </tr>
        </table>
    </form>
    <script type="text/javascript" src="assets/js/angular.min.js"></script>
    <script>
        var app = angular.module('mainApp', []);
        app.controller('calculator', function($scope) {
            $scope.form = {
                size: 14,
                cutting: 37,
                gram: 140,
                rate: 127,
                paper: 1,

                sprate_1: 42,
                spsheet_1: 2,

                sprate_2: 50,
                spsheet_2: 2,

                pprate_1: 1,
                ppsheet_1: 1,

                slirate_1: 1,
                slisheet_1: 1,

                price: 0,
                spprice_1: 0,
                spprice_2: 0,
                ppprice_1: 0,
                sliprice_1: 0,
            }

            $scope.calculatePrice = () => {
                $scope.form.price = 0;
                $scope.form.spprice_1 = 0;
                $scope.form.spprice_2 = 0;
                $scope.form.ppprice_1 = 0;
                $scope.form.sliprice_1 = 0;

                if (
                    $scope.form.size &&
                    $scope.form.cutting &&
                    $scope.form.gram &&
                    $scope.form.rate &&
                    $scope.form.paper
                ) {
                    $scope.form.price = parseFloat((($scope.form.size * $scope.form.cutting * $scope.form.gram) / 1550000 * $scope.form.rate * $scope.form.paper).toFixed(3));
                }

                if (

                    $scope.form.spsheet_1 &&
                    $scope.form.sprate_1 &&
                    $scope.form.size &&
                    $scope.form.cutting
                ) {
                    // =D9*C5*C9/(IF(ROUND(2400/D5,0)>(2400/D5),ROUND(2400/D5,0)-1,ROUND(2400/D5,0)))
                    let amount = Math.round(2400 / $scope.form.cutting) > (2400 / $scope.form.cutting) ? Math.round(2400 / $scope.form.cutting) - 1 : Math.round(2400 / $scope.form.cutting);
                    console.log('top', $scope.form.spsheet_1 * $scope.form.size * $scope.form.sprate_1);
                    console.log('bottom', amount);
                    $scope.form.spprice_1 = parseFloat(($scope.form.spsheet_1 * $scope.form.size * $scope.form.sprate_1 / amount).toFixed(3));

                }

                if (

                    $scope.form.spsheet_2 &&
                    $scope.form.sprate_2 &&
                    $scope.form.size &&
                    $scope.form.cutting
                ) {
                    // =D9*C5*C9/(IF(ROUND(2400/D5,0)>(2400/D5),ROUND(2400/D5,0)-1,ROUND(2400/D5,0)))
                    let amount = Math.round(2400 / $scope.form.cutting) > (2400 / $scope.form.cutting) ? Math.round(2400 / $scope.form.cutting) - 1 : Math.round(2400 / $scope.form.cutting);
                    console.log('top', $scope.form.spsheet_2 * $scope.form.size * $scope.form.sprate_2);
                    console.log('bottom', amount);
                    $scope.form.spprice_2 = parseFloat(($scope.form.spsheet_2 * $scope.form.size * $scope.form.sprate_2 / amount).toFixed(3));
                }
                if (
                    $scope.form.ppsheet_1 &&
                    $scope.form.pprate_1
                ) {
                    $scope.form.ppprice_1 = parseFloat(($scope.form.ppsheet_1 * $scope.form.pprate_1).toFixed(3));
                }

                if (
                    $scope.form.size &&
                    $scope.form.cutting &&
                    $scope.form.slisheet_1
                ) {
                    $scope.form.slirate_1 = parseFloat(($scope.form.size * $scope.form.cutting * 0.0025).toFixed(3));
                    $scope.form.sliprice_1 = parseFloat(($scope.form.slisheet_1 * $scope.form.slirate_1).toFixed(3));
                }
            }

            $scope.calculatePrice();

        });
    </script>
</body>

</html>