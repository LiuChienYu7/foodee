<?php
ob_start(); // 开启输出缓冲
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

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // 从 URL 查询参数获取餐厅 ID
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_id = intval($_GET["r_id$i"]);
            $restaurant_ids[] = $r_id;

            // 查询每个餐厅的名称
            $query_name = "SELECT r_name FROM detail WHERE r_id = $r_id";
            $result_name = mysqli_query($link, $query_name);

            if ($result_name) {
                $row_name = mysqli_fetch_assoc($result_name);
                $restaurant_names[$r_id] = $row_name['r_name'];
            } else {
                echo "Error in query: " . mysqli_error($link);
                $restaurant_names[$r_id] = 'Unknown';
            }

            // 查询每个餐厅的详细信息
            $query = "
                SELECT r_name, r_vibe, r_food_dishes, r_price_low, r_price_high, 
                    r_photo_env1, r_photo_env2, r_photo_env3, r_photo_food1, 
                    r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, 
                    r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3, 
                    r_has_parking, r_rating
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
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./cellphone.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>

    <!-- Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="../map/leaflet_edgeMarker.js"></script>

    <!-- openTime -->
    <link rel="stylesheet" href="../openTime/openTime.css" />
    <style>
        .gallery-img {
            width: 100%;
            height: auto;
            max-height: 100%;
            display: none;
            object-fit: cover;
            transition: opacity 0.5s ease-in-out;
        }
        .gallery-img.active {
            display: block;
            opacity: 1;
        }

        .image-container {
            position: relative;
            width: 100%;
            height: 200px;
            overflow: hidden;
        }

        .restaurant-section {
            display: none;
        }
        .restaurant-section.active {
            display: block;
        }

        .button-container {
            display: flex;
            flex-wrap: wrap;
            margin: 5px 0;
            justify-content: center;
            position: relative;
        }

        .button-container button {
            width: 20px; /* 默认宽度 */
            padding: 5px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            transition: width 0.3s, background-color 0.3s, color 0.3s; /* 增加宽度的平滑过渡 */
            text-align: center;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            position: relative;
        }

        .button-container button:hover {
            width: auto; /* 悬停时自动调整宽度 */
            background-color: #4CAF50;
            color: white;
            max-width: none; /* 防止宽度被限制 */
            z-index: 1; /* 确保悬停时按钮在其他元素上方 */
        }

        .toggle-container {
            display: flex;
            justify-content: center;
            margin: 10px 0;
        }

        .toggle-button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 150px;
            text-align: center;
        }

        .toggle-button:hover {
            background-color: #45a049;
        }

        .toggle-content {
            display: none;
        }

        .toggle-content.active {
            display: block;
        }
    </style>
</head>

<body>

    <div class="toggle-container">
        <button class="toggle-button" onclick="toggleView()">切換</button>
    </div>

    <div class="gallery-container">
        <?php
        $index = 0;
        function renderGallerySection($r_id, $restaurant_data, $index) {
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

            // 将按钮放置在图片下方
            echo "<div class='button-container'>";
            foreach ($GLOBALS['restaurant_names'] as $r_id => $r_name) {
                echo "<button onclick='changeRestaurant($r_id)'>" . htmlspecialchars($r_name) . "</button>";
            }
            echo "</div>";
        ?>
            <div id="restaurant-info" class="toggle-content active">
                <?php
                // 顯示餐廳名稱
                echo "<div class='restaurant-name'>";
                echo "<div>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";
                echo "</div>";

                // 顯示星級評分
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
                        } else {
                            echo "<img src='empty_star.png' alt='Empty Star'>";
                        }
                    }
                    echo "</div>";
                }

                // 顯示價格範圍和停車資訊在同一行
                echo "<div class='info-row'>";
                if (isset($restaurant_data['r_has_parking'])) {
                    $parkingImage = $restaurant_data['r_has_parking'] == 1 ? 'parking.png' : 'no_parking.png';
                    echo "<div style='display: inline-block;'><img src='$parkingImage' alt='Parking Info' width='20px'></div>";
                }
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "<div class='price-tag' style='display: inline-block; margin-left: 10px;'>$" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']) . "</div>";
                }
                echo "</div>";

                // 顯示氣氛標籤
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_vibe'])) {
                    $vibes = explode('，', $restaurant_data['r_vibe']);
                    foreach ($vibes as $vibe) {
                        echo "<div class='restaurant-tag'>" . htmlspecialchars(trim($vibe)) . "</div>";
                    }
                }
                echo "</div>";

                // 顯示菜餚標籤
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_food_dishes'])) {
                    $dishes = explode('、', $restaurant_data['r_food_dishes']);
                    foreach ($dishes as $dish) {
                        echo "<div class='restaurant-tag'>" . htmlspecialchars(trim($dish)) . "</div>";
                    }
                }
                echo "</div>";
                ?>
            </div>
            <?php
            echo "</div>";
        }

        if ($all_restaurant_data) {
            foreach ($all_restaurant_data as $r_id => $restaurant_data) {
                renderGallerySection($r_id, $restaurant_data, $index);
                $index++;
            }
        } else {
            echo "<p>No data available for the given restaurant IDs.</p>";
        }
        ?>
    </div>

    <div id="chat-section" class="toggle-content">
        <div class="chat">
            <div id="chat">
            <?php include '../chat/chat.php'; ?>
            </div>
        </div>
    </div>

    <div class="middle-section">
        <?php include '../spider/spider_my_data.php'; ?>
    </div>

    <div class="middle-section2" style="flex: auto;">
        <?php include '../openTime/openTime.php'; ?>
    </div>

    <div class="lower-section">
        <div id="map" width="300" height="250">
        <?php include '../map/compare_map.php'; ?>
        </div>
    </div>


    <script>
        let currentRestaurantId = <?php echo reset($restaurant_ids); ?>; // 默认显示第一个餐厅
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

        function toggleView() {
            const restaurantInfo = document.getElementById('restaurant-info');
            const chatSection = document.getElementById('chat-section');

            if (restaurantInfo.classList.contains('active')) {
                restaurantInfo.classList.remove('active');
                chatSection.classList.add('active');
            } else {
                chatSection.classList.remove('active');
                restaurantInfo.classList.add('active');
            }
        }
    </script>
</body>

</html>
<?php
ob_end_flush();
?>
