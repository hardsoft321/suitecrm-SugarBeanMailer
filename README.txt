SugarBeanMailer - класс для отправки почты


NotificationCopy - поле для выбора пользователей, которым придет уведомление
о создании записи.

В нужный модуль добавить поле в словарь
$dictionary[<Module>]['fields']['NotificationCopy'] = array(
    'required' => false,
    'name' => 'NotificationCopy',
    'vname' => 'LBL_NOTIFY_ON_CREATE',
    'type' => 'function',
    'source' => 'non-db',
    'massupdate' => 0,
    'studio' => 'visible',
    'importable' => 'false',
    'duplicate_merge' => 'disabled',
    'duplicate_merge_dom_value' => 0,
    'audited' => false,
    'reportable' => false,
    'function' => array(
        'name' => 'NotificationCopy::getFieldHtml',
        'returns' => 'html',
        'include' => 'custom/include/NotificationCopy/NotificationCopy.php'
    ),
);

Добавить поле на форму editviewdefs/quickcreatedefs
array(
    'name' => 'NotificationCopy',
    'hideLabel' => true,
),

Добавить хук для отправки
array (
    'module' => <Module>,
    'hook' => 'after_save',
    'order' => 101,
    'description' => 'Send Notification Copy',
    'file' => 'custom/include/NotificationCopy/NotificationCopy.php',
    'class' => 'NotificationCopy',
    'function' => 'sendCopyAfterSave',
),

Для сохранения выбранного списка в базе добавьте поле в словарь
$dictionary[<Object>]['fields']['notify_to'] = array (
      'name' => 'notify_to',
      'vname' => 'LBL_NOTIFY_TO',
      'type' => 'multinum',
      'dbType' => 'text',
      'len' => '1000',
      'audited' => true,
);

Рекомендуется создать почтовый шаблон <Object>Created.
