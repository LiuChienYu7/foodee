<?php
    header('Content-Type: application/json');

    include("connect.php");

    // 檢查是否設置了查詢條件
    if (!isset($where_condition)) {
        die("No query condition specified.");
    }

    // 聯合查詢，使用傳遞的查詢條件
    $sql = "
        SELECT *
        FROM 
            additional_ a
        JOIN 
            detail d ON a.r_id = d.r_id
        JOIN 
            review r ON a.r_id = r.r_id
        WHERE 
            $where_condition
    ";
    
    $result = $conn->query($sql);

    $restaurants = array();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $restaurants[] = $row;
        }
    } else {
        echo json_encode([]);
        exit();
    }

    echo json_encode($restaurants);

    $conn->close();
?>

