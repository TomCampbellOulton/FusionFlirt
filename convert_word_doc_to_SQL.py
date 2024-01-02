# Opens the file containing the questions for the surveys
file = open("E:/XAMPP/htdocs/Dating_App/Complete Questionairre For Dating Site Updated.txt", "r", encoding="utf-8")
# Reads the file and converts the contents into a list
# where each element in the list is a new line in the document
f = file.readlines()
# Closes the file
file.close()

# Define the alphabet
alphabet = "abcdefghijklmnopqrstuvwxyz"

# Cleanse the data
def clean(file):
    # Empty dictionary of cleansed data
    clean_data = {}
    # Empty list of removed data
    removed_data = []
    # Empty counter for the number of lines iterated
    line_num = 1
    # Most recent title for section is empty until defined
    title = ""
    # Most recent question for responses is empty until defined
    question = ""

    # Iterates through every line in the file
    for l in file:
        # If the line is empty
        if l == "\n":
            # It's discarded
            removed_data.append(l)

        # If there is no "." in the line then
        # the line does not contain a question
        # so can either be removed OR
        # it's a title for a new section
        elif ("." in l) == False:

            # Boolean variable to reduce
            # interations to confirm data
            # set to false as default, true
            # once data has been confirmed
            confirmed_data = False

            # Counter variable to track
            # alphabet characters checked
            counter = 0

            # Whilst no data is found,
            # this loop continues running
            while confirmed_data == False:
                # If the next letter in the alphabet
                letter = alphabet[counter]
                
                # Increases the line counter by 1 per iteration
                # completed
                counter += 1

                # Makes the line lower case to reduce
                # chekcs required
                line = l.lower()

                # Checks if the letter is in the line
                if letter in line.lower():
                    # Data is in the line so the loop can end
                    confirmed_data = True
                    # Data is in the loop so line is valid
                    valid_line = True

                # If the line counter has reached the length of the alphabet
                elif counter >= len(alphabet):
                    # Loop is terminated
                    confirmed_data = True
                    # No data in line so line is invalid
                    valid_line = False

            # If there is no data in the line                    
            if valid_line == False:
                # Remove the line
                removed_data.append(l)
            # If there is data in the line
            elif valid_line:                            
                # We replace the "\n" - newline prompt
                # and the "\t" - tab character
                # with an empty character string
                line = l.replace("\n","")
                line = line.replace("\t","")

                # Adds a new dictionary to the dictionary
                # with the key of the newest header (title)
                clean_data[line] = {}
                # Defines the new title
                title = line

        # If the line is NOT empty
        elif l != "\n" and ("." in l) == True:
            
            # We replace the "\n" - newline prompt
            # and the "\t" - tab character
            # with an empty character string
            line = l.replace("\n","")
            line = line.replace("\t","")

            # Make dictionaries inside the dictionary
            # of cleansed data

            # If it's a question, it will start with a
            # number, if it's a response, it will start
            # with a letter
            if line[0] in "0123456789":
                # Define the key for the dictionary
                question = line
                # Add a list to store the responses for the question
                clean_data[title][question]=[]

            elif line[0] in alphabet:
                # It's kept in the list of data
                # in the most recently added sub-section
                # of questions in the dictionary
                clean_data[title][question].append(line)
        
        # Else there is an error, which
        # will be displayed to the command prompt
        else:
            print(f"An error has occured on line {line_num}: {l}")
        # Increases the counter by 1, for 
        # every iteration completed
        line_num += 1

    # Empty string to store the SQL command
    SQL_Command = ""
    # Empty string to store the SQL command for question_groups table
    SQL_Command_Groups = ""

    # Now we have removed empty lines, we can
    # organise it all into their respective 
    # sub-sections.
    for title in clean_data:
        # Group ID counter - increases once per group completed
        Group_ID = 1
        
        response_string2 = f"\nINSERT INTO question_groups_tb (Question_Group) VALUES (\"{title}\");"
        SQL_Command_Groups += response_string2
        for question in clean_data[title]:
            # Format of SQL table
            "Question_ID","Question_Number","Question_Section_Letter","fk_Question_Group_ID","Response_Type","Question_Text"
            # Seperate each part :)
            # Split the question to seperate the number
            Q_List = question.split(".")
            # The question number
            Q_Num = int(Q_List[0])
            text = "".join(question[1:])

            # Insert the actual question into the table
            response_string = f"\nINSERT INTO questions_tb (Question_Number,fk_Question_Group_ID,Question_Text) VALUES {Q_Num,Group_ID,text};"
            SQL_Command += response_string
            

            # Iterate through all responses to the question
            for response in clean_data[title][question]:
                # Split the response to seperate the
                # letter and the text
                Q = response.split(".")
                # The question section letter
                Q_Section_Letter = Q[0]
                # The question text
                # merges the split list into a single string
                text = "".join(Q[1:])

                # Group ID is from a different table
                # corresponding to the section for that
                # question

                # The response type, eg radio input, text
                # or tick box
                # If "__" is in the question, the response type is input
                if "_" in response:
                    Response_Type = "input"
                # Otherwise assume Radio - can be changed later
                elif "_" not in response:
                    # If there is the word "any", then the question will allow multiple responses, otherwise only one response is accepted
                    if "any" in response.lower():
                        Response_Type = "checkbox"
                    else:
                        Response_Type = "radio"
                else:
                    input()

                # Format for SQL
                #"INSERT INTO table_name (column_names) VALUES ()""
                
                response_string = f"INSERT INTO questions_tb (Question_Number,Question_Section_Letter,fk_Question_Group_ID,Response_Type,Question_Text) VALUES {Q_Num,Q_Section_Letter,Group_ID,Response_Type,text};\n"
                SQL_Command += response_string
                
        Group_ID += 1
    input()
    print(SQL_Command)
    f= open("SQL code questions_tb.txt","w", encoding="utf-8")
    f.write(SQL_Command)
    f.close()
    f= open("SQL code questions_groups_tb.txt","w", encoding="utf-8")
    f.write(SQL_Command_Groups)
    f.close()









    return SQL_Command

# Displays the contents of the file
print(clean(f))
