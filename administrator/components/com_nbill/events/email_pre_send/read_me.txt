To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
email_pre_send (Executes just before an email is sent. Params are passed in by reference, so you can intercept the email and change any of the parameters before the mail is sent.)

PARAMS:
["from"] => string (E-Mail address from which the e-mail will appear to have been sent)
["from_name"] => string (Name of sender)
["recipient"] => mixed (Array or string containing E-Mail address(es) of recipient)
["subject"] => string (E-Mail subject)
["body"] => string (Main text of the e-mail)
["mode"] => int (0 = Plain Text; 1 = HTML)
['cc'] => mixed (Array or string containing E-Mail address(es) for courtesy copies)
['bcc'] => mixed (Array or string containing E-Mail address(es) for blind courtesy copies)
['attachment'] => string (File name of attachment)
['abort'] => boolean (Whether or not to stop the e-mail from being sent)