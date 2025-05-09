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
        'project.id_project',
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
            "status"    => $app->component("status",["url"=>"/project-status/".$data['id'],"data"=>$data['status'],"permission"=>['project.edit']]),
            "action" => $app->component("action",[
                "button" => [
                    [
                        'type' => 'link', // Changed from 'button' to 'link' for clarity
                        'name' => $jatbi->lang("Xem chi tiết"),
                        'permission' => ['project'],
                        'action' => ['href' => '/projects/projects-views?id=' . $data['id_project']] // Use 'href' for navigation
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Sửa"),
                        'permission' => ['project.edit'],
                        'action' => ['data-url' => '/project-edit/'.$data['id'], 'data-action' => 'modal']
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

})->setPermissions(['project.add']);

$app->router("/project-status/{id}", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);
    $data = $app->get("project","*",["id"=>$vars['id']]);
    if($data>1){
        if($data>1){
            if($data['status']==='A'){
                $status = "D";
            } 
            elseif($data['status']==='D'){
                $status = "A";
            }
            $app->update("project",["status"=>$status],["id"=>$data['id']]);
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

$app->router("/project-add", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Thêm Dự án");
    $vars['customers'] = $app->select("customer","*");
    $vars['data'] = [
        "id_customer" => 'A',
        "status" => 'A',
    ];
    echo $app->render('templates/project/project-post.html', $vars, 'global');
})->setPermissions(['project.add']);

$app->router("/project-add", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);
    if($app->xss($_POST['name']) == '' || $app->xss($_POST['customer']??'') == '' || $app->xss($_POST['startDate']) == '' || $app->xss($_POST['endDate']) == '') {
        echo json_encode(["status"=>"error","content"=>$jatbi->lang("Vui lòng nhập các trường bắt buộc.")]);
        exit;
    }
    if($app->xss($_POST['startDate']) > $app->xss($_POST['endDate'])) {
        echo json_encode(["status"=>"error","content"=>$jatbi->lang("Ngày bắt đầu không được lớn hơn ngày kết thúc.")]);
        exit;
    }
    $insert = [
        "name_project"  => $app->xss($_POST['name']),
        "id_customer"   => $app->xss($_POST['customer']),
        "startDate"     => $app->xss($_POST['startDate']),
        "endDate"       => $app->xss($_POST['endDate']),
        "status"        => $app->xss($_POST['status']),
    ];
    $app->insert("project",$insert);
    $jatbi->logs('project','project-add',$insert);
    echo json_encode(['status'=>'success','content'=>$jatbi->lang("Thêm thành công.")]);
    exit;

})->setPermissions(['project.add']);

$app->router("/project-edit/{id}", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Sửa Dự án");
    $vars['customers'] = $app->select("customer","*");
    $vars['data'] = $app->get("project","*",["id"=>$vars['id']]);
    if($vars['data']>1){
        echo $app->render('templates/project/project-post.html', $vars, 'global');
    }
    else {
        echo $app->render('templates/common/error-modal.html', $vars, 'global');
    }
})->setPermissions(['project.edit']);

$app->router("/project-edit/{id}", 'POST', function($vars) use ($app, $jatbi, $setting) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);
    if($app->xss($_POST['name']) == '' || $app->xss($_POST['customer']??'') == '' || $app->xss($_POST['startDate']) == '' || $app->xss($_POST['endDate']) == '') {
        echo json_encode(["status"=>"error","content"=>$jatbi->lang("Vui lòng nhập các trường bắt buộc.")]);
        exit;
    }
    if($app->xss($_POST['startDate']) > $app->xss($_POST['endDate'])) {
        echo json_encode(["status"=>"error","content"=>$jatbi->lang("Ngày bắt đầu không được lớn hơn ngày kết thúc.")]);
        exit;
    }
    $insert = [
        "name_project"  => $app->xss($_POST['name']),
        "id_customer"   => $app->xss($_POST['customer']),
        "startDate"     => $app->xss($_POST['startDate']),
        "endDate"       => $app->xss($_POST['endDate']),
        "status"        => $app->xss($_POST['status']),
    ];
    $app->update("project",$insert,["id"=>$vars['id']]);
    // $jatbi->logs('staffConfiguration','salary-edit id = ' . $vars['id'] ,$insert);
    echo json_encode(['status'=>'success','content'=>$jatbi->lang("Cập nhật thành công")]);
    exit;
})->setPermissions(['project.edit']);

?>