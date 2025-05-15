<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/projects/projects-views/camera", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.id",
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id]);
    $vars['active'] = 'camera'; // Để highlight tab Tổng quan
    $vars['id'] = $id;
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-camera.html', $vars);
})->setPermissions(['project']);

$app->router("/projects/projects-views/camera", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $searchValue = $_POST['search']['value'] ?? '';
    $orderName = isset($_POST['order'][0]['name']) ? $_POST['order'][0]['name'] : 'name';
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    $id = $_GET['id'] ?? '';
    // $endDate = isset($_POST['endDate']) ? $_POST['endDate'] : '';

    $where = [
        "AND" => [
            "OR" => [
                "camera.id[~]" => $searchValue,
            ],
            "project.id_project" => $id,
        ],
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderName => strtoupper($orderDir)]
    ];
    
    $count = $app->count("camera",[
        "AND" => $where['AND'],
    ]);
    $app->select("camera", [
        "[>]area"       => ["area_id" => "id"],
        "[>]project"    => ["area.project_id" => "id"],
    ],[
        'camera.id',
        'camera.face_count',
        'camera.face_type',
        'camera.device_code',
        'area.name (area)',
        // 'camera.name_project',
        // 'camera.name (customer)',
        // 'camera.startDate',
        // 'camera.endDate',
        'camera.status',
    ], $where, function ($data) use (&$datas,$jatbi,$app) {
        $datas[] = [
            "name"          => $data['id'],
            "area"          => $data['area'],
            "face_count"    => $data['face_count'],
            "face_type"     => $data['face_type'],
            "device_code"   => $data['device_code'],
            // "startDate" => DateTime::createFromFormat('Y-m-d', $data['startDate'])->format('d-m-Y'),
            // "endDate"   => DateTime::createFromFormat('Y-m-d', $data['endDate'])->format('d-m-Y'),
            "status"    => $app->component("status",["url"=>"/project/camera-status/".$data['id'],"data"=>$data['status'],"permission"=>['project.edit']]),
            "action" => $app->component("action",[
                "button" => [
                    [
                        'type' => 'link', // Changed from 'button' to 'link' for clarity
                        'name' => $jatbi->lang("Cấu hình"),
                        'permission' => ['project'],
                        'action' => ['data-url' => '/projects/camera-configuration?id=' . $data['id'], 'data-action' => 'modal']
                    ],
                    // [
                    //     'type' => 'button',
                    //     'name' => $jatbi->lang("Sửa"),
                    //     // 'permission' => ['project.edit'],
                    //     // 'action' => ['data-url' => '/project-edit/'.$data['id'], 'data-action' => 'modal']
                    // ],
                    // [
                    //     'type' => 'button',
                    //     'name' => $jatbi->lang("Xóa"),
                    //     // 'permission' => ['project.delete'],
                    //     // 'action' => ['data-url' => '/project-delete?box='.$data['id'], 'data-action' => 'modal']
                    // ],
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

$app->router("/project/camera-status/{id}", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);
    $data = $app->get("camera","*",["id"=>$vars['id']]);
    if($data>1){
        if($data>1){
            if($data['status']==='A'){
                $status = "D";
            } 
            elseif($data['status']==='D'){
                $status = "A";
            }
            $app->update("camera",["status"=>$status],["id"=>$data['id']]);
            // $jatbi->logs('staffConfiguration','salary-status',$data);
            echo json_encode(['status'=>'success','content'=>$jatbi->lang("Cập nhật thành công")]);
        }
        else {
            echo json_encode(['status'=>'error','content'=>$jatbi->lang("Cập nhật thất bại"),]);
        }
    }
    else {
        echo json_encode(["status"=>"error","content"=>$jatbi->lang("Không tìm thấy dữ liệu")]);
    }
})->setPermissions(['project.edit']);

$app->router("/project/camera-configuration/{id}", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Sửa Dự án");
    $vars['customers'] = $app->select("customer","*");
    $vars['data'] = $app->get("project","*",["id"=>'1']);
    if($vars['data']>1){
        echo $app->render('templates/project/project-post.html', $vars, 'global');
    }
    else {
        echo $app->render('templates/common/error-modal.html', $vars, 'global');
    }
})->setPermissions(['project.edit']);

// $app->router("/project/camera-configuration/{id}", 'POST', function($vars) use ($app, $jatbi, $setting) {
//     $app->header([
//         'Content-Type' => 'application/json',
//     ]);
//     if($app->xss($_POST['name']) == '' || $app->xss($_POST['customer']??'') == '' || $app->xss($_POST['startDate']) == '' || $app->xss($_POST['endDate']) == '') {
//         echo json_encode(["status"=>"error","content"=>$jatbi->lang("Vui lòng nhập các trường bắt buộc.")]);
//         exit;
//     }
//     if($app->xss($_POST['startDate']) > $app->xss($_POST['endDate'])) {
//         echo json_encode(["status"=>"error","content"=>$jatbi->lang("Ngày bắt đầu không được lớn hơn ngày kết thúc.")]);
//         exit;
//     }
//     $insert = [
//         "name_project"  => $app->xss($_POST['name']),
//         "id_customer"   => $app->xss($_POST['customer']),
//         "startDate"     => $app->xss($_POST['startDate']),
//         "endDate"       => $app->xss($_POST['endDate']),
//         "status"        => $app->xss($_POST['status']),
//     ];
//     $app->update("project",$insert,["id"=>$vars['id']]);
//     // $jatbi->logs('staffConfiguration','salary-edit id = ' . $vars['id'] ,$insert);
//     echo json_encode(['status'=>'success','content'=>$jatbi->lang("Cập nhật thành công")]);
//     exit;
// })->setPermissions(['project.edit']);

?>