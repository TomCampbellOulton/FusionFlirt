<?php
// We need to use sessions, so you should always start sessions using the below code.
session_start();
// Change this to your connection info.
$DATABASE_HOST = 'localhost';
$DATABASE_USER = 'root';
$DATABASE_PASS = '';
$DATABASE_NAME = 'fusion_flirt_db';
// Try and connect using the info above.
$con = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME);
// If the user is not logged in redirect to the login page...
if (!isset($_SESSION['loggedin'])) {
	header('Location: index.html');
	exit;
}
?>





<!DOCTYPE html>
<html>
    <head>
		<meta charset="utf-8">
		<title>Home Page</title>
		<link href="popup.css" rel="stylesheet">
		

    <form action=".php" method="post">
        <p> Which survey would you like to edit?</p>
        <?php
        if ($stmt = $con->prepare('SELECT survey_ID, surveyTopic FROM survey_tb')) {
            $stmt->execute();
        
            $result = $stmt->get_result();
            foreach ($result as $row){
            ?><button name="survey" type="submit" value="<?php echo $row['survey_ID'] ?>"> 
            <?php echo $row['surveyTopic'] ?> 
            </button>
            <?php
            }

            foreach ($result as $row) {
                $surveyID = $row['survey_ID'];
                ?>
                <!-- Popups -->
                <div class="popup" onclick="myFunction<?php echo $surveyID ?>()">
                    <!-- The text for inside the button -->
                    <?php echo $row['surveyTopic'] ?> -
            
                    <span class="popuptext" id="myPopup<?php echo $surveyID ?>">
                        <!-- Inside the popup bubble, add the buttons -->
                        <form action="alter_surveys.php" method="post">
                            <button name="alter" type="submit" value="<?php echo $surveyID ?>">
                                Alter
                            </button>
                        </form>
            
                        <form action="delete_survey.php" method="post">
                            <button name="delete" type="submit" value="<?php echo $surveyID ?>">
                                Delete
                            </button>
                        </form>
                    </span>
                </div>
            
                <script>
                    function myFunction<?php echo $surveyID ?>() {
                        var popup = document.getElementById("myPopup<?php echo $surveyID ?>");
                        popup.classList.toggle("show");
                    }
                </script>
                <?php
            }
            
        }?>
        
    </form>


</html>