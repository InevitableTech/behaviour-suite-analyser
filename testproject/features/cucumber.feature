Feature: Registration 
Background: 
Given user on the homepage  
And user follows "Sign in"  

@regression  
    Scenario: Create a New User 
When user fills "registration email textbox" with "chitrali.sharma27@gmail.com"  
And user clicks "create an account button"  
And user enters the following details 
| First Name | Chitrali| 
| Last Name | Sharma| 
| Password | Inquiry@1234 | 
| Date | 17| | Month | 02| | Year | 1992 |  
And user clicks "register button"

Scenario: User does not follow form validations
 When user enters wrong characters
  Then error message displayed with invalid password
And user returns back on registration page

@regression @smoke
Scenario: Verification of Login Function  
Given user on the Login Page
And user enters "email address" with "chitrali.sharma27@gmail.com" 
And user enters "password" with "Inquiry@1234"  
And user click "log in" button
Then user should see "My Account" 

Scenario: Unsuccessful login
Given user on the Login Page
And user enters "email address" with "chitrali.sharma27@gmail.com" 
And user enters "password" with "qsder@1234"  
And user clicks "login" button
Then error message displayed with wrong password
And user returns back on login page