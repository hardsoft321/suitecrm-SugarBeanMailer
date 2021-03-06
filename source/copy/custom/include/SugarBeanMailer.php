<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author  Evgeny Pervushin <pea@lab321.ru>
 */
require_once 'data/SugarBean.php';

class SugarBeanMailer extends SugarBean
{
    private $notify_list = array();
    private $bean;
    private $templateName;
    private $templateData;
    private $templateFile;
    private $altTemplateName;

    public function __construct($bean) {
        $this->bean = $bean;
        $this->id = $bean->id;
        $this->module_dir = $bean->module_dir;

        if(!empty($this->bean->assigned_user_id)) {
            $notify_user = new User();
            $notify_user->retrieve($this->bean->assigned_user_id);
            $this->new_assigned_user_name = $notify_user->full_name;
        }
    }

    public function sendNotifications() {
        if(empty($this->notify_list)) {
            $this->notify_list = $this->bean->get_notification_recipients();
        }
        $this->_sendNotifications(true);
    }

    public function get_notification_recipients() {
        return $this->notify_list;
    }

    public function set_notification_recipients($notify_list) {
        $this->notify_list = $notify_list;
    }

    public function setTemplate($templateName, $templateData = array(), $templateFile = null) {
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        $this->templateFile = $templateFile;
    }

    public function setAltTemplate($templateName) {
        $this->altTemplateName = $templateName;
    }

    public static function getSiteUrl() {
        global $sugar_config;
        $parsedSiteUrl = parse_url($sugar_config['site_url']);
        $host = $parsedSiteUrl['host'];
        if(!isset($parsedSiteUrl['port'])) {
            $parsedSiteUrl['port'] = 80;
        }
        $port = ($parsedSiteUrl['port'] != 80) ? ":".$parsedSiteUrl['port'] : '';
        $path = !empty($parsedSiteUrl['path']) ? $parsedSiteUrl['path'] : "";
        return "{$parsedSiteUrl['scheme']}://{$host}{$port}{$path}";
    }

    protected function set_notification_body($xtpl, $bean) {
        if(in_array('set_notification_body', get_class_methods($this->bean))) {
            $this->bean->current_notify_user = $this->current_notify_user;
            $xtpl = $this->bean->set_notification_body($xtpl, $this->bean);
        }
        $_xtpl = new _BeanXTemplate($xtpl, $this->templateName, $this->templateData, $this->templateFile);
        $_xtpl->altTemplateName = $this->altTemplateName;
        return $_xtpl;
    }

    /**
    * Скопировано из SugarCRMv6.5.16 SugarBean.php, т.к. функция приватная
    *
    * Send assignment notifications and invites for meetings and calls
    */
    private function _sendNotifications($check_notify){
        if($check_notify || (isset($this->notify_inworkflow) && $this->notify_inworkflow == true) // cn: bug 5795 - no invites sent to Contacts, and also bug 25995, in workflow, it will set the notify_on_save=true.
           && !$this->isOwner($this->created_by) )  // cn: bug 42727 no need to send email to owner (within workflow)
        {
            $admin = new Administration();
            $admin->retrieveSettings();
            $sendNotifications = false;

            if ($admin->settings['notify_on'])
            {
                $GLOBALS['log']->info("Notifications: user assignment has changed, checking if user receives notifications");
                $sendNotifications = true;
            }
            elseif(isset($_REQUEST['send_invites']) && $_REQUEST['send_invites'] == 1)
            {
                // cn: bug 5795 Send Invites failing for Contacts
                $sendNotifications = true;
            }
            else
            {
                $GLOBALS['log']->info("Notifications: not sending e-mail, notify_on is set to OFF");
            }


            if($sendNotifications == true)
            {
                $notify_list = $this->get_notification_recipients();
                foreach ($notify_list as $notify_user)
                {
                    $this->send_assignment_notifications($notify_user, $admin);
                }
            }
        }
    }
}

class _BeanXTemplate
{
    private $xtpl;
    private $templateName;
    private $templateData;
    public $altTemplateName;

    public function __construct($xtpl, $templateName, $templateData, $file = null) {
        global $beanList;
        $this->xtpl = $xtpl;
        $this->templateName = $templateName;
        $this->templateData = $templateData;
        if($file) {
            if(file_exists($file)) {
                $this->xtpl->filecontents=$this->xtpl->r_getfile($file);
                $this->xtpl->blocks=$this->xtpl->maketree($this->xtpl->filecontents,$this->xtpl->mainblock);
            }
            else {
                $GLOBALS['log']->error("SugarBeanMailer: template file {$templateFile} not found");
            }
        }
        $this->xtpl->assign("OBJECT", translate('LBL_MODULE_NAME'));
        if($this->templateData) {
            foreach($this->templateData as $name => $value) {
                $this->xtpl->assign($name, $value);
            }
        }
    }

    public function assign ($name, $val="") {
        if(!isset($this->templateData[$name])) {
            $this->xtpl->assign($name, $val);
        }
    }

    public function parse ($bname) {
        $_bname = $this->_replaceTemplateName($bname, $this->templateName);
        if(!empty($this->xtpl->blocks[$_bname])) {
            $this->xtpl->parse($_bname);
        }
        else {
            $_bname_alt = $this->_replaceTemplateName($bname, $this->altTemplateName);
            $this->xtpl->parse($_bname_alt);
        }
    }

    public function text($bname) {
        $text = $this->xtpl->text($this->_replaceTemplateName($bname, $this->templateName));
        return !empty($text) ? $text : $this->xtpl->text($this->_replaceTemplateName($bname, $this->altTemplateName));
    }

    protected function _replaceTemplateName($bname, $newName) {
        return ($newName ? $newName : 'Default').(strpos($bname, "_Subject") !== false ? "_Subject" : "");
    }
}
