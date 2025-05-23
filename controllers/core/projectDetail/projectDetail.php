<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/projects/projects-views", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id]);

    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    $vars['active'] = 'projects-views';
    $vars['id'] = $id;
    echo $app->render('templates/project/projectDetail/projectDetail.html', $vars);
})->setPermissions(['project']);

// Route để xử lý yêu cầu POST từ DataTables và trả về dữ liệu JSON
$app->router("/projects/projects-views", 'POST', function($vars) use ($app, $jatbi) {
    
})->setPermissions(['project']);



?>