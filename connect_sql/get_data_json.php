<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>get data json</title>
</head>
<body>
    <?php
    header('Content-Type: application/json');

    include("connect.php");

    // 查詢資料庫
    $sql = "
            SELECT *
            FROM 
                additional_ a
            JOIN 
                detail d ON a.r_id = d.r_id
            JOIN 
                review r ON a.r_id = r.r_id
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

</body>
</html>