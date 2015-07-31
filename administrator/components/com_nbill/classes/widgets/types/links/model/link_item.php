<?php
class LinkItem
{
    const TYPE_MENU = 0;
    const TYPE_EXTENSION = 1;
    const TYPE_USER_DEFINED = 2;

    public $type = self::TYPE_MENU;
    /** @var int ID number of the menu item to which the link relates **/
    public $menu_id;
    /** @var string Can be the full URL, or include the [NBILL_ADMIN] placeholder for the admin page prefix **/
    public $url;
    /** @var string Any HTML attributes you want to apply to the anchor tag (eg. to execute javascript when clicked) **/
    public $link_attributes;
    /** @var string Text for the link **/
    public $text;
    /** @var string Any HTML attributes you want to apply to the label span **/
    public $text_attributes;
    /** @var string Title is typically shown when a mouse pointer hovers over a link **/
    public $title;
    /** @var string Can be the full URL, or include the [NBILL_FE] placeholder for the front-end URL prefix **/
    public $image;
    /** @var string Any HTML attributes you want to apply to the image tag **/
    public $image_attributes;
    /** @var boolean Whether or not this link is shown **/
    public $published = false;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function prepare_for_rendering($admin_action_file_path, $action_url_path, $site_url_path, $icon_type)
    {
        $this->loadExtensionLanguage();
        $this->url = str_replace('[NBILL_ADMIN]', $action_url_path, $this->url);
        $this->url = str_replace('[NBILL_FE]', $site_url_path, $this->url);
        if ($icon_type == LinksWidget::NO_ICONS) {
            $this->image = '';
        } else {
            $this->image = str_replace('[NBILL_ADMIN]', $action_url_path, $this->image);
            $this->image = str_replace('[NBILL_FE]', $site_url_path, $this->image);
            if ($icon_type == LinksWidget::LARGE_ICONS) {
                $this->image = str_replace('/icons/', '/icons/large/', $this->image);
            }
        }
        if (strpos($this->url, '&disabled=1') === false && $this->is_disabled($admin_action_file_path, $action_url_path)) {
            $this->url .= '&disabled=1';
            $this->image = str_replace('.gif', '_disabled.gif', $this->image);
            $this->image = str_replace('.png', '_disabled.png', $this->image);
        }
        if (defined($this->text)) {
            $this->text = constant($this->text);
        }
        if (defined($this->title)) {
            $this->title = constant($this->title);
        }
    }

    protected function loadExtensionLanguage()
    {
        //For extensions, we need to load the appropriate language file
        if ($this->type == self::TYPE_EXTENSION) {
            $url_params = explode('&', $this->url);
            foreach ($url_params as $param) {
                $key_value_pair = explode('=', $param);
                if (count($key_value_pair) == 2) {
                    if ($key_value_pair[0] == 'action') {
                        nbf_common::load_language($key_value_pair[1]);
                        break;
                    }
                }
            }
        }
    }

    protected function is_disabled($admin_action_file_path, $action_url_path)
    {
        if ($this->type == LinkItem::TYPE_MENU)
        {
            if (substr($this->url, 0, strlen($action_url_path) + 8) == $action_url_path . '&action=') {
                $action = substr($this->url, strlen($action_url_path) + 8);
                if (strpos($action, '&') !== false) {
                    $action = substr($action, 0, strpos($action, '&'));
                }
                return !file_exists($admin_action_file_path . "/admin.proc/" . $action . ".php");
            }
        }
    }
}