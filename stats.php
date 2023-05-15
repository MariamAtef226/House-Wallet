<?php
require_once('processing.php');
credentialsCheck();

// in case of no budget record for the current month, create a one
Budget::create_budget_record(get_id());

$month = date('m');
$year = date('Y');
$years = Budget::get_budget_years(get_id()); // returns budget years for his user

// retrieve budgets based on year
if (empty($_GET['year'])) {
    $budgets = Budget::budgetsOfThisYear(get_id(), $year);
} else {
    $year = $_GET['year'];
    $budgets = Budget::budgetsOfThisYear(get_id(), $year);
}

// data for first chart: saved percentage of budget

foreach ($budgets as $budget) {
    $temp = $budget->getInitial() - $budget->getConsumed();
    $temp = $temp / $budget->getInitial();
    $temp = $temp * 100;
    $savedPercentage[] = $temp;
    $savedPercentageMonths[] = "'" . getMonthName($budget->getMonth()) . "'";
}
if (!empty($savedPercentage) && !empty($savedPercentageMonths)) {
    $savedPercentage = implode(", ", $savedPercentage);
    $savedPercentageMonths = implode(", ", $savedPercentageMonths);
}

// data for second chart: percentage of purchases for each category

$purchases = Purchase::get_purchases_percentage($year, get_id());
$tot_consumed = Budget::total_consumption_per_year(get_id(), $year);
if (!empty($tot_consumed)) {
    foreach ($purchases as $purchase) {
        $cats[] = "'" . $purchase[0] . "'";
        $cat_percentage[] = ($purchase[1] / $tot_consumed) * 100;
    }
}
if (!empty($cats) && !empty($cat_percentage)) {

    if (array_sum($cat_percentage) < 100) {
        $cats[] = "'Others'";
        $cat_percentage[] = 100 - array_sum($cat_percentage);
    }
    $cats = implode(", ", $cats);
    $cat_percentage = implode(", ", $cat_percentage);
}

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

                <!-- displayed month filter -->
                <div style="font-size:2rem;" class="pt-2 text-center bg-success text-light">
                    <form method="get" action="stats.php" onchange="submit()" class="p-2 d-flex align-items-center past-form-filter">
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
                                    },
                                    title: {
                                        display: true,
                                    }
                                }
                            });
                        </script>
                    </div>


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
                                    title: {
                                        display: true,
                                    }
                                }
                            });
                        </script>
                    </div>


                <?php
                }
                ?>

            </div>
        </div>
</body>




</html>