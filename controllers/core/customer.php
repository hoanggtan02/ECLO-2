<?php
if (!defined('ECLO')) die("Hacking attempt");
$jatbi = new Jatbi($app);
$setting = $app->getValueData('setting');

$app->router("/customer", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Khách hàng");
    echo $app->render('templates/customer/customer.html', $vars);
})->setPermissions(['customer']);

$app->router("/customer", 'POST', function($vars) use ($app, $jatbi) {
    $app->header(['Content-Type' => 'application/json']);

    // Nhận dữ liệu từ DataTable
    $draw = $_POST['draw'] ?? 0;
    $start = $_POST['start'] ?? 0;
    $length = $_POST['length'] ?? 10;
    $searchValue = $_POST['search']['value'] ?? '';
    $type = $_POST['type'] ?? '';

    // Fix lỗi ORDER cột
    $orderColumnIndex = $_POST['order'][0]['column'] ?? 1; // Mặc định cột SN
    $orderDir = strtoupper($_POST['order'][0]['dir'] ?? 'DESC');

    // Danh sách cột hợp lệ
    $validColumns = ["checkbox", "id_customer", "name", "tax", "phone","address"];
    $orderColumn = $validColumns[$orderColumnIndex] ?? "id_customer";

    // Điều kiện lọc dữ liệu
    $where = [
        "AND" => [
            "OR" => [
                "customer.id_customer[~]" => $searchValue,
                "customer.name[~]" => $searchValue,
            ]
        ],
        "LIMIT" => [$start, $length],
        "ORDER" => [$orderColumn => $orderDir]
    ];

    // if (!empty($type)) {
    //     $where["AND"]["employee.type"] = $type;
    // }

    // Đếm số bản ghi
    $count = $app->count("customer", ["AND" => $where["AND"]]);

    // Truy vấn danh sách nhân viên
    // $datas = $app->select("customer", [
    //     // "[>]department" => ["departmentId" => "departmentId"]
    //     ""
    // ], [
    //     "customer.id_customer",
    //     "customer.name",
    //     "customer.tax",
    //     "customer.phone",
    //     "customer.address",
    // ], $where) ?? [];
    $datas = $app->select("customer",[
        "customer.id_customer",
        "customer.name",
        "customer.tax",
        "customer.phone",
        "customer.address",
    ], $where) ?? [];


    // Xử lý dữ liệu đầu ra
    $formattedData = array_map(function($data) use ($app, $jatbi) {
        return [
            "checkbox" => $app->component("box", ["data" => $data['id_customer']]),
            "id_customer" => $data['id_customer'],
            "name" => $data['name'],
            "tax" => $data['tax'],
            "phone" => $data['phone'],
            "address" => $data['address'],
            // "status" => $app->component("status", ["url" => "/employee-status/" . $data['id_customer'], "data" => $data['status'], "permission" => ['employee.edit']]),
            "action" => $app->component("action", [
                "button" => [
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xem"),
                        'permission' => ['customer'],
                        'action' => ['data-url' => '/manager/face-viewimage?box=' . $data['id_customer'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Sửa"),
                        'permission' => ['customer.edit'],
                        'action' => ['data-url' => '/customer-edit?id=' . $data['id_customer'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xóa"),
                        'permission' => ['customer.deleted'],
                        'action' => ['data-url' => '/customer-deleted?id=' . $data['id_customer'], 'data-action' => 'modal']
                    ],
                ] 
            ]),
            // "view" => '<a href="/manager/employee-detail?box=' . $data['sn'] . '" title="' . $jatbi->lang("Xem Chi Tiết") . '"><i class="ti ti-eye"></i></a>',
        ];
    }, $datas);

    // Trả về dữ liệu JSON
    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $count,
        "recordsFiltered" => $count,
        "data" => $formattedData
    ]);
})->setPermissions(['customer']);

$app->router("/customer-add", 'GET', function($vars) use ($app, $jatbi, $setting) {
    $vars['title'] = $jatbi->lang("Khách hàng");
    echo $app->render('templates/customer/customer-post.html', $vars, 'global');
})->setPermissions(['customer.add']);

$app->router("/customer-add", 'POST', function($vars) use ($app, $jatbi) {
    $app->header(['Content-Type' => 'application/json']);

    $name      = $app->xss($_POST['name'] ?? '');
    $email     = $app->xss($_POST['email'] ?? '');
    $tax       = $app->xss($_POST['tax'] ?? '');
    $fax       = $app->xss($_POST['fax'] ?? '');
    $phone     = $app->xss($_POST['phone'] ?? '');
    $website   = $app->xss($_POST['website'] ?? '');
    $birthday  = $app->xss($_POST['birthday'] ?? '');
    $address   = $app->xss($_POST['address'] ?? '');
    $note      = $app->xss($_POST['note'] ?? '');

    if (empty($name)) {
        echo json_encode([
            "status" => "error",
            "content" => $jatbi->lang("Tên không được để trống")
        ]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        echo json_encode([
            "status" => "error",
            "content" => $jatbi->lang("Email không hợp lệ")
        ]);
        return;
    }

    try {
        $id_customer = 'CUST-' . $app->randomString(3) . $app->randomNumber(3);

        $insert = [
            "id_customer" => $id_customer,
            "name"        => $name,
            "email"       => $email,
            "tax"         => $tax,
            "fax"         => $fax,
            "phone"       => $phone,
            "website"     => $website,
            "birthday"    => $birthday,
            "address"     => $address,
            "note"        => $note,
        ];

        $app->insert("customer", $insert);
        echo json_encode([
            "status" => "success",
            "content" => $jatbi->lang("Đã thêm khách hàng thành công")
        ]);
    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "content" => "Lỗi: " . $e->getMessage()
        ]);
    }
})->setPermissions(['customer.add']);


$app->router("/customer-deleted", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Xóa khách hàng");
    echo $app->render('templates/common/deleted.html', $vars, 'global');
})->setPermissions(['customer.deleted']);

$app->router("/customer-deleted", 'POST', function($vars) use ($app, $jatbi) {
    $app->header([
        'Content-Type' => 'application/json',
    ]);

    // Kiểm tra xem có 'id' hay 'box' trong request không
    $snList = [];

    if (!empty($_GET['id'])) {
        $snList[] = $app->xss($_GET['id']);
    } elseif (!empty($_GET['box'])) {
        $snList = array_map('trim', explode(',', $app->xss($_GET['box'])));
    }

    if (empty($snList)) {
        echo json_encode(["status" => "error", "content" => "Thiếu ID nhân viên để xóa"]);
        return;
    }

    try {
        $errors = [];
        foreach ($snList as $sn) {
            if (empty($sn)) continue; // Bỏ qua nếu có giá trị rỗng

            // Xóa khỏi database
            $app->delete("customer", ["id_customer" => $sn]);
        }

        if (!empty($errors)) {
            echo json_encode([
                "status" => "error",
                "content" => "Một số khách hàng xóa thất bại",
                "errors" => $errors
            ]);
        } else {
            echo json_encode([
                "status" => "success",
                "content" => "Đã xóa thành công khách hàng"
            ]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "content" => "Lỗi: " . $e->getMessage()]);
    }
})->setPermissions(['customer.deleted']);

$app->router("/customer-edit", 'GET', function($vars) use ($app, $jatbi) {
    $vars['title'] = $jatbi->lang("Sửa khách hàng");

    $id_customer = $app->xss($_GET['id'] ?? null);
    if (!$id_customer) {
        echo $app->render('templates/common/error-modal.html', $vars, 'global');
        return;
    }

    $data = $app->get("customer", "*", ["id_customer" => $id_customer]);

    if ($data) {
        $vars['data'] = $data;
        $vars['data']['edit'] = true;
        echo $app->render('templates/customer/customer-post.html', $vars, 'global');
    } else {
        echo $app->render('templates/common/error-modal.html', $vars, 'global');
    }
})->setPermissions(['customer.edit']);

$app->router("/customer-edit", 'POST', function($vars) use ($app, $jatbi) {
    $app->header(['Content-Type' => 'application/json']);

    $id_customer = $app->xss($_POST['id_customer'] ?? null);

    if (!$id_customer) {
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Mã khách hàng không hợp lệ")]);
        return;
    }

    $data = $app->get("customer", "*", ["id_customer" => $id_customer]);
    if (!$data) {
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Không tìm thấy khách hàng")]);
        return;
    }

    // Lấy dữ liệu đầu vào từ form
    $name      = $app->xss($_POST['name'] ?? '');
    $email     = $app->xss($_POST['email'] ?? '');
    $tax       = $app->xss($_POST['tax'] ?? '');
    $fax       = $app->xss($_POST['fax'] ?? '');
    $phone     = $app->xss($_POST['phone'] ?? '');
    $website   = $app->xss($_POST['website'] ?? '');
    $birthday  = $app->xss($_POST['birthday'] ?? '');
    $address   = $app->xss($_POST['address'] ?? '');
    $note      = $app->xss($_POST['note'] ?? '');

    if (empty($name)) {
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Tên không được để trống")]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) {
        echo json_encode(["status" => "error", "content" => $jatbi->lang("Email không hợp lệ")]);
        return;
    }

    try {
        $update = [
            "name"     => $name,
            "email"    => $email,
            "tax"      => $tax,
            "fax"      => $fax,
            "phone"    => $phone,
            "website"  => $website,
            "birthday" => $birthday,
            "address"  => $address,
            "note"     => $note,
        ];

        $app->update("customer", $update, ["id_customer" => $id_customer]);

        echo json_encode(["status" => "success", "content" => $jatbi->lang("Cập nhật khách hàng thành công")]);
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "content" => "Lỗi: " . $e->getMessage()]);
    }
})->setPermissions(['customer.edit']);
