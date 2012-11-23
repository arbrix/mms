<?php
/*
$config = Zend_Registry::get('config');
    $dbUsername = $config->database->params->username;
    $dbPassword = $config->database->params->password;
    $dbName = $config->database->params->dbname;
    $file = APPLICATION_PATH . '/data/backup/' . date('Y-m-d_h.i.s') . '.sql';
    $command = sprintf("
        mysqldump -u %s --password=%s -d %s --skip-no-data > %s",
        escapeshellcmd($dbUsername),
        escapeshellcmd($dbPassword),
        escapeshellcmd($dbName),
        escapeshellcmd($file)
    );
    exec($command);
*/

/*
echo '<pre>';
var_dump($config);
var_dump($_SERVER['REQUEST_MICROTIME']);
echo '</pre>';
exit();
*/