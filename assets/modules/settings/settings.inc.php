<?php
if (IN_MANAGER_MODE != "true" || empty($modx) || !($modx instanceof DocumentParser)) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if (!$modx->hasPermission('exec_module')) {
    header("location: " . $modx->getManagerPath() . "?a=106");
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$moduleurl = '/manager/index.php?a=112&id=' . $_GET['id'] . '&';
$modulePath = '/assets/modules/settings';

require 'classes/settings.php';
$obj = new settings($modx);
$obj->firstStart();


switch ($action) {

    case 'loadData':
        echo json_encode($obj->loadData($_GET['elem']));
        die();
        break;
    case 'save-sort':
        $obj->saveSorting($_GET['data']);
        break;
    case 'delete-field':
        echo json_encode($obj->deleteField($_GET['item']));
        break;
    case 'getForm':
        $categories = $obj->getForm();
        echo '<div class="tab-content">
' . $categories . '
</div>';
        die();
        break;
    case 'saveValues':
        $obj->saveValues(json_decode($_GET['data'], true));
        break;
    case 'get-category':
        $categories = $obj->getCategory();
        echo json_encode($categories, true);
        break;

    case 'saveField':
        $resp = $obj->saveField(json_decode($_GET['field'], true));
        echo $resp;
        break;
    default:

        $resp = $obj->render();

        $tpl = file_get_contents('template.tpl', true);
        $tpl = str_replace([
            '[+tabs+]',
            '[+forms+]',
        ], [
            $resp['tabs'],
            $resp['forms'],
        ], $tpl);
}

$tpl = str_replace([
    '[+head+]',
    '[+footer+]',
    '[+moduleurl+]',
    '[+manager_theme+]',
    '[+totalimp+]',
    '[+modulePath+]',
    '[+site_url+]',
    '//browser.php',
    '//style.css',
    '//main.css',
], [
    file_get_contents('template/head.tpl', true),
    file_get_contents('template/footer.tpl', true),
    $moduleurl,
    $modx->config['manager_theme'],
    '',
    $modulePath,
    $_SERVER['HTTP_HOST'],
    '/mcpuk/browser.php',
    '/MODxRE2_DropdownMenu/style.css',
    '/MODxRE2_DropdownMenu/main.css'
],
    $tpl
);


echo $tpl;
