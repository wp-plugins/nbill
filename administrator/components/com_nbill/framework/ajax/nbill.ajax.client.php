<?php
/**
* Output of Javascript for supporting AJAX functions
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');
nbf_common::load_language("ajax");
?>

<script type="text/javascript">
var http_obj = false; //Asynchronous
var s_http_obj = false; //Synchronous
var ajax_callback = null;
var wait_msg = null;

function create_http_obj()
{
    var xmlHttpReq = false;
    if (window.XMLHttpRequest)
    {
        xmlHttpReq = new XMLHttpRequest();
    }
    else if (window.ActiveXObject)
    {
        try
        {
            xmlHttpReq = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                xmlHttpReq = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                xmlHttpReq = false;
            }
        }
    }
    return xmlHttpReq;
}

function submit_ajax_request(task, parameters, callback_function, show_wait, wait_message, wait_top)
{
    var wait_msg = null;

    if (wait_top == null)
    {
        wait_top = '300';
    }
    if (wait_message == null)
    {
        wait_message = '<?php echo NBILL_AJAX_PLEASE_WAIT; ?>';
    }

  http_obj = create_http_obj();

    if (http_obj == false)
    {
        alert('<?php echo NBILL_AJAX_NOT_SUPPORTED; ?>');
    }
    else
    {
        ajax_callback = callback_function;
        if (show_wait)
        {
            show_wait_message(wait_top, wait_message);
        }
        http_obj.open('POST', '<?php echo (defined('NBILL_ADMIN') ? nbf_cms::$interop->admin_popup_page_prefix : nbf_cms::$interop->site_popup_page_prefix); ?>&hide_billing_menu=1&action=ajax&task=' + task, true);
        var http_timeout = setTimeout(function(){if (http_obj){http_obj.abort();}clearTimeout(http_timeout);ajax_callback('');}, 120000);
        http_obj.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=<?php    echo nbf_cms::$interop->char_encoding; ?>");
        http_obj.onreadystatechange = function(){clearTimeout(http_timeout);receive_ajax_response()};
        http_obj.send(parameters);
    }
}

function submit_sjax_request(task, parameters)
{
    s_http_obj = create_http_obj();

    if (s_http_obj == false)
    {
        alert('<?php echo NBILL_AJAX_NOT_SUPPORTED; ?>');
    }
    else
    {
        s_http_obj.open('POST', '<?php echo (defined('NBILL_ADMIN') ? nbf_cms::$interop->admin_popup_page_prefix : nbf_cms::$interop->site_popup_page_prefix); ?>&hide_billing_menu=1&action=ajax&task=' + task, false);
        var s_http_timeout = setTimeout(function() {if (s_http_obj){s_http_obj.abort();}clearTimeout(s_http_timeout);ajax_callback('');}, 60000);
        s_http_obj.setRequestHeader("Content-type", "application/x-www-form-urlencoded; charset=<?php    echo nbf_cms::$interop->char_encoding; ?>");
        s_http_obj.send(parameters);
        if (document.getElementById('nbill_ajax_wait_message') && document.getElementById('nbill_ajax_wait_message').style)
        {
            document.getElementById('nbill_ajax_wait_message').style.display = 'none';
        }
        var result = s_http_obj.responseText;
        clearTimeout(s_http_timeout);
        return result;
    }
}

function receive_ajax_response()
{
  if (ajax_callback != null && http_obj != null && http_obj.readyState > 0)
    {
        if(http_obj.readyState == 4)
        {
            if (document.getElementById('nbill_ajax_wait_message') && document.getElementById('nbill_ajax_wait_message').style)
            {
                document.getElementById('nbill_ajax_wait_message').style.display = 'none';
            }
            ajax_callback(http_obj.responseText);
        }
    }
    else
    {
       ajax_callback('');
    }
}

function show_wait_message(wait_top, wait_message)
{
    if (wait_top == null)
    {
        wait_top = '300';
    }
    if (wait_message == null)
    {
        wait_message = '<?php echo NBILL_AJAX_PLEASE_WAIT; ?>';
    }
    if (document.getElementById('nbill_ajax_wait_message') == null)
    {
        wait_msg = document.createElement('div');
    }
    else
    {
        wait_msg = document.getElementById('nbill_ajax_wait_message');
    }
    wait_msg.id = 'nbill_ajax_wait_message';
    wait_msg.style.width = '200px';
    wait_msg.style.position = 'absolute';
    wait_msg.style.top = wait_top + 'px';
    wait_msg.style.left='50%';
    wait_msg.style.marginLeft = '-127px';
    wait_msg.style.width = '250px';
    wait_msg.style.height = '25px';
    wait_msg.style.padding = '12px';
    wait_msg.style.textAlign = 'center'
    wait_msg.style.color = '#ffffff';
    wait_msg.style.fontWeight = 'bold';
    wait_msg.style.fontSize = '18px';
    wait_msg.style.backgroundColor = '#999999';
    wait_msg.style.border = 'solid 2px #000000';
    wait_msg.innerHTML = wait_message;
    wait_msg.zIndex = '0';
    wait_msg.style.display = 'block';
    if (document.getElementById('nbill_ajax_wait_message') == null)
    {
        document.body.appendChild(wait_msg);
    }
}
</script>