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
    

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['alter'])) {
        $surveyID = $_POST['alter'];

        echo "Altering Survey with ID: " . $surveyID;
        // Show that survey
        
        if ($stmt = $con->prepare('SELECT question_ID, questionNumber, questionText FROM questions_tb WHERE fk_survey_ID = ?')) {
            // Bind parameters (s = string, i = int, b = blob, etc), in our case the question_ID is an integer so we use "i"
            $stmt->bind_param('i', $surveyID);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                //$stmt->bind_result($q_ID, $q_num, $q_letter, $resp_type, $q_text);
                $quesArr = array();
                foreach ($result as $question){
                    $ansArr = array();
                    // Find which questions need answering
                    // Get the answers for each question
                    if ($ansStmt = $con->prepare('SELECT answer_ID, answer, questionAnswerLetter, responseType FROM answers_tb WHERE fk_question_ID = ?')) {
                        // Bind parameters (s = string, i = int, b = blob, etc), in our case the question_ID is an integer so we use "i"
                        $ansStmt->bind_param('i', $question['question_ID']);
                        $ansStmt->execute();
                        $ansResult = $ansStmt->get_result();
                        if ($ansResult->num_rows > 0) {
                            //$stmt->bind_result($q_ID, $q_num, $q_letter, $resp_type, $q_text);
                            
                            if ($ansResult->num_rows > 0){
                                foreach ($ansResult as $answer){
                                    $ansArr[] = $answer;
                                }
                            }
                        }
                            
                    }
                    $quesArr[] = array('question'=>$question,'answers'=>$ansArr);
                }
            }
            ?>
            <form id="question-form">
            <div class="form-container"><?php
            foreach ($quesArr as $item){
                // Split the $item into two components: question and answers
                $question = $item['question'];
                $answers = $item['answers'];
                // Split up the components of that question :)
                $q_text = $question["questionText"];
                $q_num = $question["questionNumber"];
                $q_ID = $question["question_ID"];
                ?>

                <div class="question-container-<?php echo $q_ID?>">
                <!-- Creates the fieldset within the main form for that question -->
                <fieldset id="fieldset_<?php echo $q_num ?>">

                <!-- Writes what the question is above the new fieldset -->
                <legend><?php echo $q_num?>. <?php echo $q_text?>

                <!-- Adds a remove button to delete the question-->
                <button type="button" class="question-container-<?php echo $q_ID?>" onclick="removeQuestion(<?php echo $q_ID ?>)">Remove Question</button>
                </legend>

                <!-- Adds the buttons to alter the data-->
                <!-- Create a button for adding a response to the question -->
                <div class="add-answer-container">
                <input type="text" name="dynamic_answers[<?php echo $q_num ?>][]" placeholder="Enter a new answer" id="add-answer-id-<?php echo $q_num ?>">
                <button type="button" class="add-answer-button" onclick="addAnswer(<?php echo $q_num ?>)">Add Answer</button>
                </div>
                <!-- Create a button for adding a response to the question -->


                <?php
                // Write out all the answers to the question :)
                foreach ($answers as $ans){
                    $a_text = $ans['answer'];
                    $resp_type = $ans['responseType'];
                    $ans_ID = $ans['answer_ID'];
                    ?>
                    
                    <div class = "answer-container-<?php echo $ans_ID?>">
                    <!-- Checks if the response type for the input will be checkbox, radio or input -->
                    <?php 
                    if ($resp_type === "radio" or $resp_type === "checkbox"){?>
                        <!-- Creates the appropraite response type for each response and adds them to the form -->
                        <!-- For checkboxes or radio inputs, don't record the "response text" just the ID of the response -->
                        <input type="<?php echo $resp_type?>" id="<?php echo $ans_ID?>" value="$ans_ID" name="<?php echo $q_ID?>">
                        <?php
                    }else{?>
                        <!-- Creates the appropraite response type for each response and adds them to the form -->
                        <!-- For inputs, record the "response text". Default Value will be empty -->
                        <input type="<?php echo $resp_type?>" id="<?php echo $ans_ID?>" value="" name="<?php echo $q_ID?>">
                        <?php
                        }
                    
                    ?>	

                    <!-- Adds a label to tell the user what each box represents -->
                    <label for="<?php echo $ans_ID?>"> <?php echo $a_text?> </label>
                    
                    <!-- Adds a remove button to delete the answer-->
                    <button type="button" class="remove-answer-button" onclick="removeAnswer(<?php echo $ans_ID ?>)">Remove Answer</button>
                    </div>
            

                    <?php

                }
                ?>
                
                </fieldset>
                </div>
                <?php
            }
            ?>
                    
            <!-- Adds an add question button to add a question-->
            <input type="text" id="newQuestion" name="newQuestion">
            <button type="button" class="add-question-button" onclick="addQuestion()">Add Question</button>
            
            <!-- Submit Button -->
            <input type="submit" value="Submit">
            </div>
            <!-- Now all the forms are complete, the final form needs to be closed -->
            </form>
            <?php
        }
        }

    } else {
        // Handle other form elements or show an error message.
        echo "Invalid request.";
    }




?><script>
// Initialize the questionNumber as a global variable
var questionNumber = <?php echo $q_num; ?>;

function addAnswer() {
    // Get the input value for the new answer
    var newAnswerText = document.getElementById('add-answer-id-' + questionNumber).value;

    // Create a new container div for the answer
    var container = document.createElement('div');
    container.className = 'answer-container';

    // Create a new radio button
    var newAnswerInput = document.createElement('input');
    newAnswerInput.type = 'radio';
    newAnswerInput.name = 'dynamic_answers[' + questionNumber + '][]'; // Use the same name for radio buttons within a question
    newAnswerInput.value = newAnswerText; // Set the value for the new radio button

    // Create a label for the new answer input field
    var label = document.createElement('label');
    label.textContent = newAnswerText;

    // Create a delete button
    var deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.textContent = 'Delete Answer';
    deleteButton.onclick = function () {
        // Remove the entire container when the delete button is clicked
        container.remove();
    };

    // Append the new radio button, label, delete button to the container
    container.appendChild(newAnswerInput);
    container.appendChild(label);
    container.appendChild(deleteButton);

    // Append the container to the question container
    document.getElementById('fieldset_' + questionNumber).appendChild(container);
}

function removeAnswer(answerID) {
    var elementsToRemove = document.getElementsByClassName("answer-container-" + answerID);

    if (elementsToRemove.length > 0) {
        // Assuming you want to remove all elements with the specified class
        for (var i = 0; i < elementsToRemove.length; i++) {
            elementsToRemove[i].remove();
        }
    } else {
        alert('Elements to remove not found');
    }
}

function removeQuestion(questionID) {
    var elementsToRemove = document.getElementsByClassName("question-container-" + questionID);

    if (elementsToRemove.length > 0) {
        // Assuming you want to remove all elements with the specified class
        for (var i = 0; i < elementsToRemove.length; i++) {
            elementsToRemove[i].remove();
        }
    } else {
        alert('Elements to remove not found');
    }
}

function addQuestion() {
    // Get the value from the input field
    var newQuestionText = document.getElementById('newQuestion').value;

    // Increase question number by 1
    questionNumber++;

    // Create a new container fieldset for the question
    var newQuestionContainer = document.createElement('fieldset');
    newQuestionContainer.className = 'question-container-' + questionNumber;

    // Create a new legend for the question
    var newQuestionLegend = document.createElement('legend');
    newQuestionLegend.textContent = 'ello';

    // Create a button for removing the question
    var removeQuestionButton = document.createElement('button');
    removeQuestionButton.type = 'button';
    removeQuestionButton.textContent = 'Remove Question';
    removeQuestionButton.onclick = function () {
        removeQuestion(questionNumber);
    };

    // Create a button for adding answers
    var addAnswerButton = document.createElement('button');
    addAnswerButton.type = 'button';
    addAnswerButton.textContent = 'Add Answer';
    addAnswerButton.id = 'add-answer-id-' + questionNumber;
    addAnswerButton.onclick = addAnswerClosure();

    // Create an Input for adding answers
    var addAnswerInput = document.createElement('input');
    addAnswerInput.type = 'text';
    addAnswerInput.placeholder = 'Enter an answer';
    addAnswerInput.id = 'dynamic_answers[' + questionNumber + '][]';

    // Append the legend, remove button, add answer input, and add answer button to the container
    newQuestionLegend.appendChild(removeQuestionButton);
    newQuestionContainer.appendChild(newQuestionLegend);
    newQuestionContainer.appendChild(addAnswerInput);
    newQuestionContainer.appendChild(addAnswerButton);

    // Append the container to the form with the ID "myForm"
    document.getElementById('question-form').appendChild(newQuestionContainer);
}
</script>


</html>