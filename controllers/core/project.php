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
    $orderName = isset($_POST['order'][0]['name']) ? $_POST['order'][0]['name'] : 'id';
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    $startTime = $app->xss($_POST['startTime'] ?? "");
    $endTime = $app->xss($_POST['endTime'] ?? "");
    $personSn = $app->xss($_POST['personSn'] ?? "");
    $personType = $app->xss($_POST['personType'] ?? "");

    $where = [
        "AND" => [
            "OR" => [
                "record.id[~]" => $searchValue,
                "record.personName[~]" => $searchValue,
                "record.personSn[~]" => $searchValue,
            ],
        ],
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderName => strtoupper($orderDir)]
    ];
    
    if(!empty($startTime)) {
        $where["AND"]["record.createTime[>=]"] = $startTime;
    }
    if(!empty($endTime)) {
        $endTime = date("Y-m-d", strtotime($endTime . " +1 day"));
        $where["AND"]["record.createTime[<=]"] = $endTime;
    }
    if(!empty($personSn)) {
        $where["AND"]["record.personSn"] = $personSn;
    }
    if($personType > -1) {
        $where["AND"]["record.personType"] = $personType;
    }
    
    $count = $app->count("record",[
        "AND" => $where['AND'],
    ]);
    $app->select("record", 
        [
        'record.id',
        'record.personName',
        'record.personSn',
        'record.personType',
        'record.createTime',
        ], $where, function ($data) use (&$datas,$jatbi,$app) {
        $datas[] = [
            "checkbox" => $app->component("box",["data"=>$data['id']]),
            "id" => $data['id'],
            "personName" => $data['personName'],
            "personSn" => $data['personSn'],
            "personType" => $data['personType'] == 1 ? "Nhân viên" : 
                            ($data['personType'] == 2 ? "Khách" : 
                            ($data['personType'] == 3 ? "Danh sách đen" : "Không xác định")),
            "createTime" => $data['createTime'],
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

})->setPermissions(['record']);
?>