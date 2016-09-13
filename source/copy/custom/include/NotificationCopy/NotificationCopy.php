<?php
/**
 * @license http://hardsoft321.org/license/ GPLv3
 * @author Evgeny Pervushin <pea@lab321.ru>
 * @package SugarBeanMailer
 *
 * Поле для выбора пользователей, которым придет уведомление о создании записи.
 */
require_once 'include/Sugar_Smarty.php';

/**
 * Поле для выбора пользователей, которым придет уведомление о создании записи.
 */
class NotificationCopy
{
    public static function getFieldHtml($bean, $field, $value, $view)
    {
        $isDuplicate = isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true';
        $isReply = isset($_REQUEST['isReply']) && $_REQUEST['isReply'] == 'true';
        if(!empty($bean->fetched_row['id']) && !$isDuplicate && !$isReply) {
            return '';
        }
        if($view != 'EditView' && $view != 'QuickCreate') {
            return '';
        }
        $formName = $view == 'EditView' ? 'EditView' : 'form_SubpanelQuickCreate_'.$bean->module_name;
        return self::getFormFieldHtml($bean, $formName);
    }

    public static function getFormFieldHtml($bean, $formName, $params = array())
    {
        global $db, $locale;
        $ss = new Sugar_Smarty();

        if(!empty($bean->notify_to)) {
        $ss->assign('notify_to', $bean->notify_to);

        $notify_to_users = array ();
        $quotedUsersIds = array_map (function ($i) { return "'" . trim ($i, '^') . "'"; }, explode(',', $bean->notify_to));
        $q = $db->query("SELECT id, first_name, last_name FROM users WHERE id IN (" . implode (',', $quotedUsersIds) . ") AND deleted = 0");
        while ($u = $db->fetchByAssoc($q)) $notify_to_users[$u['id']] = $locale->getLocaleFormattedName($u['first_name'], $u['last_name']);
        $ss->assign('notify_to_users', $notify_to_users);
        }

        $ss->assign('formName', $formName);

        $ss->assign('params', $params);

        return $ss->fetch('custom/include/NotificationCopy/NotificationCopy.tpl');
    }

    public function sendCopyAfterSave($bean, $event)
    {
        require_once 'custom/include/SugarBeanMailer.php';
        if(empty($bean->date_entered)) {
            return; //если не новая запись, ничего не делаем
        }
        $template = $bean->getObjectName().'Created';
        $this->send($bean, $template);
    }

    public function send($bean, $template = 'Default')
    {
        require_once 'custom/include/SugarBeanMailer.php';
        if(!empty($_POST['notification_copy_just_store'])) {
            return;
        }
        if(!empty($_POST['notification_copy'])) {
            $usersIds = array();
            if(is_array($_POST['notification_copy'])) {
                foreach($_POST['notification_copy'] as $fields) {
                    $usersIds[] = $fields['id'];
                }
            }
            else {
                $usersIds[] = $_POST['notification_copy'];
            }
            $usersIds = array_unique($usersIds);
            $users = array();
            foreach($usersIds as $id) {
                if($id) {
                    if(!empty($bean->assigned_user_id) && $bean->assigned_user_id == $id) {
                        continue;
                    }
                    $user = BeanFactory::getBean('Users', $id);
                    if($user) {
                        $users[] = $user;
                    }
                    else {
                        $GLOBALS['log']->error('NotificationCopy: user not found '.$id);
                    }
                }
            }
            if(!empty($users)) {
                $assigned = reset($bean->get_notification_recipients());
                $mailer = new SugarBeanMailer($bean);
                $mailer->set_notification_recipients($users);
                $mailer->setTemplate($template, array(
                    'SUBJECT' => $bean->get_summary_text(),
                    'ASSIGNED_USER' => $assigned->full_name,
                ));
                $mailer->setAltTemplate('Default');
                $mailer->sendNotifications();
            }
            unset($_POST['notification_copy']); //чтобы два раза не отправить
        }
    }
}
