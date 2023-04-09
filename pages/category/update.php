<?php 
    include_once dirname(__FILE__).'/../../include/settings.php';

    $categoryObj = new Categories();

    if(empty($_POST['full_name']) ) {
        $error = "Please fill all fields";
    }
    else {

        $photo = $_FILES['image'];
        $uploaded = false;
        $image = "";
        if(isset($photo) && count($photo) ) {
            if($photo['error'] == 0) {
                $img = explode('.', $photo['name']);
                $photo['dst_path'] 	= dirname(__FILE__).'/../../uploads/products/';
                
                $image = time().'.'.$img[1];

                if (!file_exists($photo['dst_path'])) {

                    mkdir($photo['dst_path'], 0777, true);

                }
                
                $moved = move_uploaded_file($photo['tmp_name'], $photo['dst_path'].$image);
                if($moved) {	
                    $uploaded = true;
                }
        
            }
        }


        var_dump($uploaded);
        var_dump($image);



        $data = [                
            'id' => $_POST['id'],
            'full_name' => $_POST['full_name'],
            'groupName' => $_POST['groupName'],
            'cat_type' => $_POST['cat_type'],
            'image' => $uploaded ? $image : '',

        ];

        $update = $categoryObj->updateCategory($data);

        if(!empty($data['account_id'])) {
            $de = new DoubleEntry();
            $de->setOpeningBalance($data['account_id'], $data['opening_balance']);
        }

        if($update) {
            $message = "Successfully Assigned!";
        } else {
            $message = "Nothing change";
        }
    }

    if(!empty($error)) {
        echo json_encode(['success' => false, 'error' => $error]);
    }
    if(!empty($message)) {
        echo json_encode(['success' => true, 'message' => $message]);
    }