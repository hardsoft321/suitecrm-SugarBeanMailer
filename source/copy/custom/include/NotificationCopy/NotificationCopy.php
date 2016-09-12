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
        global $db, $locale;

        $isDuplicate = isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true';
        $isReply = isset($_REQUEST['isReply']) && $_REQUEST['isReply'] == 'true';
        if(!empty($bean->fetched_row['id']) && !$isDuplicate && !$isReply) {
            return '';
        }
        if($view != 'EditView' && $view != 'QuickCreate') {
            return '';
        }
        $ss = new Sugar_Smarty();
        $ss->assign('formName', $view == 'EditView' ? 'EditView' : 'form_SubpanelQuickCreate_'.$bean->module_name);
        $ss->assign('notify_to', $bean->notify_to);

        $notify_to_users = array ();
        $quotedUsersIds = array_map (function ($i) { return "'" . trim ($i, '^') . "'"; }, explode(',', $bean->notify_to));
        $q = $db->query("SELECT id, first_name, last_name FROM users WHERE id IN (" . implode (',', $quotedUsersIds) . ") AND deleted = 0");
        while ($u = $db->fetchByAssoc($q)) $notify_to_users[$u['id']] = $locale->getLocaleFormattedName($u['first_name'], $u['last_name']);
        $ss->assign('notify_to_users', $notify_to_users);

        return $ss->fetch('custom/include/NotificationCopy/NotificationCopy.tpl');

    }

    public function sendCopyAfterSave($bean, $event)
    {
        require_once 'custom/include/SugarBeanMailer.php';
        if(empty($bean->date_entered)) {
            return; //если не новая запись, ничего не делаем
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
            $users = array();
            foreach($usersIds as $id) {
                if($id) {
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
                $mailer = new SugarBeanMailer($bean);
                $mailer->set_notification_recipients($users);
                $template = $bean->getObjectName().'Created';
                $mailer->setTemplate($template, array(
                    'SUBJECT' => $bean->get_summary_text(),
                ));
                $mailer->setAltTemplate('Default');
                $mailer->sendNotifications();
            }
            unset($_POST['notification_copy']); //чтобы два раза не отправить
        }
    }
}
