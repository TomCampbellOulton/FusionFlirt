<?php
// Creates empty arrays to store the user’s and the requirements
All_users = array()
Users_requirements = array()
SQL QUERY (Select user_ID from users_tb)
All_users APPEND (user_ID)
// For every requirement the user has
For Each requirement IN Users_requirements:
	// Empty array to store the users who met the requirements
	Met_requirements = array()
	// Iterates through each user
	For Each user IN All_users:
		Response = SQL QUERY (Select answer_rating from users_responses_tb where fk_user_ID = user)
		// If the user should be rejected immediately
		If requirement ==  1
			Remove user from All_users
		// If the user is required to respond
		Else If requirement == 7
			Add user to Met_requirements
            // Increase the counter of number of times the user met that requirement by 1
			Increment Users Score [7] by 1
		Else
			Add user to Met_requirements
            // Increase the counter of number of times the user met that requirement by 1
			Increment Users Score [rating] by 1
    // Check how many users met the requirements
    For Each user in All_users
        // If the user isn't in the list of those who met the requirements
        If user Not In Met_requirements
            // Remove the user
            Remove user from All_users
// Calculate the scores
UserScore = 0
For each user in All_users
    // Add the score
    UserScore = - ((2*numOf2) ** 2 + numOf3 ** 2) + numOf4 ** 2 + numOf5 ** 2 + (2*numOf6) ** 2 + (3*numOf7) ** 2
?>