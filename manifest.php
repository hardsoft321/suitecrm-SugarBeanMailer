<?php
$manifest = array (
  'acceptable_sugar_versions' => array (),
  'acceptable_sugar_flavors' => array('CE','PRO','ENT'),
  'author' => 'Evgeny Pervushin <pea@lab321.ru>',
  'description' => 'Helper для отправки почты',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'SugarBeanMailer',
  'published_date' => '2015-05-06',
  'type' => 'module',
  'version' => '0.0.1',
);

$installdefs = array (
  'id' => 'SugarBeanMailer',
  'copy' => array (
    array(
        'from' => '<basepath>/source/copy',
        'to' => '.'
    ),
  ),
);
