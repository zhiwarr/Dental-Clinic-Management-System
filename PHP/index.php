<?php //This page allows the user to login as an employee or a patient.
ob_start();
session_start();

include_once 'functions.php';
include_once 'db.php';
error_reporting(0);

// Fetch all user accounts
$stmt = $dbconn->query("SELECT * FROM user_account");
$userAccounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $username = check_empty_input($_POST["username"]);
    $password = check_empty_input($_POST["password"]);
    $login_type = $_POST['login_type'];

    if ($username != -1 && $password != -1) {
        try {
            // Prepare and execute the query using parameters to prevent SQL injection
            $stmt = $dbconn->prepare("SELECT password, patient_id, employee_id FROM user_account WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $hashedPass = $stmt->fetch(PDO::FETCH_NUM);

            if (!$hashedPass) {
                $err = "The entered username does not match anything existing in our records. Please verify and/or register the entered username.";
            } elseif (password_verify($password, $hashedPass[0])) {
                $_SESSION['valid'] = true;
                $_SESSION['timeout'] = time();

                if ($hashedPass[1] == null && $login_type == "Patient") {
                    $err = "Your login credentials are correct, but there is no Patient info associated with this account. Please register as a patient.";
                } elseif ($hashedPass[2] == null && $login_type == "Employee") {
                    $err = "Your login credentials are correct, but there is no Employee info associated with this account. Please contact your branch's IT department.";
                } else {
                    echo "<h1> $username has logged in as " . $login_type . "!</h1>";

                    if ($login_type == 'Employee') {
                        $eID = $hashedPass[2];

                        $stmt = $dbconn->prepare("SELECT employee_sin FROM Employee WHERE employee_id = :employee_id");
                        $stmt->bindParam(':employee_id', $eID, PDO::PARAM_INT);
                        $stmt->execute();
                        $eSIN = $stmt->fetchColumn();

                        $stmt = $dbconn->prepare("SELECT employee_type FROM Employee_info WHERE employee_sin = :employee_sin");
                        $stmt->bindParam(':employee_sin', $eSIN, PDO::PARAM_INT);
                        $stmt->execute();
                        $eType = $stmt->fetchColumn();

                        $_SESSION['empID'] = $eID;
                        $_SESSION['empUName'] = $username;

                        if ($eType == 'd' || $eType == 'h') {
                            header('Location:dentist_landing.php');
                        } elseif ($eType == 'r') {
                            header('Location:receptionist_landing.php');
                        }
                    } else {
                        $_SESSION['patientUsername'] = $username;
                        header('Location:patient_landing.php');
                    }
                }
            } else {
                $err = "ERROR: The password you entered does not match this account.";
            }
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCMS - Login</title>
    <link rel="icon" type="image/x-icon" href="images/login2.png">
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="patient_landing_style.css" />
    <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/excite-bike/jquery-ui.css" rel="stylesheet"
        type="text/css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="scripts/dbms.js"></script>
    <script>
    $(document).ready(function() {
        $("#toggle").click(function() {
            $("#accounts").fadeToggle();
        });
    });
    </script>

</head>

<body>

    <div class="container bootstrap snippets bootdey">
        <br>
        <h1 class="bio-graph-heading dcms-header" style="font-size: 30px;">Dental Clinic Management System</h1>
        <br>
        <!-- <h1 style="text-align:center">Login page</h1> -->
        <h2>Enter Login Details</h2>
        <h3 class="error"><?php echo $err ?></h3>
        <!-- <span class="error"> * indicates a field is required </span>  -->

        <form class="form-signin" role="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>"
            method="post">
            <h4 class="form-signin-heading"><?php echo $msg; ?></h4>

            <h4>Username</h4>
            <input type="text" class="form-control" name="username" placeholder="Enter username" maxlength="255"
                required>
            <span class="error"> * <?php echo $username == - 1 ? 'Username is required!' : '' ?> </span><br>

            <h4>Password</h4>
            <input type="password" class="form-control" name="password" placeholder="Enter password" maxlength="255"
                required>
            <span class="error"> * <?php echo $password == - 1 ? 'Password is required!' : '' ?> </span><br>

            <fieldset>
                <legend>
                    <h4>What are you logging in as?</h4>
                </legend>
                <input type="radio" name="login_type" value="Patient" checked="true"> Patient<br>
                <input type="radio" name="login_type" value="Employee"> Employee<br>
            </fieldset>
            <br><br>
            <button class="btn btn-lg btn-primary btn-block btn-warning" type="submit" name="login">Login</button>
        </form>

        <h2> No Account? <a href="register.php">Register here! </a> </h2> <br>

    </div>


    </div>

</body>
<script>
// this if statement turns off the "Confirm Form Resubmission" and prevents multiple form submissions
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</html>