<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Answer Form</title>
</head>
<body>

<form action="process_form.php" method="post">
    <label for="question">Question:</label>
    <input type="text" name="question" id="question" required>

    <div id="answerContainer">
        <!-- Initial answer input field -->
        <input type="text" name="answers[]" placeholder="Enter an answer" required>
    </div>

    <button type="button" onclick="addAnswer()">Add Answer</button>

    <input type="submit" value="Submit Form">
</form>

<script>
    function addAnswer() {
        var container = document.getElementById('answerContainer');
        var newAnswerInput = document.createElement('input');
        newAnswerInput.type = 'text';
        newAnswerInput.name = 'answers[]';
        newAnswerInput.placeholder = 'Enter an answer';
        newAnswerInput.required = true;
        container.appendChild(newAnswerInput);
    }
</script>

</body>
</html>
