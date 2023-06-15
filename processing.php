<?php
require_once('User.php');
require_once('Purchase.php');
session_start();



// Process Signup Process
function signupProcessing()
{

    // if data was entered (no empty fields) --> let's process
    if (!empty($_POST['email']) && !empty($_POST['password']) && !empty($_POST['confpassword']) && !empty($_POST['name'])) {

        $email = '';
        $name = $_POST['name'];
        $password = $_POST['password'];
        $pass_confirm = $_POST['confpassword'];

        //Validating data (email)
        $filtered_email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)); // filters (cleans) email from any white spaces
        if ($valid_email = filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) { // validates email is valid
            $email = $valid_email;
        } else {
            redirect_page('login.php?status=invalid_email&signupform=yes');
            die('exit');
        }

        //validate password confirmation
        if ($password == $pass_confirm) {

            //at this point, data is valid 

            // -- check if a user with that email already exists or not
            //false --> can enter, true --> someone with same email exists
            $result = User::find_signup($email);
            if (!$result) {

                //1) insert user - create
                $u = new User($name, $email, $password);
                $id = $u->create();
                //2)assume when user signs up, we set remember me by default 
                setcookie('id', $id, time() + 60 * 60 * 24 * 365, '/');
                setcookie('name', $name, time() + 60 * 60 * 24 * 365, '/');


                //3) store in session (for access allowance for other pages)
                $_SESSION['id'] = $id;
                $_SESSION['name'] = $name;
            } else {
                redirect_page('login.php?status=already_exists&signupform=yes'); // handle status
            }
        } else {
            redirect_page('login.php?status=dismatch&signupform=yes'); // handle status
            die('exit');
        }
    } else {
        redirect_page('login.php?status=empty&signupform=yes'); //to display alert message in case of empty fields (use BS modal)
    }
}


// Process Login
function loginProcessing()
{
    // if data was entered (no empty fields) --> let's process
    if (!empty($_POST['email']) && !empty($_POST['password'])) {

        $email = '';
        $password = $_POST['password'];

        //Validating data (email)
        $filtered_email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)); //filters (cleans) email from any white spaces
        if ($valid_email = filter_var($filtered_email, FILTER_VALIDATE_EMAIL)) { //validates email is valid
            $email = $valid_email;
        } else {
            redirect_page('login.php?status=invalid_email&loginform=yes');
        }

        //at this point, data is valid and we can check existence of this user in system

        $result = User::find_login($email, $password);
        if ($result) {


            // Store credentials in cookies
            setcookie('name', $result->name, time() + 60 * 60 * 24 * 365, "/");
            setcookie('id', $result->user_id, time() + 60 * 60 * 24 * 365, "/");

            //to propagate user's data throughout pages (in both cases remember me and not) 
            $_SESSION['name'] = $result->name;
            $_SESSION['id'] = $result->user_id;

            redirect_page('index.php');
        } else {
            redirect_page('login.php?status=not_found&loginform=yes');
        }
    } else {
        redirect_page('login.php?status=empty&loginform=yes');
    }
}


// Credentials Check
function credentialsCheck()
{

    if (empty($_SESSION['name'])) {
        // me7tageen n3aby el session

        if (!empty($_COOKIE['name'])) { //y3ny ana already logged in bs lesa ba2ol besmellah habda2 el session
            $_SESSION['name'] = $_COOKIE['name'];
            $_SESSION['id'] = $_COOKIE['id'];
        } else { // illegal access state - msh 3amel login w by7awel y access el page de
            redirect_page('login.php');
        }
    }
}


// Process Logout
function logoutProcessing()
{
    session_destroy();
    setcookie('name', null, time(), "/");
    setcookie('id', null, time(), "/");
    redirect_page('login.php');
}


// return name for session
function get_username()
{
    return $_SESSION['name'];
}

// return name for session
function get_id()
{
    return $_SESSION['id'];
}

// Month translation
function getMonthName($monthNum)
{
    return date("F", mktime(0, 0, 0, $monthNum, 1));
}


function month_year_pair($id)
{
    try {
        $connect = pdo_connect();
        $statment = $connect->prepare("select budget_month, yr FROM `budget` WHERE user_id = :id order by yr, budget_month DESC");
        $statment->bindValue("id", $id);
        $statment->execute();
        // if found
        while ($result = $statment->fetchObject()) {
            $pairs[] = getMonthName($result->budget_month)."-".$result->yr;
        }
        $connect = null;
        if (!empty($pairs)){
            return $pairs;
        }
        else{
            return false;
        }
    } catch (PDOException $e) {
        catchErrorToFile($e->getMessage(), $e->getCode());
        return false;
    }
}


function parseMonthYearPair($str){
    $month = preg_replace('/[0-9]+/', '', $str);
    $year = preg_replace('/\D/', '', $str);

    // get the month in numbers
    $dateObj = DateTime::createFromFormat('!F', $month);
    $month = $dateObj->format('n');
    
    return array($month, intval($year));
    
}


// data for first chart in statistics page: saved percentage of budget
function savedPercentChart($budgets)
{
    foreach ($budgets as $budget) {
        if ($budget->getInitial() !=0){
        $savedPercentage[] = ($budget->getInitial() - $budget->getConsumed()) * 100 / $budget->getInitial();
        $savedPercentageMonths[] = "'" . getMonthName($budget->getMonth()) . "'";
        }
        else{
            $savedPercentage[] = 0;
            $savedPercentageMonths[] = "'" . getMonthName($budget->getMonth()) . "'";
        }
    }
    if (!empty($savedPercentage) && !empty($savedPercentageMonths)) {
        $savedPercentage = implode(", ", $savedPercentage);
        $savedPercentageMonths = implode(", ", $savedPercentageMonths);
        return array($savedPercentage, $savedPercentageMonths);
    }

    return false;
}

// data for second chart in statistics page: percentage of purchases for each category
function purchasesPercentages($purchases, $tot_consumed)
{
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
        return array($cats, $cat_percentage);
    }
    return false;
    

}





if (!empty($_GET['mode'])) {
    if ($_GET['mode'] == "logout") {
        logoutProcessing();
    }
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
