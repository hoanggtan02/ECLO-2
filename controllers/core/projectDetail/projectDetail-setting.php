<?php

if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/projects/projects-views/setting", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", [
        "[>]customer" => ["id_customer" => "id"]
    ], [
        "project.name_project",
        "project.startDate",
        "project.endDate",
        "project.webhook_url",
        "customer.name (customer_name)"
    ], ["project.id_project" => $id]);
    $vars['active'] = 'setting'; // Để highlight tab Tổng quan
    $vars['id'] = $id;
    $vars['webhook_url'] = $project[0]['webhook_url'] ?? '';
    if ($project && isset($project[0])) {
        $vars['title'] = $jatbi->lang($project[0]['name_project']);
        $vars['project'] = $project[0];    
    } else {
        $vars['title'] = $jatbi->lang("Project Not Found");
        $vars['project'] = null;
    }
    echo $app->render('templates/project/projectDetail/projectDetail-setting.html', $vars);
})->setPermissions(['project']);

$app->router("/projects/projects-views/setting-webhook", 'GET', function($vars) use ($app, $jatbi) {
    $id = $_GET['id'] ?? '';
    $project = $app->select("project", ['webhook_url'], ["project.id_project" => $id]);
    $vars['webhook_url'] = $project[0]['webhook_url'] ?? '';
    echo $app->render('templates/project/projectDetail/projectDetail-setting-post.html', $vars, 'global');
})->setPermissions(['project']);

$app->router("/projects/projects-views/setting-webhook", 'POST', function($vars) use ($app, $jatbi) {
    $app->header(['Content-Type' => 'application/json']);
    $id = $_GET['id'] ?? '';
    $webhook_url = $_POST['webhook'] ?? '';
    if ($id && $webhook_url) {
        $project = $app->get("project", ["id", "webhook_url"], ["id_project" => $id]);
        if (!$project) {
            echo json_encode([
                'status' => 'error',
                'content' => $jatbi->lang('Dự án không tồn tại'),
            ]);
            return;
        }
        $cameraIds = $app->select("camera", [
            "[>]area" => ["area_id" => "id"]
        ], "camera.id", [
            "area.project_id" => $project['id'],
            "camera.is_active" => 1
        ]);
        if (empty($cameraIds)) {
            echo json_encode([
                'status' => 'error',
                'content' => $jatbi->lang('Không tìm thấy camera nào trong dự án'),
            ]);
            return;
        }
        $old_webhook_url = $project['webhook_url'] ?? '';
        $successCameras = [];
        $error = null;
        foreach ($cameraIds as $cameraId) {
            try {
                $apiData = [
                    'deviceKey' => $cameraId,
                    'secret'    => '123456',
                    'sevUploadRecRecordUrl' => $webhook_url,
                ];
                $headers = [
                    'Authorization: Bearer your_token',
                    'Content-Type: application/x-www-form-urlencoded'
                ];
                // Gửi yêu cầu đến API
                $response = $app->apiPost(
                    'http://camera.ellm.io:8190/api/device/setSevConfig',
                    $apiData,   
                    $headers
                );
                

                $successCameras[] = $cameraId;
                
            } catch (Exception $e) {
                $error = $e->getMessage();

                // ROLLBACK: Quay lại webhook cũ cho các camera đã thành công
                foreach ($successCameras as $camId) {
                    try {
                        $rollbackData = [
                            'deviceKey' => $camId,
                            'secret'    => '123456',
                            'sevUploadRecRecordUrl' => $old_webhook_url,
                        ];
                        $app->apiPost(
                            'http://camera.ellm.io:8190/api/device/setSevConfig',
                            $rollbackData,
                            $headers
                        );
                    } catch (Exception $rollbackError) {
                        // Có thể log rollback lỗi nếu cần
                    }
                }
                echo json_encode([
                    'status' => 'error',
                    'content' => 'Lỗi khi cập nhật webhook cho camera: ' . $jatbi->lang('Một số camera không cập nhật được. Đã khôi phục lại như cũ.'),
                    'debug' => $error
                ]);
                return;
            }
        }
            $app->update("project", [
                "webhook_url" => $webhook_url
            ], [
                "id_project" => $id
            ]);
            echo json_encode([
                'status' => 'success',
                'content' => $jatbi->lang('Cấu hình Webhook URL thành công'),
            ]);
           
    } else {
        echo json_encode([
            'status' => 'error',
            'content' => $jatbi->lang('Cấu hình Webhook URL thất bại'),
        ]);
    }
})->setPermissions(['project']);

$app->router("/api/webhook", 'POST', function($vars) use ($app, $jatbi) {

    $app->header(['Content-Type' => 'application/json']);

    // Lấy dữ liệu từ request
    $input = file_get_contents("php://input");
    file_put_contents("log1.txt", "Raw Input: " . $input . PHP_EOL, FILE_APPEND);   

    // Chuyển đổi từ URL-encoded string thành mảng
    parse_str($input, $decoded_params);

    if (!$decoded_params) {
        file_put_contents("log1.txt", "❌ Lỗi: Không thể parse dữ liệu!" . PHP_EOL, FILE_APPEND);
        echo json_encode(["status" => "error", "message" => "Invalid data format"]);
        return;
    }

    // Ghi log các biến để kiểm tra
    $log_data = "Parsed Data:\n";
    foreach ($decoded_params as $key => $value) {
        $log_data .= "$key: $value\n";
    }
    file_put_contents("log1.txt", $log_data . PHP_EOL, FILE_APPEND);

    // Lấy dữ liệu với kiểm tra giá trị có tồn tại không
    $checkImgUrl    = isset($decoded_params['checkImgUrl']) ? $decoded_params['checkImgUrl'] : null;
    $capFlag        = isset($decoded_params['capFlag']) ? $decoded_params['capFlag'] : null;
    $idCard         = isset($decoded_params['idCard']) ? $decoded_params['idCard'] : null;
    $resultFlag     = isset($decoded_params['resultFlag']) ? $decoded_params['resultFlag'] : 0;
    $fingerFlag     = isset($decoded_params['fingerFlag']) ? $decoded_params['fingerFlag'] : null;
    $cardNo         = isset($decoded_params['cardNo']) ? $decoded_params['cardNo'] : null;
    $verifyStyle    = isset($decoded_params['verifyStyle']) ? $decoded_params['verifyStyle'] : 0;
    $openDoorFlag   = isset($decoded_params['openDoorFlag']) ? $decoded_params['openDoorFlag'] : 0;
    $recordId       = isset($decoded_params['recordId']) ? $decoded_params['recordId'] : null;
    $palmFlag       = isset($decoded_params['palmFlag']) ? $decoded_params['palmFlag'] : null;
    $temperature    = isset($decoded_params['temperature']) ? $decoded_params['temperature'] : null;
    $attach         = isset($decoded_params['attach']) ? $decoded_params['attach'] : null;
    $personType     = isset($decoded_params['personType']) ? $decoded_params['personType'] : 0;
    $recordTimeStr  = isset($decoded_params['recordTimeStr']) ? $decoded_params['recordTimeStr'] : null;
    $voucherCode    = isset($decoded_params['voucherCode']) ? $decoded_params['voucherCode'] : null;
    $direction      = isset($decoded_params['direction']) ? $decoded_params['direction'] : 0;
    $checkImgBase64 = isset($decoded_params['checkImgBase64']) ? $decoded_params['checkImgBase64'] : null;
    $pwdFlag        = isset($decoded_params['pwdFlag']) ? $decoded_params['pwdFlag'] : null;
    $idCardFlag     = isset($decoded_params['idCardFlag']) ? $decoded_params['idCardFlag'] : null;
    $maskFlag       = isset($decoded_params['maskFlag']) ? $decoded_params['maskFlag'] : null;
    $cardFlag       = isset($decoded_params['cardFlag']) ? $decoded_params['cardFlag'] : null;
    $deviceKey      = isset($decoded_params['deviceKey']) ? $decoded_params['deviceKey'] : null;
    $personName     = isset($decoded_params['personName']) ? urldecode($decoded_params['personName']) : null;
    $recordTime     = isset($decoded_params['recordTime']) ? $decoded_params['recordTime'] : null;
    $strangerFlag   = isset($decoded_params['strangerFlag']) ? $decoded_params['strangerFlag'] : 0;
    $personSn       = isset($decoded_params['personSn']) ? $decoded_params['personSn'] : null;
    $faceFlag       = isset($decoded_params['faceFlag']) ? $decoded_params['faceFlag'] : 0;

    // Chuẩn bị dữ liệu chèn vào bảng `access_events`
    $insert = [
        "checkImgUrl"    => $checkImgUrl,
        "capFlag"        => $capFlag,
        "idCard"         => $idCard,
        "resultFlag"     => $resultFlag,
        "fingerFlag"     => $fingerFlag,
        "cardNo"         => $cardNo,
        "verifyStyle"    => $verifyStyle,
        "openDoorFlag"   => $openDoorFlag,
        "recordId"       => $recordId,
        "palmFlag"       => $palmFlag,
        "temperature"    => $temperature,
        "attach"         => $attach,
        "personType"     => $personType,
        "recordTimeStr"  => $recordTimeStr,
        "voucherCode"    => $voucherCode,
        "direction"      => $direction,
        "checkImgBase64" => $checkImgBase64,
        "pwdFlag"        => $pwdFlag,
        "idCardFlag"     => $idCardFlag,
        "maskFlag"       => $maskFlag,
        "cardFlag"       => $cardFlag,
        "deviceKey"      => $deviceKey,
        "personName"     => $personName,
        "recordTime"     => $recordTime,
        "strangerFlag"   => $strangerFlag,
        "personSn"       => $personSn,
        "faceFlag"       => $faceFlag
    ];
    $app->insert("record", $insert);
})->setPermissions(['project']);

?>