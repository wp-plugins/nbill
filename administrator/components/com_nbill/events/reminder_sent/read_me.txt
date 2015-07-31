To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
reminder_sent

PARAMS:
["id"] => int (ID of the reminder record - you can look up the rest of the details on the #__nbill_reminders table)
["to"] => string (E-mail address that the reminder was sent to)
["message"] => string (Main body of the e-mail, containing the reminder text)