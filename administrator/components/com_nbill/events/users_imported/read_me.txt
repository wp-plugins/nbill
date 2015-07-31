To execute a PHP script when this event is fired, just save a PHP file in this folder and it will be included when the event occurs. Files are executed in alphabetical order.

EVENT:
users_imported

PARAMS:
["user_ids"] => int[,int[,int...etc.]] (Comma-separated list of Joomla user IDs that have been imported as clients. You can look up the client details on the #__nbill_client table, using the user_id column as the key. If ALL Joomla users were imported, this parameter will just hold the string value "all" rather than the entire list of User IDs)
["client_ids"] => int[,int[,int...etc.]] (Comma-separated list of newly created or updated Client IDs. This is only populated if the import was done from a CSV file - if Joomla users were imported, the user_ids parameter, above, will be populated instead. If a client record is imported but the client already exists, it will still be listed here - the client details may have been updated by the import process)

NOTE:
The relevant user_created and client_created events will be fired in addition to this event, if applicable.