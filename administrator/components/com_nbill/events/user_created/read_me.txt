To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
user_created

PARAMS:
["id"] => int (ID of the newly created user record - you can look up the rest of the details (if not provided here) on the #__users table)
["username"] => string (The new user's username)
["email"] => string (The new user's e-mail address)