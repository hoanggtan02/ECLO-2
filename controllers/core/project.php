<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/project", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Dự án");
    echo $app->render('templates/project/project.html', $vars);
})->setPermissions(['project']);

$app->router("/project", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $searchValue = $_POST['search']['value'] ?? '';
    $orderName = isset($_POST['order'][0]['name']) ? $_POST['order'][0]['name'] : 'name';
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    // $startTime = $app->xss($_POST['startTime'] ?? "");
    // $endTime = $app->xss($_POST['endTime'] ?? "");
    // $personSn = $app->xss($_POST['personSn'] ?? "");
    // $personType = $app->xss($_POST['personType'] ?? "");

    $where = [
        "AND" => [
            "OR" => [
                "project.id[~]" => $searchValue,
                "project.name_project[~]" => $searchValue,
            ],
        ],
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderName => strtoupper($orderDir)]
    ];
    
    // if(!empty($startTime)) {
    //     $where["AND"]["record.createTime[>=]"] = $startTime;
    // }
    // if(!empty($endTime)) {
    //     $endTime = date("Y-m-d", strtotime($endTime . " +1 day"));
    //     $where["AND"]["record.createTime[<=]"] = $endTime;
    // }
    // if(!empty($personSn)) {
    //     $where["AND"]["record.personSn"] = $personSn;
    // }
    // if($personType > -1) {
    //     $where["AND"]["record.personType"] = $personType;
    // }
    
    $count = $app->count("project",[
        "AND" => $where['AND'],
    ]);
    $app->select("project", [
        "[>]customer" => ["id_customer" => "id"],
    ],[
        'project.id',
        'project.name_project',
        'customer.name (name_customer)',
        'project.startDate',
        'project.endDate',
        'project.status',
    ], $where, function ($data) use (&$datas,$jatbi,$app) {
        $datas[] = [
            "checkbox"  => $app->component("box",["data"=>$data['id']]),
            "name"      => $data['name_project'],
            "customer"  => $data['name_customer'],
            "startDate" => $data['startDate'],
            "endDate"   => $data['endDate'],
            "status"    => $app->component("status",["url"=>"/staffConfiguration/department-status/".$data['id'],"data"=>$data['status'],"permission"=>['project.edit']]),
            "action" => $app->component("action",[
                "button" => [
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xem ảnh"),
                        'permission' => ['record'],
                        'action' => ['data-url' => '/record-viewimage?box='.$data['id'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xóa"),
                        'permission' => ['record.deleted'],
                        'action' => ['data-url' => '/record-delete?box='.$data['id'], 'data-action' => 'modal']
                    ],
                ]
            ]),
        ];
    });

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $datas ?? [],
    ]);

})->setPermissions(['project']);

$app->router("/project/project-add", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Thêm Tài khoản");
    $vars['permissions'] = $app->select("permissions","*",["deleted"=>0,"status"=>"A"]);
    $vars['customers'] = $app->select("customer","*");
    $vars['data'] = [
        "id_customer" => 'A',
        "status" => 'A',
        "permission" => '',
        "gender" => '',
    ];
    echo $app->render('templates/project/project-post.html', $vars, 'global');
})->setPermissions(['project.add']);

?>