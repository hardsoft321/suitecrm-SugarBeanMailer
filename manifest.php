<?php
$manifest = array (
  'acceptable_sugar_versions' => array (),
  'acceptable_sugar_flavors' => array('CE','PRO','ENT'),
  'author' => 'Evgeny Pervushin <pea@lab321.ru>',
  'description' => 'Helper для отправки почты',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'SugarBeanMailer',
  'published_date' => '2016-09-13',
  'type' => 'module',
  'version' => '0.1.1',
);

$installdefs = array (
  'id' => 'SugarBeanMailer',
  'copy' => array (
    array(
        'from' => '<basepath>/source/copy',
        'to' => '.'
    ),
  ),
  'language' => array (
    array (
      'from' => '<basepath>/source/language/application/ru_ru.SugarBeanMailer.php',
      'to_module' => 'application',
      'language' => 'ru_ru',
    ),
    array (
      'from' => '<basepath>/source/language/application/en_us.SugarBeanMailer.php',
      'to_module' => 'application',
      'language' => 'en_us',
    ),
  ),
);
