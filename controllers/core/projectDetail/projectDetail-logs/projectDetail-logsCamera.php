<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/projects/projects-views/logs/logs-camera", 'GET', function($vars) use ($app, $jatbi) {
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
    $vars['active2'] = 'camera'; // Để highlight tab Camera
    $vars['id'] = $id;
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-logs/projectDetail-log-logsCamera.html', $vars);
})->setPermissions(['project']);

$app->router("/projects/projects-views/logs/logs-camera", 'POST', function($vars) use ($app, $jatbi) {
    $app->header(['Content-Type' => 'application/json']);
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 0;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $orderName = isset($_POST['order'][0]['name']) ? $_POST['order'][0]['name'] : 'date';
    $orderDir = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'desc';
    $id = $_GET['id'] ?? '';
    // Lấy project.id từ id_project
    $project = $app->get("project", "id", ["id_project" => $id, "status" => 'A']);
    if (!$project) {
        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => 0,
            "recordsFiltered" => 0,
            "data" => []
        ]);
        return;
    }


    // Lấy danh sách camera thuộc dự án
    $cameraIds = $app->select("camera", [
        "[>]area" => ["area_id" => "id"]
    ], "camera.id", [
        "area.project_id" => $project,
        "camera.is_active" => 'A',
        "area.is_active" => 'A',
    ]);
    if (empty($cameraIds)) {
        $cameraIds = ['-1']; // Tránh lỗi nếu không có camera
    }

    $where = [
        "AND" => [
            "logs.dispatch" => 'camera',
            "logs.deleted" => 0,
        ],
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderName => strtoupper($orderDir)]
    ];

    // Đếm số bản ghi
    $count = $app->count("logs", $where['AND']);
    error_log("Count: " . $count);

    // Lấy dữ liệu mà không dùng callback
    $results = $app->select("logs", [
        'logs.id',
        'logs.action',
        'logs.date',
        'logs.content',
    ], $where);

    error_log("Raw results: " . json_encode($results));

    $datas = [];
    foreach ($results as $data) {
        $content = json_decode($data['content'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON decode error: " . json_last_error_msg() . " for content: " . ($data['content'] ?? 'empty'));
            $content = [];
        }
        error_log("Parsed content: " . json_encode($content));
        if (isset($content['camera'])) {
            $cameraId = $content['camera'];
            if (!in_array($cameraId, $cameraIds)) {
                continue; // Bỏ qua nếu camera không thuộc dự án
            }else{
                $datas[] = [
                    "action" => $data['action'] ?? 'N/A',
                    "area" => $content['area'] ?? 'N/A',
                    "camera" => $content['camera'] ?? 'N/A',
                    "result" => $content['result'] ?? 'N/A',
                    "content" => $content['description'] ?? 'N/A',
                    "day" => $data['date'] ?? '',
                ];
            }

        } else {
            error_log("Camera ID not found in content: " . json_encode($content));
            continue; // Bỏ qua nếu không có camera
        }
    }
    // Cập nhật count dựa trên số bản ghi sau khi lọc
    $count = count($datas);
    error_log("Count after filtering for project $id: " . $count);
    error_log("Final data: " . json_encode($datas));
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $datas ?? []
    ]);
})->setPermissions(['project']);

function addFaceRecognition($app, $jatbi, $cameraId, $imageUrl) {
    // Lấy thông tin khu vực và camera
    $camera = $app->get("camera", "*", ["id" => $cameraId]);
    $area = $app->get("area", "name", ["id" => $camera['area_id']]);

    // Chuẩn bị dữ liệu log
    $logContent = [
        "description" => "Thêm thành công",
        "type" => "Nhận diện khuôn mặt",
        "area" => $area ?? 'N/A',
        "camera" => $camera['id'] ?? 'N/A',
        "image" => $imageUrl,
        "result" => "1",
    ];

    // Ghi log
    $jatbi->logs('camera', 'addface', $logContent);

    file_put_contents("my_custom_log.txt", "Area: " . print_r($area, true) . "\n", FILE_APPEND);

}

$app->router("/projects/test", 'GET', function($vars) use ($app, $jatbi) {
    addFaceRecognition($app, $jatbi, "77ed8738f236e8df86", "");
})->setPermissions(['project']);
?>