<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/projects/projects-views/face", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id]);
    $vars['active'] = 'face'; // Để highlight tab Tổng quan
    $vars['id'] = $id;
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-face.html', $vars);
})->setPermissions(['project']);

$app->router("/projects/projects-views/face", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);

    // Nhận dữ liệu từ DataTable
    $draw = $_POST['draw'] ?? 0;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $id_project = $_GET['id'] ?? '';

    // Kiểm tra id_project
    if (empty($id_project)) {
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
            "error" => "Missing id_project"
        ]);
        return;
    }

    // Lấy project.id từ id_project
    $project = $app->select("project", ["id"], ["id_project" => $id_project, "status" => 'A']);
    if (!$project || !isset($project[0])) {
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
        ]);
        return;
    }
    $project_id = $project[0]['id'];

    // Đếm tổng số bản ghi
    $totalRecords = $app->count("faces", [
        "[>]area" => ["area_id" => "id"]
    ], "*", ["area.project_id" => $project_id, "area.is_active" => 'A']);

    // Xử lý sắp xếp cột
    $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
    $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');
    $validColumns = [
        0 => "faces.image_url",
        1 => "faces.name",
        2 => "faces.type",
        3 => "area.name",
        4 => "faces.status"
    ];
    $orderColumn = $validColumns[$orderColumnIndex] ?? "faces.name";

    // Điều kiện
    $conditions = [
        "area.project_id" => $project_id,
        "area.is_active" => 'A',];

    // Đếm số bản ghi (recordsFiltered = recordsTotal vì không có tìm kiếm/lọc)
    $filteredRecords = $totalRecords;

    // Lấy dữ liệu faces với phân trang và sắp xếp
    $faces = $app->select("faces", [
        "[>]area" => ["area_id" => "id"]
    ], [
        "faces.id",
        "faces.name",
        "faces.image_url",
        "faces.type",
        "faces.area_id",
        "area.name (area_name)",
        "faces.status"
    ], array_merge($conditions, [
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderColumn => $orderDir]
    ]));

    // Chuẩn bị dữ liệu trả về cho DataTable
    $data = [];
    foreach ($faces as $face) {
        $data[] = [
            "image" => "<img src='data:image/jpeg;base64,{$face['image_url']}' style='max-width: 100px;'>",
            "name" => $face['name'] ?? 'Unknown',
            "type" => $face['type'] ?? 'Unknown',
            "area" => $face['area_name'] ?? 'Unknown Area',
            "status"    => $app->component("status",["url"=>"/project-status/".$face['id'],"data"=>$face['status'],"permission"=>['project.edit']]),
            "action" => $app->component("action", [
                "button" => [
                    [
                    'type' => 'link', // Sử dụng 'link' thay vì 'button' để chuyển hướng
                    'name' => $jatbi->lang("Xem"),
                    'action' => ['href' => '/projects/projects-views/logs/logs-face?box=' . $face['id'] . '&id=' . $id_project . '']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Sửa"),
                        'action' => ['data-url' => '/projects/projects-views/face-edit?id='.$face['id'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xóa"),
                        'action' => ['data-url' => '/projects/projects-views/face-delete?box='.$face['id'], 'data-action' => 'modal']
                    ],
                ]
            ]),   
        ];
    }

    // Trả về JSON cho DataTable
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    ]);
})->setPermissions(['project']);
?>