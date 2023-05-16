<?php

require_once('processing.php');
credentialsCheck();

// in case of no budget record for the current month, create a one
Budget::create_budget_record(get_id());


$month = date('m');
$year = date('Y');
$pairs = month_year_pair(get_id()); // get month-year pairs available in database
$categories = Purchase::get_categories(); // get categories stored in database



// get filter parameters if found
$category = !empty($_GET['category']) ? $_GET['category'] : null;
$above = !empty($_GET['above']) ? $_GET['above'] : null;
$below = !empty($_GET['below']) ? $_GET['below'] : null;

// Adding purchase handling
if (!empty($_POST['price']) && !empty($_POST['item'])) {
    $flag = Purchase::add_purchase($month,get_id(),$year);
    if ($flag == false){
        echo "<script>alert('Purchase Recording Has Failed For Some Reason')</script>";
    }
}

// retrieve purchases based on month - year filter
if (empty($_GET['monthyear'])) {

    $purchases = Purchase::get_purchases($month, $year, get_id(), $category, $above, $below);
} else {
    $temp = parseMonthYearPair($_GET['monthyear']);
    $month = $temp[0];
    $year = $temp[1];
    $purchases = Purchase::get_purchases($month, $year, get_id(), $category, $above, $below);
}



require_once('head.php');
$place = 'purchases';
?>


<body class="wave-bg" style="background-image:url('imgs/wave.svg')">
    
    <div class="row m-0">

        <?php
        require_once('sidenav.php'); //  desktop view
        require_once('mobilenav.php') // mobile view
        ?>
        <div class="purchases" style="padding-left:280px; padding-right:0;">

            <!-- filtering -->
            <div class="bg-success text-light ps-2 pt-2 pb-2 fs-4 text-center">
                Wanna display specific purchases?&nbsp; &nbsp;
                <button type="button" class="btn btn-light text-success" data-bs-toggle="modal" data-bs-target="#exampleModal2">Filter Your List</button>
            </div>



            <!-- filtering modal -->
            <div class="modal fade" id="exampleModal2" tabindex="-1" aria-labelledby="exampleModalLabel2" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-success" id="exampleModalLabel2">Filter Purchases List .. </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">

                            <form class="row g-3 mt-4 ps-4 filter-form" action="purchases.php" method="get">

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Category</label>
                                        <select name="category" class="form-control">
                                            <option value="">Select a category to filter upon</option>
                                            <?php
                                            foreach ($categories as $c) {
                                            ?>
                                                <option value="<?= $c ?>"><?= $c ?></option>
                                            <?php } ?>

                                        </select>
                                    </div>
                                </diV>

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Cost Above</label>
                                        <input type="text" class="form-control" id="above" name="above" placeholder="Display purchases with cost above ...">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Cost Below</label>
                                        <input type="text" class="form-control" id="below" name="below" placeholder="Display purchases with cost below ...">
                                    </div>
                                </div>
                                <input type="hidden" class="form-control" id="costbelow" name="monthyear" value=<?= getMonthName($month) . $year ?>>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Apply Filter</button>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>

            <!-- display deletion success bar -->
            <?php
            if (!empty($_GET['successdel'])) { ?>
                <div class="text-center bg-warning">Purchase is successfully deleted! &nbsp;Note: any set filters have been cleared!</div>
            <?php } ?>

            <!-- header -->
            <div class="header-purchases p-2 pb-0 mt-1 d-flex justify-content-between align-items-center ">

                <!-- displayed month filter -->
                <div style="font-size:2.3rem;" class="pt-4 ">
                    <form class="disp-purchases-form mb-0 d-flex align-items-center" method="get" action="purchases.php" onchange="submit()">
                        <div class="col-auto">
                            <label class="col-form-label pe-2"> Purchases for Month</label>
                        </div>
                        <div class="col-auto">


                            <select name="monthyear" style="font-size:2rem;" class="text-success p-2 form-select border-white ">
                                <?php
                                if (!empty($pairs)) {
                                    foreach ($pairs as $pair) {
                                        $pair = explode("-", $pair);
                                        $m = $pair[0];
                                        $y = $pair[1];
                                ?>
                                        <option style="font-size:2rem;" value="<?= $m . "" . $y ?>" <?= !empty($_GET['monthyear']) && $_GET['monthyear'] == $m . $y ? " selected " : " " ?>><?= $m . " " . $y ?></option>
                                <?php }
                                }
                                ?>

                            </select>
                        </div>
                    </form>
                </div>

                <div>
                <button type="button" class="btn text-success mt-3 fw-bold plus-button me-1" data-bs-toggle="modal" data-bs-target="#exampleModal3">+</button>
                </div>

            </div>

                        <!-- Add Purchase Modal -->
                        <div class="modal fade" id="exampleModal3" tabindex="-1" aria-labelledby="exampleModalLabel3" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title text-success" id="exampleModalLabel3">Add a New Purchase ..</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <form class="row g-3 mt-4 ps-4 add-form" method="post" action="purchases.php">

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Item</label>
                                        <input type="text" class="form-control" id="item" name="item" placeholder="Enter items label here...">
                                    </div>
                                </diV>

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Price</label>
                                        <input type="text" class="form-control" id="price" name="price" placeholder="Enter a numeric value for the price...">
                                    </div>
                                </div>


                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category" id="category">
                                            <option selected value="Groceries">Groceries</option>
                                            <option value="Food">Food</option>
                                            <option value="Furniture">Furniture</option>
                                            <option value="Clothing">Clothing</option>
                                            <option value="Electronics">Electronics</option>
                                            <option value="Books">Books</option>
                                            <option value="Toys">Toys</option>
                                            <option value="Outing">Outing</option>
                                            <option value="Home Decor">Home Decor</option>
                                            <option value="Jewellery">Jewellery</option>
                                            <option value="Fitness Equipment">Fitness Equipment</option>
                                            <option value="Car Maintenance">Car Maintenance</option>
                                            <option value="Beauty Products">Beauty Products</option>
                                            <option value="Services">Services</option>
                                            <option value="Medicine">Medicine</option>
                                            <option value="Others">Others</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Date of Purchasing Process</label>
                                        <input type="date" class="form-control" id="date" name="date">
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div>
                                        <label class="form-label">Payment Method</label>
                                        <select  class="form-select" name="paymethod" id="paymethod">
                                            <option selected value="cash">Cash</option>
                                            <option value="visa">Visa</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Add</button>
                                </div>

                            </form>

                        </div>

                    </div>
                </div>
            </div>


            <!-- purchases table -->
            <div class="p-2 pt-0 table-responsive p-table">
                <table class="table table-striped table-hover text-center the-table">
                    <thead class="table-success">
                        <tr>
                            <th scope="col" class="d-none d-md-table-cell">#</th>
                            <th scope="col">Date</th>
                            <th scope="col">Purchase</th>
                            <th scope="col">Category</th>
                            <th scope="col">Price</th>
                            <th scope="col">Paid by</th>
                            <?= $month == date('m') && $year == date('Y') ? '<th scope="col">Remove</th>' : '' ?>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $i = 1;

                        if (!empty($purchases)) {
                            foreach ($purchases as $p) {
                        ?>
                                <tr>
                                    <th scope="row" class="d-none d-md-table-cell"><?= $i++ ?></th>
                                    <td><?= $p->getDate(); ?></td>
                                    <td><?= $p->getName(); ?></td>
                                    <td><?= $p->getCategory(); ?></td>
                                    <td><?= $p->getPrice(); ?></td>
                                    <td><?= $p->getPayment(); ?></td>
                                    <?= ($month == date('m') && $year == date('Y')) ? '<td><i data-bs-toggle="modal" data-bs-target="#exampleModal3' . $p->getId() . '" class="fa-solid fa-trash-can text-danger fs-5" ></i></td>' : '' ?>
                                </tr>

                                <!-- delete modal -->
                                <div class="modal fade" id="exampleModal3<?= $p->getId(); ?>" tabindex="-1" aria-labelledby="exampleModalLabel3<?= $p->getId() ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title text-success" id="exampleModalLabel3<?= $p->getId() ?>">Deleting Purchase ... </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">Are you sure you want to delete this purchase?
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <a href="processing.php?delete=<?= $p->getId() ?>&monthyear=<?= getMonthName($month) . $year ?>" class="btn btn-danger text-decoration-none">Delete</a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                        <?php
                            }
                        } ?>


                    </tbody>

                </table>
            </div>

        </div>

    </div>
</body>

</html>