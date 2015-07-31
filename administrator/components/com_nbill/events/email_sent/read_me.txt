To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
email_sent

PARAMS:
["from"] => string (E-Mail address from which the e-mail will appear to have been sent)
["from_name"] => string (Name of sender)
["recipient"] => string (E-Mail address of recipient)
["subject"] => string (E-Mail subject)
["body"] => string (Main text of the e-mail)