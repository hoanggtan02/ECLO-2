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


// Route xử lý trạng thái khu vực
$app->router("/project/area-status/{id}", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);

    // Lấy bản ghi từ bảng area
    $data = $app->get("area", "*", ["id" => $vars['id']]);

    // Kiểm tra xem bản ghi có tồn tại không
    if (!$data) {
        $jatbi->logs('area', 'area-status-error', ['id' => $vars['id'], 'error' => 'Không tìm thấy bản ghi']);
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Không tìm thấy dữ liệu")]);
        return;
    }

    // Lấy trạng thái hiện tại và chuyển đổi
    $current_status = $data['is_active'];
    $new_status = ($current_status == 'A') ? 'D' : 'A'; // Sử dụng 'A'/'D' thay vì 1/0

    // Cập nhật trạng thái
    $update_result = $app->update("area", ["is_active" => $new_status], ["id" => $data['id']]);

    // Kiểm tra kết quả cập nhật
    if ($update_result === false) {
        $jatbi->logs('area', 'area-status-error', ['id' => $vars['id'], 'error' => 'Cập nhật thất bại']);
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Cập nhật thất bại")]);
        return;
    }

    // Log hành động cập nhật thành công
    $jatbi->logs('area', 'area-status-update id = ' . $vars['id'], ['is_active' => $new_status]);
    echo json_encode(['status' => 'success', 'content' => $jatbi->lang("Cập nhật thành công")]);
})->setPermissions(['area.edit']);




$app->router("/projects/projects-views/area", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);

    // Nhận dữ liệu từ DataTable
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $searchValue = $_POST['search']['value'] ?? '';
    $orderName = isset($_POST['order'][0]['name']) ? $_POST['order'][0]['name'] : 'name';
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'DESC';

    $id_project = $_GET['id'] ?? ''; // Lấy id từ URL

    // Điều kiện truy vấn
    $where = [
        "AND" => [
            "OR" => [
                "area.id[~]" => $searchValue,
                "area.name[~]" => $searchValue,
                "area.address[~]" => $searchValue,
                "area.code[~]" => $searchValue,
            ],
            "project.id_project" => $id_project,
        ],
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderName => strtoupper($orderDir)]
    ];

    // Đếm tổng số bản ghi
    $count = $app->count("area", [
        "[>]project" => ["project_id" => "id"]
    ], "*", $where['AND']);

    // Lấy dữ liệu khu vực
    $datas = [];
    $app->select("area", [
        "[>]project" => ["project_id" => "id"]
    ], [
        'area.id',
        'area.name',
        'area.address',
        'area.code',
        'area.is_active',
        'area.created_at',
        'project.name_project (project_name)',
        'project.id_project',
    ], $where, function ($data) use (&$datas, $jatbi, $app) {
        $status = ($data['is_active'] == 'A') ? "Hoạt động" : "Ngừng hoạt động";
        $created_at = !empty($data['created_at']) ? date('d-m-Y H:i:s', strtotime($data['created_at'])) : 'N/A';

        // Đếm số lượng camera trong khu vực
        $camera_count = $app->count("camera", ["area_id" => $data['id']]);

        // Tạo nội dung cột Camera (số lượng + liên kết Thêm camera)
        // $camera_content = $camera_count . ' <a href="#" class="text-primary" data-action="modal" data-url="/project/camera-add?area_id=' . $data['id'] . '">Thêm camera</a>';
        $camera_content =   '<div class="row justify-content-between">
                                <div class="col-1">' . '<a href="/projects/projects-views/camera?id=' . $data['id_project'] . '&area=' . $data['id'] . '" class="btn btn-sm text-primary">' . $camera_count . '</a>' . '</div>
                                <div class="col-8">' . '<a href="#" class="btn btn-outline-primary btn-sm border-0" data-action="modal" data-url="/project/camera-add?area_id=' . $data['id'] . '">Thêm camera</a>' . '</div>
                            </div>';

        $datas[] = [
            "checkbox" => '<div class="form-check"><input class="form-check-input checker" type="checkbox" value="' . $data['id'] . '"></div>',
            "id" => $data['id'],
            "name" => $data['name'] ?? 'Unknown',
            "address" => $data['address'] ?? 'N/A',
            "code" => $data['code'] ?? 'N/A',
            "camera" => $camera_content, 
            "is_active" => $app->component("status", [
                "url" => "/project/area-status/" . $data['id'],
                "data" => $data['is_active'],
                "permission" => ['area.edit']
            ]),
            "created_at" => $created_at,
            "action" => $app->component("action", [
                "button" => [
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xem Chi Tiết"),
                        'permission' => ['area'],
                        'action' => ['data-url' => '/projects/projects-views/area/view-detail?id=' . $data['id'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Sửa"),
                        'permission' => ['area.edit'],
                        'action' => ['data-url' => '/project/area-edit/' . $data['id'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xóa"),
                        'permission' => ['area.delete'],
                        'action' => ['data-url' => '/project/area-delete?box=' . $data['id'], 'data-action' => 'modal']
                    ],
                ]
            ]),
        ];
    });

    // Trả về JSON cho DataTable
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $datas ?? [],
    ]);
})->setPermissions(['area']);

$app->router("/project/area-add", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Thêm Khu vực");
    $id_project = $_GET['id'] ?? ''; 
    if (empty($id_project)) {
        // Nếu không có id_project, trả về lỗi
        $vars['title'] = $jatbi->lang("Lỗi");
        $vars['error'] = $jatbi->lang("Không tìm thấy dự án.");
        echo $app->render('templates/error.html', $vars, 'global');
        return;
    }
    $vars['id_project'] = $id_project;
    echo $app->render('templates/project/projectDetail/area-post.html', $vars, 'global');
})->setPermissions(['area.add']);

$app->router("/project/area-add", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);

    // Lấy dữ liệu từ form
    $name = $app->xss($_POST['name'] ?? '');
    $address = $app->xss($_POST['address'] ?? '');
    $status = $app->xss($_POST['status'] ?? 'A');
    $id_project = $app->xss($_POST['id_project'] ?? '');

    // Kiểm tra dữ liệu
    if (empty($name)) {
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Vui lòng nhập tên khu vực.")]);
        return;
    }
    if (empty($id_project)) {
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Không tìm thấy dự án.")]);
        return;
    }

    // Tạo dữ liệu để chèn
    $insert = [
        "id" => $app->getMax("area", "id") + 1,
        "name" => $name,
        "project_id" => $id_project,
        "address" => $address ?: null,
        "is_active" => $status,
        "created_at" => date('Y-m-d H:i:s')
    ];

    // Chèn vào cơ sở dữ liệu
    $app->insert("area", $insert);

    // Kiểm tra kết quả
    if ($app->id()) {
        $jatbi->logs('area', 'area-add', $insert);
        echo json_encode(['status' => 'success', 'content' => $jatbi->lang("Thêm khu vực thành công."), 'redirect' => '/projects/projects-views/area?id=' . $id_project]);
    } else {
        $jatbi->logs('area', 'area-add-error', ['error' => 'Thêm khu vực thất bại', 'data' => $insert]);
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Thêm khu vực thất bại.")]);
    }
})->setPermissions(['area.add']);

$app->router("/project/camera-add", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Thêm Camera");
    $vars['customers'] = $app->select("customer","*");
    $vars['data'] = [
        "id_customer" => 'A',
        "status" => 'A',
    ];
    echo $app->render('templates/project/projectDetail/cameraAdd-post.html', $vars, 'global');
})->setPermissions(['project']);