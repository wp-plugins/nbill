<?php
/**
* Greek Language file for Paypal gateway
* @version 2
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Administrator
define("NBILL_PAYPAL_DESC", "Ενοποίηση Paypal - προεπιλεγμένη επέκταση υπηρεσίας πληρωμών που παρέχεται με το " . NBILL_BRANDING_NAME . " (%sΚάντε κλικ εδώ%s για να εγγραφείτε σε έναν εμπορικό λογαριασμό Paypal).");
define("NBILL_PAYPAL_EMAIL", "Διεύθυνση E-Mail Paypal");
define("NBILL_PAYPAL_EMAIL_HELP", "Εισάγετε την διεύθυνση e-mail με την οποία εγγραφήκατε στο Paypal, και στην οποία θέλετε να οδηγούνται οι πληρωμές.");
define("NBILL_PAYPAL_REATTEMPT", "Νέα Απόπειρα σε Αποτυχία");
define("NBILL_PAYPAL_REATTEMPT_HELP", "Εάν το Paypal θα πρέπει να δέχεται ξανά την πληρωμή (έως δύο ακόμη φορές) αν η πρώτη απόπειρα αποτύχει (Εισάγετε '" . NBILL_YES . "' για νέα προσπάθεια, ή '" . NBILL_NO . "' για ακύρωση στην πρώτη αποτυχία).");
define("NBILL_PAYPAL_SANDBOX", "Χρήση του Sandbox;");
define("NBILL_PAYPAL_SANDBOX_HELP", "Εισάγετε '" . NBILL_YES . "' εδώ για να χρησιμοποιήσετε το Paypal sandbox για έλεγχο της ενοποίησης.  Εισάγετε '" . NBILL_NO . "' για να χρησιμοποιήσετε το πραγματικό σύστημα Paypal.");
define("NBILL_PAYPAL_IPN_URL", "IPN URL");
define("NBILL_PAYPAL_IPN_URL_HELP", "Το URL στο οποίο το Paypal θα πρέπει να στέλνει μηνύματα Άμεσης Ειδοποίησης Πληρωμής ('Instant Payment Notification').");
define("NBILL_PAYPAL_INCLUDE_BREAKDOWN", "Συμπερίληψη Αναλύσεων; (Δεν συνιστάται!)");
define("NBILL_PAYPAL_INCLUDE_BREAKDOWN_HELP", "Εάν θα συμπεριλαμβάνεται ή όχι μια ανάλυση καλαθιού αγορών για εφ' άπαξ πληρωμές (ώστε οι ποσότητες να διοχετεύονται στο Paypal αντί να καταλήγει στην προεπιλογή 1). Ορίστε το σε '1' για να στείλετε την ανάλυση στο Paypal, ή σε '0' για να στείλετε απλώς τα σύνολα. ΣΗΜΕΙΩΣΗ: Λόγω των περιορισμών στο Paypal, δεν μπορείτε να χρησιμοποιήσετε αυτό το χαρακτηριστικό αν έχετε ορίσει έξοδα αποστολής, καθώς το Paypal τα αγνοεί ακόμη κι αν διοχετεύονται στο Paypal σωστά. Εάν χρησιμοποιήσετε αυτό το χαρακτηριστικό, ΔΕΝ θα πρέπει επίσης να έχετε ορισμένα οποιαδήποτε ποσοστά ΦΠΑ ή αποστολής στο προφίλ σας Paypal, αφού αυτό το χαρακτηριστικό δεν μπορεί να παραβλέψει τις ρυθμίσεις στο προφίλ σας. Επιπλέον, αν έχετε κάποιες εκπτώσεις ή αρνητικές τιμές κάθε είδους, το Paypal δεν θα δεχθεί αυτούς τους λογαριασμούς έτσι η συνολική πληρωμή θα είναι λάθος. Συνεπώς στις περισσότερες περιπτώσεις είναι προτιμότερο να αποφύγετε την χρήση του!");
define("NBILL_PAYPAL_ADD_DEBUG_INFO", "Προσθήκη Πληροφοριών Ανίχνευσης Λαθών;");
define("NBILL_PAYPAL_ADD_DEBUG_INFO_HELP", "Εάν θα μεταφέρονται ή όχι τα περιεχόμενα ορισμένων μεταβλητών σε οποιεσδήποτε αναφορές σφαλμάτων αποστέλλονται με e-mail σε έναν διαχειριστή. Αυτό θα μπορούσε να αφορά στην αποστολή ευαίσθητων ενδεχομένως πληροφοριών μέσω e-mail, συνεπώς χρησιμοποιήστε το μόνο αν απαιτείται για λόγους αντιμετώπισης προβλημάτων.");

//Front End / Processing
define("NBILL_PAYPAL_ERR_HTTP", "Σφάλμα HTTP");
define("NBILL_PAYPAL_ERR_AMOUNT_MISMATCH", "ΣΦΑΛΜΑ - Αναντιστοιχία ποσών (το ποσό που καταβλήθηκε ήταν διαφορετικό από το αναμενόμενο ποσό)");
define("NBILL_PAYPAL_ERR_MAIL_MISMATCH", "ΣΦΑΛΜΑ - Αναντιστοιχία Mail (η πληρωμή δεν έγινε στην σωστή διεύθυνση e-mail - θα μπορούσε να είναι μία απάτη πληρωμής");
define("NBILL_PAYPAL_ERR_DUPLICATE_NOTIFICATION", "Ειδοποίηση Επανάληψης");
define("NBILL_PAYPAL_ERR_INVALID_TX", "Άκυρη Συναλλαγή - Έχει ληφθεί μια απόκριση, αλλά το " . NBILL_BRANDING_NAME . " δεν μπόρεσε να επαληθεύσει ότι προέρχεται από το Paypal. Αυτό θα μπορούσε να οφείλεται σε προβλήματα σύνδεσης με το Paypal για την επαλήθευση της αντίστροφης κλήσης, ή επειδή κάποιος 'ύποπτος' στο web (π.χ. κάποιος που καραδοκεί σε μηχανισμούς αναζήτησης - bot) έχει βρει την σύνδεση αντίστροφης κλήσης και προσπαθεί να την καταχωρήσει (οπότε, προσπαθήστε προσθέτοντας γραμμή στο τέλος του αρχείου robots.txt στην ρίζα του λογαριασμού σας στέγασης, που θα περιέχει το κείμενο: Disallow: /index.php?option=" . NBILL_BRANDING_COMPONENT_NAME . "&action=gateway), ή (λιγότερο πιθανό) θα μπορούσε να οφείλεται στο ότι κάποιος προσπαθεί να προσποιηθεί ότι έχει γίνει μια πληρωμή ενώ αυτό δεν συμβαίνει. Εάν συνεχίζετε να παίρνετε αυτό το σφάλμα και οι γνήσιες αντίστροφες κλήσεις σας στο Paypal (callbacks) αποτυγχάνουν να υποβληθουν σε επεξεργασία, μπορείτε να απενεργοποιήσετε την επαλήθευση αντίστροφων κλήσεων στην σελίδα ρυθμίσεων της υπηρεσίας πληρωμών.");
define("NBILL_PAYPAL_SUBSCRIPTION_NO", "Αρ. Εγγραφής Paypal: ");

//Version 1.2.0
define("NBILL_PAYPAL_VERIFY_CALLBACK", "Επαλήθευση Αντίστροφης Κλήσης");
define("NBILL_PAYPAL_VERIFY_CALLBACK_HELP", "Εάν θα επαληθεύονται ή όχι οι αντίστροφες κλήσεις IPN (callbacks) με το Paypal για να διασφαλίζεται ότι είναι γνήσιες. Στις περισσότερες περιπτώσεις, αυτό θα πρέπει να παραμείνει στην προεπιλογή ('" . NBILL_YES . "'). Εάν τα τιμολόγιά σας δεν σημειώνονται ως εξοφλημένα, μπορείτε να προσπαθήσετε θέτοντας αυτή την επιλογή σε '" . NBILL_NO . "'.");

//Version 1.2.1
define("NBILL_PAYPAL_SUCCESS_URL", "URL Επιτυχίας");
define("NBILL_PAYPAL_SUCCESS_URL_HELP", "ΠΛΗΡΗΣ διεύθυνση ιστοτόπου (website) για επιστροφή του χρήστη μετά από επιτυχή πληρωμή. Εάν παραμείνει κενό, θα χρησιμοποιηθεί η τιμή από το προφίλ σας Paypal.");
define("NBILL_PAYPAL_FAILURE_URL", "URL Αποτυχίας");
define("NBILL_PAYPAL_FAILURE_URL_HELP", "ΠΛΗΡΗΣ διεύθυνση ιστοτόπου (website) για επιστροφή του χρήστη μετά από ανεπιτυχή πληρωμή. Εάν παραμείνει κενό, ο χρήστης θα παραμείνει στον τόπο Paypal.");

//Version 3.0.2
define("NBILL_PAYPAL_SSL_CIPHER", "SSL Cipher");
define("NBILL_PAYPAL_SSL_CIPHER_HELP", "Some server configurations require the SSL cipher to be specified in order for Paypal callback verification to work. Others require that the cipher not be specified. If orders are not being activated or invoices not being marked as paid when a Paypal payment is made, you can try setting this to TLSv1 or TLSv1.2 (or if it is already set, try deleting the value and leaving this setting blank).");

//Version 3.1.0
define("NBILL_PAYPAL_API_INFO_HELP", "<b>Pre-Approved Payments</b><p>If you want to allow your clients to set up a pre-approval with Paypal so that you can help yourself to payment for their invoices without them needing to manually pay each time, you will need to fill in the following settings. These settings are NOT needed for normal purchases and payment of invoices.</p>");
define("NBILL_PAYPAL_API_USE_SANDBOX", "Use Sandbox for API Calls");
define("NBILL_PAYPAL_API_USE_SANDBOX_HELP", "Whether or not to use the Paypal sandbox (test environment) for API calls to set up and take pre-approved payments. This setting does NOT affect normal Paypal payments, which have a separate sandbox setting, above.");
define("NBILL_PAYPAL_CONFIRM_SIGNUPS", "E-mail admin for new signups?");
define("NBILL_PAYPAL_CONFIRM_SIGNUPS_HELP", "Whether or not to send a confirmation e-mail to an administrator whenever a client signs up for a Paypal pre-approval.");
define("NBILL_PAYPAL_DEFAULT_PREAPP_THANKS", "Pre-approval Thanks");
define("NBILL_PAYPAL_DEFAULT_PREAPP_THANKS_VALUE", "Thank you for authorising us to charge your Paypal account. Your instruction has been received successfully.");
define("NBILL_PAYPAL_DEFAULT_PREAPP_THANKS_HELP", "Thank you message to show the client after they have successfully set up a Paypal pre-approval.");
define("NBILL_PAYPAL_PREAPP_SUCCESS_URL", "Pre-approval Setup Success URL");
define("NBILL_PAYPAL_PREAPP_SUCCESS_URL_HELP", "Optional URL to redirect to after successfully creating a new pre-approval (if a value is provided here, the thank you message defined above will be ignored).");
define("NBILL_PAYPAL_PREAPP_FAILURE_URL", "Pre-approval Setup Failure URL");
define("NBILL_PAYPAL_PREAPP_FAILURE_URL_HELP", "Optional URL to redirect to if the pre-approval creation process fails or is cancelled.");
define("NBILL_PAYPAL_DEFAULT_MAX_AMOUNT", "Default Max Amount");
define("NBILL_PAYPAL_DEFAULT_MAX_AMOUNT_HELP", "When setting up pre-approved payments, enter the default value to use as the maximum payment amount for any one payment (can be overridden when inviting clients to sign up for pre-approval). When multiplied by the maximum number of payments (below), this cannot exceed 2,000 USD unless previously agreed with Paypal.");
define("NBILL_PAYPAL_DEFAULT_PAYMENT_COUNT", "Default Max Number of Payments");
define("NBILL_PAYPAL_DEFAULT_PAYMENT_COUNT_HELP", "For pre-approved payments, specify the maximum number of payments to be made per year.");
define("NBILL_PAYPAL_API_SANDBOX_USER", "Sandbox API Username");
define("NBILL_PAYPAL_API_SANDBOX_USER_HELP", "Classic API Username for your Paypal SANDBOX business account. If using pre-approved payments with the Paypal sandbox (test environment), you must obtain classic API credentials for your SANDBOX Paypal business account. Details on how to obtain API credentials are available on the <a target_\"_blank\" href=\"https://developer.paypal.com/docs/classic/api/apiCredentials/\">Paypal Developer website</a> (but perform these steps in your SANDBOX account, not your live account).");
define("NBILL_PAYPAL_API_SANDBOX_PASSWORD", "Sandbox API Password");
define("NBILL_PAYPAL_API_SANDBOX_PASSWORD_HELP", "Classic API Password for your Paypal SANDBOX business account (see help of Sandbox API Username setting, above)");
define("NBILL_PAYPAL_API_SANDBOX_SIGNATURE", "Sandbox API Signature");
define("NBILL_PAYPAL_API_SANDBOX_SIGNATURE_HELP", "Classic API Signature for your Paypal SANDBOX business account (see help of Sandbox API Username setting, above)");
define("NBILL_PAYPAL_API_SANDBOX_APPID", "Sandbox API AppID");
define("NBILL_PAYPAL_API_SANDBOX_APPID_HELP", "At the time of writing, you do not need your own AppID for sandbox use, you can just use the shared one: 'APP-80W284485P519543T'. For live use, you will need to apply to Paypal for an AppID (see below).");
define("NBILL_PAYPAL_API_USER", "API Username");
define("NBILL_PAYPAL_API_USER_HELP", "Classic API Username for your Paypal business account. If using pre-approved payments, you must obtain classic API credentials and an App ID for your Paypal business account. Details on how to obtain API credentials are available on the <a target_\"_blank\" href=\"https://developer.paypal.com/docs/classic/api/apiCredentials/\">Paypal Developer website</a>.");
define("NBILL_PAYPAL_API_PASSWORD", "API Password");
define("NBILL_PAYPAL_API_PASSWORD_HELP", "Classic API Password for your Paypal business account (see help of API Username setting, above)");
define("NBILL_PAYPAL_API_SIGNATURE", "API Signature");
define("NBILL_PAYPAL_API_SIGNATURE_HELP", "Classic API Signature for your Paypal business account (see help of API Username setting, above)");
define("NBILL_PAYPAL_API_APPID", "API AppID");
define("NBILL_PAYPAL_API_APPID_HELP", "You must apply to Paypal for an AppID before you can use pre-approved payments. To do that, register at https://developer.paypal.com and select 'My Apps' from the dashboard (Note: you need to create a Classic API app, not a REST app).");define("NBILL_PAYPAL_GATEWAY_SETTINGS", "Paypal Settings");
define("NBILL_PAYPAL_PREAPP_INVOICES", "Pre-approved invoices awaiting payment");
define("NBILL_PAYPAL_PREAPP_CLIENTS", "Client Pre-approvals");
define("NBILL_PAYPAL_URL_ERROR", "Unable to construct URL for redirecting to Paypal");
define("NBILL_PAYPAL_FUNCTIONS_TITLE", "Paypal Pre-Approval Functions");
define("NBILL_PAYPAL_FUNCTIONS_INTRO", "From here you can manage pre-approvals and take payments for outstanding invoices directly. You can take payments for any unpaid invoices for clients who have given authorisation using the 'Invoices' tab. To invite a client to make a pre-approval instruction, click on the 'Clients' tab, then click on the 'New' toolbar button.");
define("NBILL_PAYPAL_FUNCTIONS_INVOICES", "Invoices");
define("NBILL_PAYPAL_FUNCTIONS_INVOICES_DESC", "Pre-approved invoices awaiting payment");
define("NBILL_PAYPAL_FUNCTIONS_INVOICES_INTRO", "Unpaid invoices for which credit card pre-approval already exists are listed here. Check the box(es) next to the invoices you want to collect payment for and click the 'Charge' toolbar button to take a payment.");
define("NBILL_PAYPAL_FUNCTIONS_CLIENTS", "Clients");
define("NBILL_PAYPAL_FUNCTIONS_CLIENTS_DESC", "Client Pre-approvals");
define("NBILL_PAYPAL_FUNCTIONS_CLIENT_INVITE", "Paypal Pre-approval Invitation for %s");
define("NBILL_PAYPAL_PREAPP_CLIENTS_INTRO", "This tab shows a list of all clients who have provided pre-approval for payments to be taken automatically (up to a specified limit). To invite a client to create a new pre-approval instruction, click the 'New' toolbar button (note, the instruction will not appear in the list below unless the client approves).");
define("NBILL_PAYPAL_PREAPP_RESOURCE_ID", "Pre-approval Resource ID");
define("NBILL_PAYPAL_PREAPP_DATE", "Date");
define("NBILL_PAYPAL_PREAPP_MAX_AMOUNT", "Max Amount");
define("NBILL_PAYPAL_PREAPP_PER", "Per");
define("NBILL_PAYPAL_PREAPP_STATUS", "Status");
define("NBILL_PAYPAL_INVITATION_SENT", "Invitation Sent %s");
define("NBILL_PAYPAL_INVITATION_RESEND", "Resend");
define("NBILL_PAYPAL_NEW_PREAPP_INTRO", "Click on a client name (or check the box next to multiple clients and click the 'invite' toolbar button) to invite them to authorise you to collect payments from them (this will open a new page where you can specify the parameters and wording of the invitation).");
define("NBILL_PAYPAL_PREAPP_INVITE_INTRO", "You can use this form to send the client an e-mail inviting them to sign up for pre-approved payments with Paypal. Specify the maximum amount you will need to debit for any one payment and the maximum number of payments you will need to take in a year (you will not be able to exceed this limit). The total amount taken in a year must not exceed 2,000 USD or equivalent (that is a Paypal rule). Pre-approvals are limited to one year unless you have specifically arranged otherwise with Paypal (if you do have permission to create open ended preapprovals, just set the maximum number of payments to 0 [zero]). You can also amend the email message below if required (but make sure you keep the payment link intact), and hit send.");
define("NBILL_PAYPAL_PREAPP_DESCRIPTION", "Description (optional)");
define("NBILL_PAYPAL_PREAPP_INVITE_SUBJECT", "Invitation to set up Paypal pre-approval with %s");
define("NBILL_PAYPAL_PREAPP_INVITE_BODY", "Dear %1\$s,\n\nWe would like to invite you to set up a Paypal pre-approval so that your payments to us can be taken directly from your Paypal account without needing to worry about making manual payments for each invoice. This should make your payments much more convenient. If you would like to set up pre-approved Paypal payments, please use the following link to authorise this with Paypal: \n\n%2\$s\n\nYou can login to Paypal at any time to view your transactions and cancel your pre-approval if required.\n\nWe are sure you will find pre-approved payments much easier and more convenient. Thank you.\n\nRegards,\n%3\$s");
define("NBILL_PAYPAL_INVITE_SANDBOX_WARNING", "WARNING! You are currently using the Paypal sandbox. This message should NOT be sent to a real client on a live system!");
define("NBILL_PAYPAL_PREAPP_LINK_PLACEHOLDER", "Click here to set up a Paypal pre-approval");
define("NBILL_PAYPAL_CLIENT", "Client");
define("NBILL_PAYPAL_CLIENT_PLACEHOLDER", "[Contact Name]");
define("NBILL_PAYPAL_DAYS", "Day(s)");
define("NBILL_PAYPAL_WEEKS", "Week(s)");
define("NBILL_PAYPAL_MONTHS", "Month(s)");
define("NBILL_PAYPAL_YEARS", "Year(s)");
define("NBILL_PAYPAL_MAX_AMOUNT", "Max Amount per Payment");
define("NBILL_PAYPAL_MAX_PAYMENT_COUNT", "Max Number of Payments");
define("NBILL_PAYPAL_PREAPP_INVITATION_SENT", "Pre-approval invitation sent to '%s' successfully!");
define("NBILL_PAYPAL_PREAPP_INVITATION_FAILED", "An error occurred whilst attempting to send the invitation to '%1\$s'. Please e-mail the following link to the client manually: %2\$s");
define("NBILL_PAYPAL_PREAPP_INVITATION_SAVE_FAILED", "An error occurred whilst attempting to save this invitation to '%1\$s'. The following error message was returned by the database: %2\$s");
define("NBILL_PAYPAL_NEW_PREAPP_NOTIFICATION_SUBJECT", "New Paypal Pre-Approval: %s");
define("NBILL_PAYPAL_NEW_PREAPP_NOTIFICATION_MESSAGE", "This is a notification from %1\$s to inform you that %2\$s has just completed a Pre-Approval with Paypal. You can now take payments from %2\$s without requiring a new authorisation each time (up to the limits imposed in the pre-approval instruction).");
define("NBILL_PAYPAL_PREAPP_CLIENT_NOT_FOUND", "Thank you for submitting a Paypal pre-approval. Unfortunately, we are having trouble identifying which account this pre-approval relates to. An administrator has been informed of the problem, but as we may have difficulty determining which client it relates to, please contact us to confirm receipt of your instruction.");
define("NBILL_PAYPAL_PREAPP_FAILED", "Thank you for submitting a Paypal pre-approval. Unfortunately, we were unable to save the details of this instruction. An administrator has been informed of the problem.");
define("NBILL_PAYPAL_NEW_PREAPP_FAILED_NOTIFICATION_NAMED", "Paypal Pre-Approval FAILED: %s");
define("NBILL_PAYPAL_NEW_PREAPP_FAILED_NOTIFICATION", "Paypal Pre-Approval FAILED");
define("NBILL_PAYPAL_NEW_PREAPP_FAILED_NOTIFICATION_MESSAGE_NAMED", "This is a notification from %1\$s to inform you that %2\$s has just completed a Pre-Approval with Paypal, however, the Paypal gateway script was unable to save the details in your database. You will not be able to take payments for this client. If any error message was reported by the database it will be shown below:\n\n");
define("NBILL_PAYPAL_NEW_PREAPP_FAILED_NOTIFICATION_MESSAGE", "This is a notification from %1\$s to inform you that a client (identity unknown) has just completed a Pre-Approval with Paypal, however, the Paypal gateway script was unable to save the details in your database. You will not be able to take payments for this client. If any error message was reported by the database it will be shown below:\n\n");
define("NBILL_PAYPAL_API_ERR", "A problem occurred whilst attempting to process your request. The following error was returned: %s.");
define("NBILL_PAYPAL_PAYABLE_INVOICES", "Payable Invoices");
define("NBILL_PAYPAL_PAYABLE_INVOICES_INTRO", "These are unpaid or part paid invoices for clients who have set up a pre-approval. You can therefore initiate a request for payment from the client's Paypal account without needing to involve the client. The client will get notification from Paypal that a payment has been taken. Check the box(es) next to the invoice(s) you want to collect payment for, and click the Process toolbar button.");
define("NBILL_PAYPAL_INVOICE_PENDING", "A payment is already pending for this invoice");
define("NBILL_PAYPAL_TB_PROCESS", "Process");
define("NBILL_PAYPAL_TB_INVITE", "Invite");
define("NBILL_PAYPAL_INVOICE", "Invoice");
define("NBILL_PAYPAL_PAYABLE_INVOICES_PROCESS_INTRO", "If you want to take a partial payment for any of these invoices, please adjust the amount(s) below. Click Start to begin processing the invoices listed below. You can abort the process at any time by clicking Cancel.");
define("NBILL_PAYPAL_START", "Start");
define("NBILL_PAYPAL_CANCEL", "Cancel");
define("NBILL_PAYPAL_INVOICE_NO", "Invoice Number");
define("NBILL_PAYPAL_AMOUNT", "Amount");
define("NBILL_PAYPAL_STATUS", "Status");
define("NBILL_PAYPAL_STATUS_NONE", "Not yet processed");
define("NBILL_PAYPAL_STATUS_SUCCESS", "Success");
define("NBILL_PAYPAL_STATUS_SUCCESS_ALT", "Paypal payment taken successfully");
define("NBILL_PAYPAL_STATUS_FAILURE", "Failure");
define("NBILL_PAYPAL_STATUS_FAILURE_ALT", "Paypal payment could not be taken: %s");
define("NBILL_PAYPAL_FAILURE_NO_RESPONSE", "No response from server");
define("NBILL_PAYPAL_STATUS_ABORTED", "Aborted");
define("NBILL_PAYPAL_STATUS_ABORTED_ALT", "Potential conflict with an existing payment - no action taken.");
define("NBILL_PAYPAL_PROCESSING", "Processing...");
define("NBILL_PAYPAL_PROCESS_COMPLETE", "Process Complete - please check the status of each invoice to verify success (hover over status icon for more information).");
define("NBILL_PAYPAL_PROCESS_CANCELLED", "Process ABORTED. Some invoices have not been processed - please check the status of each invoice (hover over status icon for more information). To resume again, click Start.");
define("NBILL_PAYPAL_NO_INVOICES_SELECTED", "Please check the box next to one or more invoices before you click on the 'Process' button");
define("NBILL_PAYPAL_NO_VALID_INVOICES_SELECTED", "None of the selected invoices can be processed at this time, as there are already pending transactions awaiting completion.");
define("NBILL_PAYPAL_MULTIPLE_RECIPIENTS", "Multiple Recipients");
define("NBILL_PAYPAL_CLIENT_FILTER", "Filter");
define("NBILL_PAYPAL_FILTER_UNINVITED", "Uninvited clients only");
define("NBILL_PAYPAL_FILTER_UNAUTHORISED", "All clients without pre-approvals (invited or not)");
define("NBILL_PAYPAL_FILTER_UNACCEPTED", "Only those invited but not accepted");
define("NBILL_PAYPAL_FILTER_AUTHORISED", "Only clients with pre-approvals");
define("NBILL_PAYPAL_FILTER_ALL", "All clients");

//Processing
define("NBILL_PAYPAL_PAYMENT_FAILED", "An attempt to take payment by Paypal pre-approval for the following transaction was unsuccessful. This might be due to insufficient funds in the account.");
define("NBILL_PAYPAL_SUBSCRIPTION_ENDED", "The status of the following Paypal subscription is now: '%s'. No further payments will be taken for this subscription unless a new instruction is provided.");
define("NBILL_PAYPAL_PREAPP_ENDED", "The status of the following Paypal pre-approval is now: '%s'. No further payments can be taken for this client unless a new pre-approval instruction is provided.");
define("NBILL_PAYPAL_EXPIRED", "Expired");
define("NBILL_PAYPAL_CANCELLED", "Cancelled");
define("NBILL_PAYPAL_FE_UNKNOWN_FUNCTION", "Sorry, the link you used to reach this page is not working! If you clicked a link in an e-mail, please try copying and pasting the link into your browser address bar instead.");
define("NBILL_PAYPAL_FE_INVITATION_NOT_FOUND", "Sorry, Paypal pre-approval invitation '%s' was not found!");
define("NBILL_PAYPAL_FE_INVITATION_HASH_MISMATCH", "Sorry, the link you used to reach this page is not valid (hash mismatch).");
define("NBILL_PAYPAL_SIGNUP_NOTIFICATION", "Paypal Pre-Approval Notification");
define("NBILL_PAYPAL_SIGNUP_NOTIFICATION_MSG", "A client has just set up a pre-approval with Paypal.\n\n");
define("NBILL_PAYPAL_EXPENDITURE_FOR", "Transaction Fees");
define("NBILL_PAYPAL_PREAPP_NOT_APPROVED", "A pre-approval request was processed (ID: %s), but the preapproval was NOT approved. No action has been taken.");
define("NBILL_PAYPAL_PREAPP_INVITATION_NOT_FOUND", "A pre-approval request was processed, but the preapproval invitation (ID: %s) could not be found. No action has been taken.");
define("NBILL_PAYPAL_PREAPP_NOT_SAVED", "A pre-approval request was processed, but could not be saved (error: %1\$s).");