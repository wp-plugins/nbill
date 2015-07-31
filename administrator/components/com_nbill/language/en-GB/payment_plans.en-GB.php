<?php
/**
* Language file for the Payment Plans feature
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
* 
* @access private* 
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

//Payment Plans
define("NBILL_PAYMENT_PLANS_TITLE", "Payment Plans");
define("NBILL_PAYMENT_PLANS_INTRO", "Payment plans allow you to define whether items with a one-off payment frequency (ie. anything that is not an ongoing subscription) can be paid for in more than one installment, and if so, what the rules are for making payments (eg. whether a deposit is required, installment frequency, etc). Not all payment gateways support all the features of payment plans. Some payment gateways might not support payment plans at all (eg. if they do not allow recurring payments or a fixed number of installments).");
define("NBILL_PAYMENT_PLAN_NAME", "Payment Plan Name");
define("NBILL_PAYMENT_PLAN_TYPE", "Payment Plan Type");
define("NBILL_PAYMENT_PLAN_PERCENTAGE", "Deposit Percentage");
define("NBILL_EDIT_PAYMENT_PLAN", "Edit Payment Plan");
define("NBILL_PAYMENT_PLAN_NAME_REQUIRED", "Please enter a name for this payment plan.");
define("NBILL_NEW_PAYMENT_PLAN", "New Payment Plan");
define("NBILL_PAYMENT_PLAN_DETAILS", "Payment Plan Details");
define("NBILL_INSTR_PAYMENT_PLAN_NAME", "Enter a descriptive name for this payment plan that will help you easily identify what it is for.");
define("NBILL_PAYMENT_PLAN_TYPE", "Plan Type");
define("NBILL_INSTR_PAYMENT_PLAN_TYPE", "Select the type of payment plan you require.<ul><li>'Payment Up Front' is the default action where the full amount owing is paid immediately.</li><li>'Installments' splits the amount owed into regular payments at intervals you define below.</li><li>'Deposit Plus Final Payment' takes a fixed amount or percentage immediately and allows the user to choose when to pay the balance (no further amount is taken automatically).</li><li>'Deposit Plus Installments' takes a fixed amount or percentage immediately, with the balance split into regular payments at intervals you define below (the initial deposit payment is classed as an installment).</li><li>'Deposit then User Controlled' takes a fixed amount or percentage immediately and then changes to 'user controlled' for the balance to be paid (in multiple partial payments if required) as and when the customer wishes (no further amount is taken automatically).</li><li>'Deferred Payment' does not take any payment, and just waits for the user to pay when they are ready.</li><li>'User Controlled' allows the user to choose how much to pay up-front (if anything), and allows them to pay the rest (in multiple partial payments if required) when they are ready.</li></ul>");
define("NBILL_INSTR_PAYMENT_PLAN_PERCENTAGE", "Percentage required for the deposit (ie. the first payment) - don't include the percent sign. (eg. for 10%, just enter 10). This setting has no effect unless the plan type includes a deposit.");
define("NBILL_PAYMENT_PLAN_AMOUNT", "Amount");
define("NBILL_INSTR_PAYMENT_PLAN_AMOUNT", "Fixed amount required for the deposit (ie. the first payment - only applies if percentage is 0, and the plan type includes a deposit).");
define("NBILL_PAYMENT_PLAN_AMOUNT_REQUIRED", "Please specify either a percentage or an amount.");
define("NBILL_PAYMENT_PLAN_INSTALLMENT_FREQUENCY", "Installment Payment Frequency");
define("NBILL_INSTR_PAYMENT_PLAN_INSTALLMENT_FREQUENCY", "After the initial deposit, indicate how often to take the installments. This setting has no effect unless the plan type includes installments.");
define("NBILL_PAYMENT_PLAN_NO_OF_INSTALLMENTS", "Number of Installments");
define("NBILL_INSTR_PAYMENT_PLAN_NO_OF_INSTALLMENTS", "How many payments to take (the amount of each installment will be calculated based on the total amount left to pay divided by the number of installments you define here. If the amount cannot be divided into exactly equal installments, the initial payment amount will be increased or decreased accordingly). This setting has no effect unless the plan type includes installments.");
define("NBILL_PAYMENT_PLAN_DURATION", "Deferred Period");
define("NBILL_INSTR_PAYMENT_PLAN_DURATION", "If the client does not have to pay anything up-front, you can indicate here how long to defer the first payment for. Unless you select 'Indefinitely', the client will still be directed the the payment gateway to schedule their payment(s). NOTE: Not all payment gateways support deferrals (or 'free trials'). This setting has no effect unless the plan type includes a deferred period.");
define("NBILL_PAYMENT_PLAN_CANNOT_DELETE", "You cannot delete a payment plan that is being used as the default plan for quotes, orders, or invoices.");
define("NBILL_PAYMENT_PLAN_QUOTE_DEFAULT", "Quote Default");
define("NBILL_INSTR_PAYMENT_PLAN_QUOTE_DEFAULT", "Whether or not to make this payment plan the default for new Quote records. If you set this to 'yes', any other payment plan currently marked as the default will also be amended to 'no'.");
define("NBILL_PAYMENT_PLAN_INVOICE_DEFAULT", "Invoice Default");
define("NBILL_INSTR_PAYMENT_PLAN_INVOICE_DEFAULT", "Whether or not to make this payment plan the default for new Invoice records. If you set this to 'yes', any other payment plan currently marked as the default will also be amended to 'no'.");
define("NBILL_PLAN_DEFAULT_YES", "Yes");
define("NBILL_PLAN_DEFAULT_NO", "No");