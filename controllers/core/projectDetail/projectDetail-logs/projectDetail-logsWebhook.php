<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/projects/projects-views/logs/logs-webhook", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id, "project.status" => 'A']);
    $vars['active'] = 'logs'; // Để highlight tab Tổng quan
    $vars['active2'] = 'webhook'; // Để highlight tab Camera
    $vars['id'] = $id;
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-logs/projectDetail-log-logsWebhook.html', $vars);
})->setPermissions(['project']);
$app->router("/projects/projects-views/logs/logs-webhook", 'POST', function($vars) use ($app, $jatbi) {
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
    ], "*", ["area.project_id" => $project_id, "amera.is_active" => 'A', "area.is_active" => 'A']);
    error_log("Total Records: $totalRecords");

    // Xử lý sắp xếp cột
    $orderColumnIndex = $_POST['order'][0]['column'] ?? 0;
    $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');
    $validColumns = [
        1 => "area.name",
        2 => "camera.id",
        3 => "camera.device_code",
        4 => "access_events.recordTimeStr"
    ];
    $orderColumn = $validColumns[$orderColumnIndex] ?? "access_events.recordTimeStr";

    // Điều kiện (không có tìm kiếm và lọc status)
    $conditions = [
        "area.project_id" => $project_id,
        "camera.status" => 'A',
        "area.is_active" => 'A',
        "project.status" => 'A',
    ];
    // Đếm số bản ghi (recordsFiltered = recordsTotal vì không có tìm kiếm/lọc)
    $filteredRecords = $totalRecords;

    // Lấy dữ liệu logs face với phân trang và sắp xếp
    $logs = $app->select("access_events", [
        "[>]camera" => ["deviceKey" => "id"],
        "[>]area" => ["camera.area_id" => "id"],
        "[>]project" => ["area.project_id" => "id"]
    ], [
        "access_events.id",
        "access_events.personName (people_name)",
        "access_events.personType",
        "access_events.checkImgBase64",
        "camera.id (camera_name)",
        "area.name (area_name)",
        "access_events.recordTime",
        "project.webhook_url (webhook_url)",
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
        $data[] = [
            "action" => $log['webhook_url'],
            "area" => $log['area_name'] ?? 'Unknown Area',
            "camera" => $log['camera_name'] ?? 'Unknown Camera',
            "result" => 200,
            "description" => $log['people_name'] ?? 'Unknown',
            "date" => !empty($log['recordTime']) ? date('Y-m-d H:i:s', $log['recordTime'] / 1000) : 'N/A',
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

?>