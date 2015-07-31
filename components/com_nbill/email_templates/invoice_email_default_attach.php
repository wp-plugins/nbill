<?php
//Ensure this file has been reached through a valid entry point
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo nbf_cms::$interop->char_encoding; ?>" />
<title><?php echo nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE'); ?></title>
<style type="text/css">
body, td
{
    font-family: Arial, Helvetica, sans-serif;
    font-size: 10pt;
}
</style>
</head>
<body>
    <table cellpadding="3" cellspacing="0" border="0">
        <tr>
            <?php if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/$vendor_id.gif") || file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/$vendor_id.png")) { ?><th align="left"><img src="<?php echo nbf_cms::$interop->live_site . "/" . nbf_cms::$interop->site_popup_page_prefix; ?>&action=show_image&file_name=vendors/<?php echo $vendor_id; if (file_exists(nbf_cms::$interop->nbill_fe_base_path . "/images/vendors/$vendor_id.gif")){echo ".gif";}else{echo ".png";} echo nbf_cms::$interop->public_site_page_suffix(); ?>" alt="<?php echo nbf_cms::$interop->site_name; ?> Logo" /></th><?php } ?>
            <th align="left"><h3><?php echo nbf_cms::$interop->site_name; ?> - <?php echo nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE'); ?></h3></th>
        </tr>
        <tr>
            <td align="left" colspan="2">
                <p><?php echo sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_GREETING'), $contact_name); ?></p>
                <p><?php echo nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_1_ATTACHED'); ?></p>
                <p><?php echo @$document->username ? sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2_USERNAME'), '<a href="' . nbf_cms::$interop->live_site . '">' . nbf_cms::$interop->live_site . '</a>', $document->username) : sprintf(nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_NEW_INVOICE_PAR_2'), '<a href="' . nbf_cms::$interop->live_site . '">' . nbf_cms::$interop->live_site . '</a>'); ?></p>
                <p><?php echo nbf_common::parse_translation($document->default_language, "email", 'NBILL_EM_REGARDS'); ?><br />
                <?php echo nbf_cms::$interop->site_name; ?></p>
            </td>
        </tr>
    </table>
</body>
</html>