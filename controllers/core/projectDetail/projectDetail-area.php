<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

// Route hiển thị chi tiết khu vực của dự án
$app->router("/projects/projects-views/area", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id]);
    $vars['active'] = 'area'; 
    $vars['id'] = $id;
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-area.html', $vars);
})->setPermissions(['project']);


$app->router("/project/area", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Khu vực");
  
    $data = $app->select("area", ["id", "name", "project_id", "address", "code", "is_active", "created_at"]);
    $vars['data'] = $data;
    $vars['active'] = 'area';
    echo $app->render('templates/project/area.html', $vars);
})->setPermissions(['area']);

$app->router("/project/area", 'POST', function($vars) use ($app, $jatbi) {
    $app->header(['Content-Type' => 'application/json']);

    // Lấy các tham số từ DataTables
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $searchValue = isset($_POST['search']['value']) ? $_POST['search']['value'] : '';
    $status = isset($_POST['is_active']) ? $_POST['is_active'] : '';
    $orderColumnIndex = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : 1;
    $orderDir = strtoupper(isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC');

    // Danh sách cột hợp lệ
    $validColumns = [
        "checkbox",
        "area.id",
        "area.name",
        "project.name_project",
        "area.address",
        "area.code",
        "area.is_active",
        "area.created_at",
        "action"
    ];
    $orderColumn = $validColumns[$orderColumnIndex] ?? "area.created_at";

    // Điều kiện lọc dữ liệu
    $conditions = ["AND" => []];

    if (!empty($searchValue)) {
        $conditions["AND"]["OR"] = [
            "area.id[~]" => $searchValue,
            "area.name[~]" => $searchValue,
            "area.address[~]" => $searchValue,
            "area.code[~]" => $searchValue,
            "project.name_project[~]" => $searchValue
        ];
    }

    if ($status !== '') {
        $conditions["AND"]["area.is_active"] = $status;
    }

    // Kiểm tra nếu conditions bị trống
    if (empty($conditions["AND"])) {
        unset($conditions["AND"]);
    }

    // Đếm tổng số bản ghi với JOIN
    $count = $app->count("area", [
        "[>]project" => ["project_id" => "id"]
    ], "area.id", $conditions);

    // Truy vấn danh sách dữ liệu
    $datas = $app->select("area", [
        "[>]project" => ["project_id" => "id"]
    ], [
        "area.id",
        "area.name",
        "area.project_id",
        "area.address",
        "area.code",
        "area.is_active",
        "area.created_at",
        "project.name_project(project_name)"
    ], array_merge($conditions, [
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderColumn => $orderDir]
    ])) ?? [];

    // Xử lý dữ liệu đầu ra
    $formattedData = array_map(function($data) use ($app, $jatbi) {
        $address = $data['address'] ? str_replace("\n", "<br>", wordwrap($data['address'], 20, "<br>", true)) : $jatbi->lang("Không có địa chỉ");

        return [
            "checkbox" => $app->component("box", ["data" => $data['id']]),
            "id" => $data['id'],
            "name" => $data['name'] ?: $jatbi->lang("Không xác định"),
            "project_id" => $data['project_name'] ?: $jatbi->lang("Không xác định"),
            "address" => $address,
            "code" => $data['code'] ?: $jatbi->lang("Không xác định"),
            "is_active" => $app->component("status", [
                "url" => "/project/area-status/" . $data['id'],
                "data" => $data['is_active'],
                "permission" => ['area.edit']
            ]),
            "created_at" => $data['created_at'] ? date('d/m/Y H:i:s', strtotime($data['created_at'])) : $jatbi->lang("Không xác định"),
            "action" => $app->component("action", [
                "button" => [
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Sửa"),
                        'permission' => ['area.edit'],
                        'action' => ['data-url' => '/project/area-edit/' . $data['id'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xóa"),
                        'permission' => ['area.deleted'],
                        'action' => ['data-url' => '/project/area-deleted?box=' . $data['id'], 'data-action' => 'modal']
                    ],
                ]
            ]),
        ];
    }, $datas);

    // Trả về dữ liệu dưới dạng JSON cho DataTables
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $formattedData
    ]);
})->setPermissions(['area']);