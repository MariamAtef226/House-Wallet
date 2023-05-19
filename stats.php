<?php
require_once('processing.php');
credentialsCheck();
Budget::create_budget_record(get_id()); // in case of no budget record for the current month, create a one



$month = date('m');
$year = date('Y');
$years = Budget::get_budget_years(get_id()); // returns budget years for this user



// retrieve budgets based on year
if (empty($_GET['year'])) {
    $budgets = Budget::budgetsOfThisYear(get_id(), $year);
} else {
    $year = $_GET['year'];
    $budgets = Budget::budgetsOfThisYear(get_id(), $year);
}

// retieve purchases data for the pie chart
$purchases = Purchase::get_purchases_percentage($year, get_id());
$tot_consumed = Budget::total_consumption_per_year(get_id(), $year);


$temp = savedPercentChart($budgets);
$savedPercentage = $temp[0];
$savedPercentageMonths = $temp[1];

$temp = purchasesPercentages($purchases, $tot_consumed);
$cats = $temp[0];
$cat_percentage = $temp[1];

$payCount = Purchase::visa_cash_count(get_id(), $year);
$visa = $payCount["visa"];
$cash = $payCount["cash"];

require_once('head.php');
$place = 'statistics';
?>



<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js">
</script>

<body>
    <div class="row m-0">

        <?php
        require_once('sidenav.php'); //  desktop view
        require_once('mobilenav.php') //  mobile view
        ?>
        <div class="past stats" style="padding-left:280px; padding-right:0;">
            <div class="header-purchases">

                <!-- displayed year filter -->
                <div style="font-size:2rem;" class="pt-2 text-center bg-success text-light">
                    <form method="get" action="stats.php" onchange="submit()" class="p-2 d-flex align-items-center past-form-filter flex-column flex-md-row">
                        <label class="form-label col-auto ps-md-3 pe-2 pe-md-3"> Displaying Statistics for year
                        </label>
                        <div class="col-auto">
                            <select style="font-size:1.6rem;" name="year" class="text-success border-success form-select ">
                                <?php
                                if (!empty($years)) {
                                    foreach ($years as $y) {
                                ?>
                                        <option value="<?= $y ?>" <?= !empty($_GET['year']) && $_GET['year'] == $y ? " selected " : " " ?>><?= $y ?></option>
                                <?php }
                                }
                                ?>

                            </select>
                        </div>
                    </form>
                </div>

                <?php
                if (!empty($budgets)) { ?>
                    <!-- saved percentage bar chart -->
                    <h6 class="text-center">
                        Saved Budget Percentage for year <?= $year ?>
                    </h6>
                    <div class="d-flex justify-content-center pb-4">

                        <canvas id="savePercentage" style="width:100%;max-width:700px"></canvas>
                        <script>
                            var xValues = [<?= $savedPercentageMonths ?>];
                            var yValues = [<?= $savedPercentage ?>];
                            var barColors = ["#42c05e", "#243b28", "#90ffa6", "#07781b", "#b7ffcc", "#186306", "#aaffc0", "#198754", "#9dffb2", "#394d43", "#d1e7dd", "#83ff9a"]
                            new Chart("savePercentage", {
                                type: "bar",
                                data: {
                                    labels: xValues,
                                    datasets: [{
                                        backgroundColor: barColors,
                                        data: yValues
                                    }]
                                },
                                options: {
                                    legend: {
                                        display: false
                                    }
                                }
                            });
                        </script>
                    </div>

                    <hr>
                <?php } ?>

                <?php if (!empty($purchases)) { ?>

                    <!-- categories percentage -->
                    <h6 class="text-center pt-4">
                        Categories Purchases Percentage for year <?= $year ?> </h6>
                    <div class="d-flex justify-content-center  pb-4">
                        <canvas id="catPercentage" style="width:100%;max-width:700px"></canvas>

                        <script>
                            var xValues = [<?= $cats ?>];
                            var yValues = [<?= $cat_percentage ?>];
                            var barColors = ["#42c05e", "#243b28", "#90ffa6", "#07781b", "#b7ffcc", "#186306", "#aaffc0", "#198754", "#9dffb2", "#394d43", "#d1e7dd", "#83ff9a"]


                            new Chart("catPercentage", {
                                type: "pie",
                                data: {
                                    labels: xValues,
                                    datasets: [{
                                        backgroundColor: barColors,
                                        data: yValues
                                    }]
                                },
                                options: {

                                }
                            });
                        </script>
                    </div>


                <?php
                }
                ?>

                <hr>

                <!-- visa cash count -->
                <h6 class="text-center pt-4">
                    Cash Purchases vs Visa Purchases for year <?= $year ?> </h6>
                <div class="d-flex justify-content-center  pb-4">
                    <canvas id="myChart" style="width:100%;max-width:600px"></canvas>

                    <script>
                        var xValues = ["Cash", "Visa"];
                        var yValues = [<?= $cash ?>, <?= $visa ?>];
                        var barColors = ["#aaffc0", "#198754"];

                        new Chart("myChart", {
                            type: "horizontalBar",
                            data: {
                                labels: xValues,
                                datasets: [{
                                    backgroundColor: barColors,
                                    data: yValues
                                }]
                            },
                            options: {
                                legend: {
                                    display: false
                                }
                            }
                        });
                    </script>
                </div>


            </div>
        </div>
</body>




</html>