<?php
/**
* HTML output for translations
* @version 1
* @package nBill
* @copyright (C) 2015 Netshine Software Limited
* @license GPL v2 (LITE edition only. Standard Edition is NOT licensed under the GPL)
*
* @access private*
*/

//Ensure this file has been reached through a valid entry point (not always necessary eg. for class files, but included on every file to be safe!)
(defined('_VALID_MOS') || defined('_JEXEC') || defined('ABSPATH') || defined('NBILL_VALID_NBF')) or die('Access Denied.');

class nBillTranslation
{
    public static function showTranslations($tables)
    {
        ?>
        <table class="adminheading" style="width:100%">
        <tr>
            <th <?php echo str_replace(".gif", ".png", sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "translation")); ?>>
                <?php echo NBILL_TRANSLATION_TITLE; ?>
            </th>
        </tr>
        </table>

        <p align="left"><?php echo NBILL_TRANSLATION_INTRO; ?></p>

        <div class="rounded-table">
            <table class="adminlist">
                <tr>
                    <th class="title">
                        <?php echo NBILL_TRANSLATION_TABLE; ?>
                    </th>
                    <th class="title">
                        <?php echo NBILL_TRANSLATION_DESCRIPTION; ?>
                    </th>
                </tr>

                <?php
                foreach ($tables as $file_name=>$table)
                { ?>
                    <tr>
                        <td class="list-value">
                            <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=translation&task=edit_table&table=<?php echo $file_name; ?>" title="<?php echo NBILL_TRANSLATION_EDIT_TABLE; ?>"><?php echo $table->title; ?></a>
                        </td class="list-value">
                        <td>
                            <?php echo $table->description; ?>
                        </td>
                    </tr>
                    <?php
                } ?>
            </table>
        </div>
        <?php
    }

    public static function editTable($table_file, $table_name, $title, $rows)
    {
        ?>
        <table class="adminheading" style="width:100%">
        <tr>
            <th <?php echo str_replace(".gif", ".png", sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "translation")); ?>>
                <?php echo sprintf(NBILL_TRANSLATION_EDIT_TABLE_TITLE, $table_name); ?>
            </th>
        </tr>
        </table>

        <p align="left"><?php echo NBILL_TRANSLATION_EDIT_TABLE_INTRO; ?></p>

        <div class="rounded-table">
            <table class="adminlist">
                <tr>
                    <th class="selector">
                        <?php echo NBILL_ID; ?>
                    </th>
                    <th class="title">
                        <?php echo $title; ?>
                    </th>
                </tr>

                <?php
                foreach ($rows as $row)
                {
                    $row_title = trim(strip_tags($row->title));
                    if (strlen($row_title) > 150)
                    {
                        $row_title = substr($row_title, 0, 150) . "...";
                    }
                    ?>
                    <tr>
                        <td class="selector">
                            <?php echo $row->pk; ?>
                        </td>
                        <td class="list-value">
                            <a href="<?php echo nbf_cms::$interop->admin_page_prefix; ?>&action=translation&task=edit_row&table=<?php echo $table_file; ?>&row=<?php echo $row->pk; ?>" title="<?php echo NBILL_TRANSLATION_EDIT_ROW; ?>"><?php echo $row_title ? $row_title : NBILL_TRANSLATION_NO_TITLE; ?></a>
                        </td>
                    </tr>
                    <?php
                } ?>
            </table>
        </div>
        <?php
    }

    public static function editTranslation($table, $table_name, $row_name, $row, $display_columns, $languages, $default_language, $translation)
    {
        ?>
        <script language="javascript" type="text/javascript">
        function nbill_submit_task(task_name)
        {
            var form = document.adminForm;
            document.adminForm.task.value=task_name;
            document.adminForm.submit();
        }
        function language_selected()
        {
            var new_lang = document.getElementById('nbill_translate_language').value;
            <?php
            foreach ($languages as $language=>$value)
            {
                foreach ($display_columns as $key=>$value)
                {
                    ?>
                    document.getElementById('tr_<?php echo $language; ?>_<?php echo $key; ?>').style.display='none';
                    <?php
                }
            }
            foreach ($display_columns as $key=>$value)
            {
                ?>
                document.getElementById('tr_' + new_lang + '_<?php echo $key; ?>').style.display='';
                <?php
            }
            ?>
        }
        </script>
        <table class="adminheading" style="width:auto;">
        <tr>
            <th <?php echo str_replace(".gif", ".png", sprintf(NBILL_ADMIN_IMAGE, nbf_cms::$interop->nbill_site_url_path, "translation")); ?>>
                <?php echo NBILL_BRANDING_NAME . NBILL_BRANDING_TRADEMARK_SYMBOL; ?>:
                <?php echo NBILL_EDIT_TRANSLATION . " - $table_name: '$row_name'"; ?>
            </th>
        </tr>
        </table>

        <div class="nbill-message-ie-padding-bug-fixer"></div>
        <?php
        if (nbf_common::nb_strlen(nbf_globals::$message) > 0)
        {
            echo "<div class=\"nbill-message\">" . nbf_globals::$message . "</div>";
        } ?>

        <form action="<?php echo nbf_cms::$interop->admin_page_prefix; ?>" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="<?php echo NBILL_BRANDING_COMPONENT_NAME; ?>" />
        <input type="hidden" name="action" value="translation" />
        <input type="hidden" name="task" value="edit_row" />
        <input type="hidden" name="box_checked" value="0" />
        <input type="hidden" name="hidemainmenu" value="0">
        <input type="hidden" name="table" value="<?php echo $table; ?>" />
        <input type="hidden" name="row" value="<?php echo $row->pk; ?>" />

        <div class="rounded-table">
            <table width="100%" border="0" cellspacing="0" cellpadding="3" class="adminform">
            <tr>
                <th class="title"><?php echo NBILL_TRANSLATION_SELECT_LANG; ?></th>
                <th colspan="4">
                    <?php
                    $lang_list = array();
                    foreach ($languages as $key=>$value)
                    {
                        $lang_list[] = nbf_html::list_option($key, $value);
                    }
                    echo nbf_html::select_list($lang_list, 'language', 'id="nbill_translate_language" onclick="language_selected();"', $default_language);
                    ?>
                </th>
            </tr>

            <tr><td colspan="4">&nbsp;</td></tr>

            <tr>
                <th class="title"><?php echo NBILL_TRANSLATION_COLUMN; ?></th>
                <th colspan="2" class="title"><?php echo NBILL_TRANSLATION_SOURCE; ?></th>
                <th class="title"><?php echo NBILL_TRANSLATION_TARGET; ?></th>
                <th class="title"><?php echo NBILL_TRANSLATION_PUBLISHED; ?></th>
            </tr>

            <?php
            foreach ($display_columns as $key=>$value)
            {
                foreach ($languages as $language=>$language_value)
                {
                    $this_published = 1;
                    $this_value = '';
                    if (array_key_exists($language, $translation))
                    {
                        if (array_key_exists($key, $translation[$language]))
                        {
                            if (array_key_exists('published', $translation[$language][$key]))
                            {
                                $this_published = intval($translation[$language][$key]['published']);
                            }
                            if (array_key_exists('value', $translation[$language][$key]))
                            {
                                $this_value = $translation[$language][$key]['value'];
                            }
                        }
                    }
                    ?>
                    <tr id="tr_<?php echo $language; ?>_<?php echo $key; ?>"<?php if ($language != $default_language) {echo ' style="display:none;"';} ?>>
                        <td class="list-value"><?php echo $value; ?></td>
                        <td class="list-value">
                            <div class="translation-source" id="source_<?php echo $language; ?>_<?php echo $key; ?>"><?php echo $row->$key; ?></div>
                        </td>
                        <td class="list=value">
                            <input type="button" class="button btn" value="<?php echo NBILL_TRANSLATION_COPY; ?>" onclick="if(document.getElementById('translation_<?php echo $language; ?>_<?php echo $key; ?>').value.length==0 || confirm('<?php echo sprintf(NBILL_TRANSLATION_OVERWRITE, $value); ?>')){document.getElementById('translation_<?php echo $language; ?>_<?php echo $key; ?>').value='<?php echo htmlentities(str_replace("'", "\\'", str_replace('"', '\"', str_replace("\r", "", str_replace("\n", "\\n", $row->$key)))), ENT_QUOTES | 0, nbf_cms::$interop->char_encoding); ?>';}" />
                        </td>
                        <td class="list-value"><textarea class="translation-target" name="translation_<?php echo $language; ?>_<?php echo $key; ?>" id="translation_<?php echo $language; ?>_<?php echo $key; ?>"><?php echo $this_value; ?></textarea></td>
                        <td  class="selector">
                            <input type="hidden" name="published_<?php echo $language; ?>_<?php echo $key; ?>" id="published_<?php echo $language; ?>_<?php echo $key; ?>" value="<?php echo $this_published; ?>" />
                            <?php
                            $yes_img = nbf_cms::$interop->nbill_site_url_path . '/images/icons/tick.png';
                            $no_img = nbf_cms::$interop->nbill_site_url_path . '/images/icons/cross.png';
                            $yes_alt = NBILL_TRANSLATION_PUBLISHED_YES;
                            $no_alt = NBILL_TRANSLATION_PUBLISHED_NO;
                            $img = $this_published ? $yes_img : $no_img;
                            $alt = $this_published ? $yes_alt : $no_alt;
                            ?>
                            <a href="#" title="<?php echo $alt; ?>" onclick="var this_published = document.getElementById('published_<?php echo $language; ?>_<?php echo $key; ?>');this_published.value = this_published.value == 1 ? this_published.value = 0 : this_published_value = 1;var this_img = document.getElementById('img_published_<?php echo $language; ?>_<?php echo $key; ?>');this_img.src=this_published.value == 1 ? '<?php echo $yes_img; ?>' : '<?php echo $no_img; ?>';this_img.alt=this_published.value == 1 ? '<?php echo $yes_alt; ?>' : '<?php echo $no_alt; ?>';this.title=this_published.value == 1 ? '<?php echo $yes_alt; ?>' : '<?php echo $no_alt; ?>'"><img src="<?php echo $img; ?>" alt="<?php echo $alt; ?>" border="0" id="img_published_<?php echo $language; ?>_<?php echo $key; ?>" /></a>
                        </td>
                    </tr>
                    <?php
                }
            } ?>
            </table>
        </div>

        </form>
        <?php
    }
}