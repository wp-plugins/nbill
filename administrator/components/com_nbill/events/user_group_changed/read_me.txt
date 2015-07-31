To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
user_group_changed

PARAMS:
["user_id"] => int (User ID of the User whose group assignment has been changed)
["new_level"] => int (Group ID (GID) of the newly assigned user group - the event is fired immediately before the group is changed, so you can still look up the old group(s) using the user_id)

NOTES:
This event is typically fired by the account expiry mambot when a user subscription expires, or when a user attains a new access level (eg. after purchasing a subscription product)