<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <title>總評視覺化</title>
    <link rel="stylesheet" href="./comment.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
</head>

<body>
    <?php
    // 获取餐厅ID
    $r_id1 = $_GET['r_id1'];
    $r_id2 = $_GET['r_id2'];
    $r_id3 = $_GET['r_id3'];

    // 连接数据库并查询数据
    $conn = new mysqli('localhost', 'root', '', 'foodee');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT * FROM additional WHERE r_id IN ('$r_id1', '$r_id2', '$r_id3')";
    $result = $conn->query($sql);

    $data = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // 将数据转换为 JSON 格式
    $json_data = json_encode($data);
    // 关闭数据库连接
    $conn->close();

    ?>

    <div class="upper-section">
        <span class="comment_comment">評論</span>
    </div>

    <script type="text/javascript">
        // 在PHP中将JSON数据传递给JS
        const reviewData = <?php echo $json_data; ?>;
        // 你可以先檢查reviewData的內容
        console.log(reviewData);
    </script>

    <script type="module">
        import './comment2.0.js';
    </script>
</body>

</html>