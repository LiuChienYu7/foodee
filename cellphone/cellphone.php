<?php
ob_start(); 
header('Content-Type: text/html; charset=UTF-8');

// 数据库连接设置
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';

// 建立数据库连接
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

// 初始化变量
$all_restaurant_data = [];
$restaurant_ids = [];
$restaurant_names = [];
$r_ids = [];

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_id = intval($_GET["r_id$i"]);
            $restaurant_ids[] = $r_id;
            $r_ids[] = $r_id;

            $query_name = "SELECT r_name FROM detail2 WHERE r_id = $r_id";
            $result_name = mysqli_query($link, $query_name);

            if ($result_name) {
                $row_name = mysqli_fetch_assoc($result_name);
                $restaurant_names[$r_id] = $row_name['r_name'];
            } else {
                echo "Error in query: " . mysqli_error($link);
                $restaurant_names[$r_id] = 'Unknown';
            }

            $query = "
                SELECT r_name, r_vibe, r_food_dishes, r_price_low, r_price_high, 
                    r_photo_env1, r_photo_env2, r_photo_env3, r_photo_food1, 
                    r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, 
                    r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3, 
                    r_has_parking, r_rating, r_time_low
                FROM additional
                WHERE r_id = $r_id";
            $result = mysqli_query($link, $query);

            if ($result) {
                $restaurant_data = mysqli_fetch_assoc($result);
                $all_restaurant_data[$r_id] = $restaurant_data;
            } else {
                echo "查询出错: " . mysqli_error($link);
                $all_restaurant_data[$r_id] = null;
            }
        }
    }
} else {
    echo "数据库连接失败: " . mysqli_connect_error();
}
mysqli_close($link);

// 初始化接收到的變數
$vibe = isset($_GET['vibe']) ? json_decode(urldecode($_GET['vibe']), true) : [];
$food = isset($_GET['food']) ? json_decode(urldecode($_GET['food']), true) : [];
$price = isset($_GET['price']) ? json_decode(urldecode($_GET['price']), true) : [];
$diningTime = isset($_GET['diningTime']) ? json_decode(urldecode($_GET['diningTime']), true) : [];
$parking = isset($_GET['parking']) ? json_decode(urldecode($_GET['parking']), true) : [];
$spider = isset($_GET['spider']) ? json_decode(urldecode($_GET['spider']), true) : [];
$comment = isset($_GET['comment']) ? json_decode(urldecode($_GET['comment']), true) : [];
$openTime = isset($_GET['openTime']) ? json_decode(urldecode($_GET['openTime']), true) : [];

// 傳遞 PHP 變數到 JavaScript
echo "<script>
    const receivedVibe = " . json_encode($vibe) . ";
    const receivedFood = " . json_encode($food) . ";
    const receivedPrice = " . json_encode($price) . ";
    const receivedDiningTime = " . json_encode($diningTime) . ";
    const receivedParking = " . json_encode($parking) . ";
    const receivedSpider = " . json_encode($spider) . ";
    const receivedComment = " . json_encode($comment) . ";
    const receivedOpenTime = " . json_encode($openTime) . ";
</script>";

function renderTags($items, $selectedItems, $r_id, $delimiter) {
    if (!empty($items)) {
        $tags = explode($delimiter, $items);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            $backgroundColor = isset($selectedItems[$r_id]) && in_array($tag, $selectedItems[$r_id]) ? 'background-color: #fff89e;' : 'background-color: #f5f5f5;';
            echo "<span style='padding: 5px; margin: 5px; border-radius: 5px; $backgroundColor'>" . htmlspecialchars($tag) . "</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../cellphone/cellphone.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script src="../map/leaflet_edgeMarker.js"></script>
</head>

<body>

    <div class="gallery-container">
        <?php
        $index = 0;
        function renderGallerySection($r_id, $restaurant_data, $index, $price, $diningTime, $parking) {
            $activeClass = $index === 0 ? 'active' : '';
            echo "<div class='restaurant-section $activeClass' id='restaurant-$r_id'>";
            echo "<div class='image-container'>";
            $image_fields = [
                'r_photo_env1', 'r_photo_env2', 'r_photo_env3', 
                'r_photo_food1', 'r_photo_food2', 'r_photo_food3', 
                'r_photo_food4', 'r_photo_food5', 'r_photo_door', 
                'r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3'
            ];
            foreach ($image_fields as $index => $field) {
                if (!empty($restaurant_data[$field])) {
                    $activeClass = $index === 0 ? 'active' : '';
                    echo "<img src='" . htmlspecialchars($restaurant_data[$field]) . "' class='gallery-img $activeClass' data-category='environment-{$r_id}' data-index='$index' onclick='nextImage(this)' />";
                }
            }
            echo "</div>";

            echo "<div class='button-container'>";
            $colors = [
                "rgba(255, 112, 174, 0.5)",  // #FF70AE with 50% opacity
                "rgba(133, 180, 255, 0.5)",  // #85B4FF with 50% opacity
                "rgba(255, 206, 71, 0.5)"    // #FFCE47 with 50% opacity
            ];
            $index = 0;
            
            foreach ($GLOBALS['restaurant_names'] as $r_id => $r_name) {
                $color = $colors[$index % count($colors)]; // 根據索引選擇顏色
                echo "<button style='background-color: $color;' onclick='changeRestaurant($r_id)'>" . htmlspecialchars($r_name) . "</button>";
                $index++;
            }
            
            echo "</div>";            

        ?>
            <div id="restaurant-info" class="toggle-content active">
                <?php
                echo "<div class='restaurant-name'>";
                echo "<div>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";
                echo "</div>";

                if (isset($restaurant_data['r_rating'])) {
                    $rating = floatval($restaurant_data['r_rating']);
                    $fullStars = floor($rating);
                    $halfStar = ($rating - $fullStars) >= 0.5;
                    echo "<div class='star-rating'>";
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $fullStars) {
                            echo "<img src='full_star.png' alt='Full Star'>";
                        } elseif ($i == $fullStars && $halfStar) {
                            echo "<img src='half_star.png' alt='Half Star'>";
                        }
                    }
                    echo "</div>";
                }

                echo "<div class='info-row'>";
                $parkingTagClass = isset($parking[$r_id]) && $parking[$r_id] ? 'background-color: #fff89e;' : 'background-color: #f5f5f5;';
                if (isset($restaurant_data['r_has_parking'])) {
                    $parkingImage = $restaurant_data['r_has_parking'] == 1 ? 'parking.png' : 'no_parking.png';
                    echo "<div class='parking-tag' style='display: inline-block; $parkingTagClass'><img src='$parkingImage' alt='Parking Info' width='20px'></div>";
                }

                $diningTimeTagClass = isset($diningTime[$r_id]) && $diningTime[$r_id] ? 'background-color: #fff89e;' : 'background-color: #f5f5f5;';
                if (!empty($restaurant_data['r_time_low'])) {
                    echo "<div class='dining-time-tag' style='display: inline-block; $diningTimeTagClass'>用餐時間: " . htmlspecialchars($restaurant_data['r_time_low']) . "</div>";
                } else {
                    echo "<div class='dining-time-tag' style='display: inline-block; $diningTimeTagClass'>無用餐時間限制</div>";
                }
                
                $priceTagClass = isset($price[$r_id]) && $price[$r_id] ? 'background-color: #fff89e;' : 'background-color: #f5f5f5;';
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "<div class='price-tag' style='$priceTagClass'>$" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']) . "</div>";
                }
                echo "</div>";

                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_vibe'])) {
                    renderTags($restaurant_data['r_vibe'], $GLOBALS['vibe'], $r_id, '，');
                }
                echo "</div>";

                echo "<div class='food-tags'>";
                if (!empty($restaurant_data['r_food_dishes'])) {
                    renderTags($restaurant_data['r_food_dishes'], $GLOBALS['food'], $r_id, '、');
                }
                echo "</div>";

                ?>
            </div>
            <?php
            echo "</div>";
        }

        if ($all_restaurant_data) {
            foreach ($all_restaurant_data as $r_id => $restaurant_data) {
                renderGallerySection($r_id, $restaurant_data, $index, $price, $diningTime, $parking);
                $index++;
            }
        } else {
            echo "<p>No data available for the given restaurant IDs.</p>";
        }
        ?>
    </div>

    <!-- 評論使用的資料庫 -->
    <?php
        // 获取餐厅ID
        $r_ids = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_GET["r_id$i"])) {
                $r_ids[] = intval($_GET["r_id$i"]);
            }
        }

        // 连接数据库并查询数据
        $conn = new mysqli('localhost', 'root', '', 'foodee');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 构建 SQL 查询
        if (!empty($r_ids)) {
            $ids = implode("','", $r_ids); // 将数组中的ID转换为SQL字符串格式

            // 查询餐厅的评论
            $sql = "SELECT * FROM additional WHERE r_id IN ('$ids')";
            $result = $conn->query($sql);

            $data = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[$row['r_id']] = $row; // 以r_id为键保存数据
                }
            }

            // 查询朋友的评论
            $sql_friends = "SELECT * FROM comment WHERE r_id IN ('$ids')";
            $result_friends = $conn->query($sql_friends);

            $friend_comments = array();
            if ($result_friends->num_rows > 0) {
                while ($row = $result_friends->fetch_assoc()) {
                    $friend_comments[] = $row;
                }
            }

            // 將朋友評論合併到原本的數據中
            foreach ($r_ids as $restaurant_id) {
                if (isset($data[$restaurant_id])) {
                    $data[$restaurant_id]['friend_reviews'] = array_filter($friend_comments, function ($comment) use ($restaurant_id) {
                        return $comment['r_id'] == $restaurant_id;
                    });
                }
            }

            // 按照 $r_ids 的顺序重新排序 $data
            $ordered_data = array();
            foreach ($r_ids as $id) {
                if (isset($data[$id])) {
                    $ordered_data[] = $data[$id];
                }
            }

            // 将数据转换为 JSON 格式
            $json_data = json_encode($ordered_data);
        } else {
            // 如果没有 r_id 參數，返回空数据
            $json_data = json_encode([]);
        }

        // 关闭数据库连接
        $conn->close();
        ?>

        <!-- 評論使用資料庫 結束 -->


        <!-- map使用資料庫開始 -->
        <?php
        // 获取餐厅ID
        $r_ids = [];
        for ($i = 1; $i <= 3; $i++) {
            if (isset($_GET["r_id$i"])) {
                $r_ids[] = intval($_GET["r_id$i"]);
            }
        }

        // 连接数据库并查询数据
        $conn = new mysqli('localhost', 'root', '', 'foodee');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // 構建SQL查詢
        if (!empty($r_ids)) {
            $ids = implode("','", $r_ids); // 將數組中的ID轉換為SQL字符串格式
            $sql = "SELECT * FROM detail2 WHERE r_id IN ('$ids')";

            $result = $conn->query($sql);

            $data = array();
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[$row['r_id']] = $row; // 以 r_id 為鍵保存數據
                }
            }

            // 根據 $r_ids 的順序重新排序 $data
            $ordered_data = array();
            foreach ($r_ids as $id) {
                if (isset($data[$id])) {
                    $ordered_data[] = $data[$id];
                }
            }

            // 将数据转换为 JSON 格式
            $detail_data = json_encode($ordered_data);
        } else {
            // 處理沒有 r_id 參數的情況
            $detail_data = json_encode([]);
        }

        // 关闭数据库连接
        $conn->close();
        ?>

        <!-- map使用資料庫 結束 -->

        <div class="info-container">
            <div class="upper-section">
                <script type="text/javascript">
                    // 在PHP中将JSON数据传递给JS
                    const reviewData = <?php echo $json_data; ?>;
                    console.log('reviewData', reviewData);
                </script>
                <!-- <svg class="comment" width="600" height="220"></svg> -->
                <!-- <div class="comment_comment">評論</div> -->
            </div>

            <div class="resizer-horizontal-1"></div> <!-- 新增的水平分隔條 -->

            <div class="middle-section">
                <div class="middle-section1">
                    <script type="text/javascript">
                        const restaurant_data = <?php echo $detail_data; ?>;
                    </script>
                    <svg id="spider-<?php echo $r_id; ?>" class="spider" width="300" height="200"></svg>
                </div>
                <div class="middle-section2">
                    <script type="text/javascript">
                        const restaurant_time = <?php echo $detail_data; ?>;
                    </script>
                    <svg id="openTime-<?php echo $r_id; ?>" class="openTime" width="300" height="250"></svg>
                </div>
            </div>
            <div class="resizer-horizontal-2"></div> <!-- 新增的水平分隔條 -->

            <div class="lower-section">
                <script type="text/javascript">
                    const restaurant_data_detail = <?php echo $detail_data; ?>;
                </script>

                <div id="map" width="250" height="200">
                    <svg id="comment-<?php echo $r_id; ?>" class="map" width="250" height="200"></svg>
                </div>
            </div>

            <!--<div class="button_container">
                <button id="shareButton">分享</button>
            </div>
            
            <script type="text/javascript">
                var globalData = {}; // 用來共享狀態的全局變量
            </script>

            <! <script src="https://d3js.org/d3.v7.min.js"></script> -->
            <script type="module">
                // import '../word_tree/word_tree_modify.js';
                import '../comment/comment.js'
                import '../spider/spider.js';
                import '../openTime/openTime.js'
                import '../map/compare_map.js'
            </script>
            <div id="chat-section">
                <div class="chat">
                    <div id="chat">
                    <?php include '../chat/chat.php'; ?>
                    </div>
                </div>
            </div>

    <script>
        let currentRestaurantId = <?php echo reset($restaurant_ids); ?>;
        function changeRestaurant(r_id) {
            document.getElementById(`restaurant-${currentRestaurantId}`).classList.remove('active');
            document.getElementById(`restaurant-${r_id}`).classList.add('active');
            currentRestaurantId = r_id;
        }

        function nextImage(element) {
            const images = element.parentElement.querySelectorAll('.gallery-img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }

        window.onload = function() {
            // 在接收到的變數中尋找並亮起相關標籤
            function highlightTagsBasedOnReceivedData() {
                const tagElements = document.querySelectorAll('.vibe-tags span, .food-tags span');
                tagElements.forEach(tagElement => {
                    const tagText = tagElement.innerText.trim();
                    const restaurantId = tagElement.closest('.restaurant-section').id.split('-')[1]; // 获取餐厅ID

                    // 检查 receivedVibe 和 receivedFood 中是否有对应的标签
                    if ((receivedVibe[restaurantId] && receivedVibe[restaurantId].includes(tagText)) ||
                        (receivedFood[restaurantId] && receivedFood[restaurantId].includes(tagText))) {
                        tagElement.style.backgroundColor = '#fff89e';
                    }
                });

                // 高亮价钱标签
                Object.keys(receivedPrice).forEach(restaurantId => {
                    if (receivedPrice[restaurantId]) {
                        const priceTag = document.querySelector(`#restaurant-${restaurantId} .price-tag`);
                        if (priceTag) {
                            priceTag.style.backgroundColor = '#fff89e';
                        }
                    }
                });

                // 高亮用餐时间标签
                Object.keys(receivedDiningTime).forEach(restaurantId => {
                    if (receivedDiningTime[restaurantId]) {
                        const diningTimeTag = document.querySelector(`#restaurant-${restaurantId} .dining-time-tag`);
                        if (diningTimeTag) {
                            diningTimeTag.style.backgroundColor = '#fff89e';
                        }
                    }
                });

                // 高亮停车场标签
                Object.keys(receivedParking).forEach(restaurantId => {
                    if (receivedParking[restaurantId]) {
                        const parkingTag = document.querySelector(`#restaurant-${restaurantId} .parking-tag`);
                        if (parkingTag) {
                            parkingTag.style.backgroundColor = '#fff89e';
                        }
                    }
                });

                // 高亮图表背景
                if (receivedSpider[currentRestaurantId]) {
                    document.querySelector('.middle-section1').style.backgroundColor = '#fff89e';
                }
                if (receivedComment[currentRestaurantId]) {
                    document.querySelector('.upper-section').style.backgroundColor = '#fff89e';
                }
                if (receivedOpenTime[currentRestaurantId]) {
                    document.querySelector('.middle-section2').style.backgroundColor = '#fff89e';
                }
            }

            // 檢查傳過來的變數 spider 是否為 true，並亮起 middle-section1 背景
            if (receivedSpider === true) {
                document.querySelector('.middle-section1').style.backgroundColor = '#fff89e';
            }
            if (receivedOpenTime === true) {
                document.querySelector('.middle-section2').style.backgroundColor = '#fff89e';
            }

            highlightTagsBasedOnReceivedData();
        }



    </script>

</body>

</html>

<?php
ob_end_flush();
?>
