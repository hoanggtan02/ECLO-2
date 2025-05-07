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
                        'action' => ['data-url' => '/manager/employee-edit?id=' . $data['id_customer'], 'data-action' => 'modal']
                    ],
                    [
                        'type' => 'button',
                        'name' => $jatbi->lang("Xóa"),
                        'permission' => ['customer.deleted'],
                        'action' => ['data-url' => '/manager/employee-deleted?id=' . $data['id_customer'], 'data-action' => 'modal']
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