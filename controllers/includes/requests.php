<?php
    if (!defined('ECLO')) die("Hacking attempt");
    $requests = [
        "main"=>[
            "name"=>$jatbi->lang("Chính"),
            "item"=>[
                '/'=>[
                    "menu"=>$jatbi->lang("Trang chủ"),
                    "url"=>'/',
                    "icon"=>'<i class="ti ti-dashboard"></i>',
                    "controllers"=>"controllers/core/main.php",
                    "main"=>'true',
                    "permission" => "",
                ],
            ],
        ],
        "personnel"=>[
            "name"=>'Nhân sự',
            "item"=>[
                'hr_config'=>[
                    "menu"=>$jatbi->lang("Cấu hình nhân sự"),
                    "url"=>'/staffConfiguration/department',
                    "icon"=>'<i class="ti ti-settings"></i>',
                    "controllers" => [
                        "controllers/core/staffConfiguration.php",
                        "controllers/core/latetime.php",
                        "controllers/core/timeperiod.php",
                        "controllers/core/leavetype.php",
                    ],
                    "main"=>'false',
                    "permission" => [
                        'staffConfiguration'=>$jatbi->lang("Cấu hình nhân sự"),
                        'staffConfiguration-department.add'=>$jatbi->lang("Thêm nhân sự"),
                        'staffConfiguration-department.edit'=>$jatbi->lang("sửa nhân sự"),
                        'staffConfiguration-department.delete'=>$jatbi->lang("Xóa nhân sự"),
                        'staffConfiguration-position'=>$jatbi->lang("Chức vụ"),
                        'staffConfiguration-position.add'=>$jatbi->lang("Thêm chức vụ"),
                        'staffConfiguration-position.edit'=>$jatbi->lang("sửa chức vụ"),
                        'staffConfiguration-position.delete'=>$jatbi->lang("Xóa chức vụ"),
                        'staffConfiguration-salary'=>$jatbi->lang("Tiền lương"),
                        'staffConfiguration-salary.add'=>$jatbi->lang("Thêm tiền lương"),
                        'staffConfiguration-salary.edit'=>$jatbi->lang("sửa tiền lương"),
                        'staffConfiguration-salary.delete'=>$jatbi->lang("Xóa tiền lương"),
                        'staffConfiguration-holiday'=>$jatbi->lang("Ngày lễ"),
                        'staffConfiguration-holiday.add'=>$jatbi->lang("Thêm ngày lễ"),
                        'staffConfiguration-holiday.edit'=>$jatbi->lang("sửa ngày lễ"),
                        'staffConfiguration-holiday.delete'=>$jatbi->lang("Xóa ngày lễ"),  
                        'timeperiod' => $jatbi->lang("Khung thời gian"),
                        'timeperiod.add' => $jatbi->lang("Thêm Khung thời gian"),
                        'timeperiod.edit' => $jatbi->lang("Sửa Khung thời gian"),
                        'timeperiod.deleted' => $jatbi->lang("Xóa Khung thời gian"),
                        'leavetype' => $jatbi->lang("Loại nghỉ phép"),
                        'leavetype.add' => $jatbi->lang("Thêm Loại nghỉ phép"),
                        'leavetype.edit' => $jatbi->lang("Sửa Loại nghỉ phép"),
                        'leavetype.deleted' => $jatbi->lang("Xóa Loại nghỉ phép"),
                        'latetime' => $jatbi->lang("Đi muộn về sớm"),
                        'latetime.add' => $jatbi->lang("Thêm Đi muộn về sớm"),
                        'latetime.edit' => $jatbi->lang("Sửa Đi muộn về sớm"),
                        'latetime.deleted' => $jatbi->lang("Xóa Đi muộn về sớm"),

                    ],
                ],
            ],
        ],
    ];
    foreach($requests as $request){
        foreach($request['item'] as $key_item =>  $items){
            if (is_array($items['controllers'])) {
                foreach($items['controllers'] as $controller) {
                    $setRequest[] = [
                        "key" => $key_item,
                        "controllers" => $controller,
                    ];
                }
            } else {
                $setRequest[] = [
                    "key" => $key_item,
                    "controllers" => $items['controllers'],
                ];
            }
            // Thêm controllers từ sub
            if (isset($items['sub']) && is_array($items['sub'])) {
                foreach ($items['sub'] as $sub_key => $sub_item) {
                    if (isset($sub_item['controllers'])) {
                        $setRequest[] = [
                            "key" => $sub_key,
                            "controllers" => $sub_item['controllers'],
                        ];
                    }
                }
            }
            if($items['main']!='true'){
                $SelectPermission[$items['menu']] = $items['permission'];
            }
            if (isset($items['permission']) && is_array($items['permission'])) {
                foreach($items['permission'] as $key_per => $per) {
                    $userPermissions[] = $key_per; 
                }
            }
        }
    }
?>