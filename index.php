<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

include 'DbConnect.php';
$onjDB = new DbConnect();
$conn = $onjDB->connect();

$method = $_SERVER['REQUEST_METHOD'];
switch ($method) {
    case "POST": {
        $user = json_decode(file_get_contents('php://input'));

        $checkSql = "SELECT * FROM user WHERE username = :username";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bindParam(':username', $user->name);
        $checkStmt->execute();
        
        if ($checkStmt->rowCount() > 0) {
            $response = ['status' => 0, 'msg' => 'Tên người dùng đã tồn tại. Vui lòng chọn tên khác.'];
            echo json_encode($response);
            exit;
        }

        $sql = "INSERT INTO user (username, email, pass, created_at) VALUES (:username, :email, :pass, :created_at)";
        $stmt = $conn->prepare($sql);
        $created_at = date('Y-m-d');
        $stmt->bindParam(':username', $user->name);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':pass', $user->password);
        $stmt->bindParam(':created_at', $created_at);

        if ($stmt->execute()) {
            $response = ['status' => 1, 'msg' => 'Đã tạo tài khoản thành công.'];
        } else {
            $response = ['status' => 0, 'msg' => 'Không thể tạo tài khoản.'];
        }
        
        echo json_encode($response);
        break;
    }
    case "GET":{
        if (isset($_GET['username']) && isset($_GET['password'])) {
            $username = $_GET['username'];
            $password = $_GET['password'];

            // Kiểm tra thông tin đăng nhập
            $checkSql = "SELECT * FROM user WHERE username = :username";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bindParam(':username', $username);
            $checkStmt->execute();

            if ($checkStmt->rowCount() > 0) {
                $row = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($row['pass'] === $password) {
                    $response = [
                        'status' => 1,
                        'msg' => 'Đăng nhập thành công.',
                        'token' => 'dummy-token' // Thay bằng token thực tế nếu có
                    ];
                } else {
                    $response = ['status' => 0, 'msg' => 'Mật khẩu không đúng.'];
                }
            } else {
                $response = ['status' => 0, 'msg' => 'Tên người dùng không tồn tại.'];
            }
        } else {
            $response = ['status' => 0, 'msg' => 'Vui lòng cung cấp tên người dùng và mật khẩu.'];
        }

        echo json_encode($response);
        break;
    }
}
