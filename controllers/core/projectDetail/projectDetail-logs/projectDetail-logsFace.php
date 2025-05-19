<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

// Route GET: Hiển thị giao diện logs face
$app->router("/projects/projects-views/logs/logs-face", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.id",
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id,
        "project.status" => 'A']);
    $vars['active'] = 'logs';
    $vars['active2'] = 'face';
    $vars['id'] = $id;
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-logs/projectDetail-log-logsFace.html', $vars);
})->setPermissions(['project']);

// Route POST: Trả về dữ liệu logs face cho DataTable
$app->router("/projects/projects-views/logs/logs-face", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);

    // Debug: Log dữ liệu nhận được
    error_log("POST Data: " . print_r($_POST, true));
    error_log("GET Data: " . print_r($_GET, true));

    // Nhận dữ liệu từ DataTable
    $draw = $_POST['draw'] ?? 0;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $id_project = $_GET['id'] ?? '';

    // Kiểm tra id_project
    if (empty($id_project)) {
        error_log("Error: Missing id_project");
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
    $project = $app->select("project", ["id"], ["id_project" => $id_project, 
        "status" => 'A']);
    if (!$project || !isset($project[0])) {
        error_log("Error: Project not found for id_project: $id_project");
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => [],
        ]);
        return;
    }
    $project_id = $project[0]['id'];
    error_log("Project ID: $project_id");

    // Đếm tổng số bản ghi (recordsTotal)
    $totalRecords = $app->count("access_events", [
        "[>]camera" => ["deviceKey" => "id"],
        "[>]area" => ["camera.area_id" => "id"]
    ], "*", ["area.project_id" => $project_id]);
    error_log("Total Records: $totalRecords");

    // Xử lý sắp xếp cột
    $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
    $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');
    $validColumns = [
        0 => "access_events.personName",
        1 => "access_events.personType",
        2 => "area.name",
        3 => "camera.device_code",
        4 => "access_events.recordTimeStr"
    ];
    $orderColumn = $validColumns[$orderColumnIndex] ?? "access_events.recordTimeStr";
    error_log("Order Column: $orderColumn, Direction: $orderDir");

    // Điều kiện (không có tìm kiếm và lọc status)
    $conditions = [
        "area.project_id" => $project_id
    ];

    // Đếm số bản ghi (recordsFiltered = recordsTotal vì không có tìm kiếm/lọc)
    $filteredRecords = $totalRecords;
    error_log("Filtered Records: $filteredRecords");

    // Lấy dữ liệu logs face với phân trang và sắp xếp
    $logs = $app->select("access_events", [
        "[>]camera" => ["deviceKey" => "id"],
        "[>]area" => ["camera.area_id" => "id"]
    ], [
        "access_events.id",
        "access_events.personName",
        "access_events.personType",
        "access_events.checkImgBase64",
        "camera.id (camera_name)",
        "area.name (area_name)",
        "access_events.recordTime"
    ], array_merge($conditions, [
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderColumn => $orderDir]
    ]));
    error_log("Logs Data: " . print_r($logs, true));

    // Chuẩn bị dữ liệu trả về cho DataTable
    $data = [];
    foreach ($logs as $log) {
        // Ánh xạ personType (giả định 1 = Visitor, 0 = Unknown)
        $event_type = ($log['personType'] == 1) ? "Nhân viên" : "Người lạ";

        // Hiển thị ảnh từ checkImgUrl (nếu có)
        $image = !empty($log['checkImgBase64']) 
            ? "<img src='data:image/jpeg;base64,{$log['checkImgBase64']}' style='max-width: 50px;' 'max-height: 50px;' />" 
            : "N/A";

       

        $data[] = [
            "name" => $log['personName'] ?? 'Unknown',
            "type" => $event_type,
            "area" => $log['area_name'] ?? 'Unknown Area',
            "camera" => $log['camera_name'] ?? 'Unknown Camera',
            "day" => !empty($log['recordTime']) ? date('Y-m-d H:i:s', $log['recordTime'] / 1000) : 'N/A',
            "image" => $image,
            "action" =>  '<a href="/projects/projects-views/logs/view-image?id=' . $log['id'] . '" title="' . $jatbi->lang("Xem Chi Tiết") . '"><i class="ti ti-eye"></i></a>',

        ];
    }
    error_log("Formatted Data: " . print_r($data, true));

    // Trả về JSON cho DataTable
    $response = json_encode([
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $data
    ]);
    error_log("Response: $response");
    echo $response;
})->setPermissions(['project']);

// Route GET: Hiển thị ảnh từ checkImgUrl
$app->router("/projects/projects-views/logs/view-image", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Xem ảnh sự kiện");

    $recordId = $app->xss($_GET['id'] ?? '');

    if (empty($recordId)) {
        echo json_encode(['status'=>'error', "content"=>$jatbi->lang("Không tìm thấy ID sự kiện.")]);
        return;
    }

    $event = $app->select("access_events", ["id", "checkImgUrl"], ["id" => $recordId]);

    $vars['image'] = !empty($event[0]['checkImgUrl']) ? $event[0]['checkImgUrl'] : null;
    $vars['id'] = $recordId;

    echo $app->render('templates/common/view-image-post.html', $vars, 'global');
})->setPermissions(['project']);
?>