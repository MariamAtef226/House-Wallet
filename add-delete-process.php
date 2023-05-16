<?php

require_once("processing.php");
$month = date('m'); // gets current month
$year = date('Y'); // gets current month
// Adding purchase handling
if (!empty($_POST['price']) && !empty($_POST['item'])) {
    $flag = Purchase::add_purchase($month,get_id(),$year);
    if ($flag == false){
        echo "<script>alert('Purchase Recording Has Failed For Some Reason')</script>";
    }
    redirect_page($_POST['source']);
}
elseif(!empty($_GET['delete'])){
    // 1st: delete purchase and store its price ( to update budget )
    $price = Purchase::delete_purchase($_GET['delete']);

    // 2nd: updating budget
    $month = date('m');
    $year = date('Y');
    $budget = Budget::budget_details(get_id(),$month,$year);
    $cold = $budget->getConsumed();
    $c = intval($cold);
    $c=$c-$price;
    Budget::update_consumption(get_id(),$c);

    // 3rd: redirect to same page
    redirect_page('purchases.php?monthyear='.$_GET["monthyear"].'&successdel=yes&price='.$price.'&cold=.'.$cold.'&c=.'.$c);
}