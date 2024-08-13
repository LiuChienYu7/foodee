<?php
ob_start(); // Start output buffering
?>
<?php
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

// 初始化變數
$all_restaurant_data = [];
$current_restaurant_index = 0;

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // Get restaurant IDs from GET parameters
    $r_ids = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_ids[] = intval($_GET["r_id$i"]);
        }
    }

    // Fetch images, names, vibes, dishes, prices, and ratings for each restaurant
    foreach ($r_ids as $r_id) {
        $query = "SELECT r_name, r_vibe, r_food_dishes, r_price_low, r_price_high, r_photo_env1, r_photo_env2, r_photo_env3, r_photo_food1, r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3,
                         r_rating, special_comment_sum, notice_comment_sum
                  FROM compare
                  WHERE r_id = $r_id";
        $result = mysqli_query($link, $query);

        if ($result) {
            $restaurant_data = mysqli_fetch_assoc($result);
            $all_restaurant_data[$r_id] = $restaurant_data;
        } else {
            echo "Error in query: " . mysqli_error($link);
            $all_restaurant_data[$r_id] = null;
        }
    }
} else {
    echo "Failed to connect to the database: " . mysqli_connect_error();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="0807.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <link rel="stylesheet" href="../word_tree/word_tree.css">
    <style>
        .restaurant-section {
            display: none;
        }
        .active-restaurant {
            display: block;
        }
        .nav-button {
            margin: 10px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .nav-button:hover {
            background-color: #45a049;
        }

        /* CSS for image slider */
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: auto;
        }

        .mySlides img {
            width: 100%; /* 設定圖片寬度為100% */
            height: 300px; /* 固定圖片高度 */
            object-fit: cover; /* 圖片的大小會根據容器大小進行調整，保持內容不被拉伸 */
        }

        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 25%;
            width: auto;
            /*margin-top: -22px;*/
            padding: 16px;
            color: white;
            font-weight: bold;
            font-size: 18px;
            transition: 0.6s ease;
            border-radius: 0 3px 3px 0;
            user-select: none;
        }

        .next {
            right: 0;
            border-radius: 3px 0 0 3px;
        }

        .prev:hover, .next:hover {
            background-color: rgba(0,0,0,0.8);
        }

        .info {
            width: 50%; /* 限制info區域寬度不超過頁面50% */
            margin: 0px 5px;
        }

        .restaurant-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0px;
        }

        .star-rating {
            margin-top: 5px;
        }

        .star-rating img {
            height: 20px; /* 設定統一的高度 */
            vertical-align: middle; /* 對齊方式 */
        }

        .vibe-tags, .food-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px; /* 控制標籤間距 */
            margin: 10px 0;
            font-size: 20px;
        }

        .vibe-tags .restaurant-tag {
            display: inline-block;
            background-color: #f1f1f1;
            padding: 5px;
            margin-right: 5px;
            border-radius: 5px;
        }

        .price-tag {
            font-weight: bold;
            color: #555;
        }

        .gallery-container {
            margin: 20px 0;
        }

        /* 新增的CSS，确保 .middle-graph 和 .info 高度一致 */
        .middle {
            display: flex;
            flex-direction: row;
            width: 100%;
            align-items: stretch; /* 保证子元素高度一致 */
        }

        .middle-graph {
            flex: 1;
            background-color: #f9f9f9;
            padding: 10px;
            margin-left: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }
    </style>
        <!-- openTime -->
        <link rel="stylesheet" href="../openTime/openTime.css" />
</head>
<body>
    <div class="container">
        <div class="gallery-container">
            <?php
            function renderGallerySection($r_id, $restaurant_data, $index) {
                $activeClass = $index === 0 ? 'active-restaurant' : '';
                echo "<div class='restaurant-section $activeClass' id='restaurant-$index'>";

                // Image slider container
                echo "<div class='slideshow-container'>";

                // Environment images
                $env_images = ['r_photo_food1', 'r_photo_food2', 'r_photo_food3', 'r_photo_food4', 'r_photo_food5', 'r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3','r_photo_env1', 'r_photo_env2', 'r_photo_env3', 'r_photo_door'];
                foreach ($env_images as $field_index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        echo "<div class='mySlides environment-{$r_id}'>";
                        echo "<img src='{$restaurant_data[$field]}' alt='Restaurant Image'>";
                        echo "</div>";
                    }
                }

                // 包裝餐廳名字、星級評分和價格標籤
                echo "<div class='middle'>";
                
                // Left section (info)
                echo "<div class='info'>";
                echo "<div class='restaurant-name'>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";
                
                // Render star rating
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

                // Display vibe tags
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_vibe'])) {
                    $vibes = explode('，', $restaurant_data['r_vibe']);
                    foreach ($vibes as $vibe) {
                        echo "<div class='restaurant-tag'>" . htmlspecialchars(trim($vibe)) . "</div>";
                    }
                }
                echo "</div>";

                // Display price range
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "<div class='price-tag'> $" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']) . "</div>";
                }
                echo "</div>";

                echo "</div>"; // End of info
                
                // Right section (middle-graph)
                echo "<div class='middle-graph'>";
                include '../openTime/openTime.php'; // 包含openTime.php生成的圖表
                echo "</div>";

                echo "</div>"; // End of middle
                
                echo "<a class='prev' onclick='plusSlides(-1, \"environment-{$r_id}\")'>&#10094;</a>";
                echo "<a class='next' onclick='plusSlides(1, \"environment-{$r_id}\")'>&#10095;</a>";

                echo "</div>";
            }

            if ($all_restaurant_data) {
                $index = 0;
                foreach ($all_restaurant_data as $r_id => $restaurant_data) {
                    if ($restaurant_data) {
                        renderGallerySection($r_id, $restaurant_data, $index);
                        $index++;
                    } else {
                        echo "<p>No data available for restaurant ID: $r_id.</p>";
                    }
                }
            } else {
                echo "<p>No data available for the given restaurant IDs.</p>";
            }
            ?>
            <div class="navigation-buttons">
                <?php if (count($all_restaurant_data) > 1): ?>
                    <button class="nav-button" onclick="changeRestaurant(-1)">Previous Restaurant</button>
                    <button class="nav-button" onclick="changeRestaurant(1)">Next Restaurant</button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let currentRestaurantIndex = 0;
        const totalRestaurants = <?php echo count($all_restaurant_data); ?>;

        let slideIndex = {};

        function plusSlides(n, category) {
            showSlides(slideIndex[category] += n, category);
        }

        function showSlides(n, category) {
            let slides = document.getElementsByClassName(`mySlides ${category}`);
            if (!slideIndex[category]) {
                slideIndex[category] = 1;
            }

            if (n > slides.length) { slideIndex[category] = 1 }
            if (n < 1) { slideIndex[category] = slides.length }

            for (let i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }

            slides[slideIndex[category] - 1].style.display = "block";  
        }

        function changeRestaurant(direction) {
            document.getElementById(`restaurant-${currentRestaurantIndex}`).classList.remove('active-restaurant');
            currentRestaurantIndex = (currentRestaurantIndex + direction + totalRestaurants) % totalRestaurants;
            document.getElementById(`restaurant-${currentRestaurantIndex}`).classList.add('active-restaurant');

            // 重置每個餐廳的slideIndex，顯示第一張圖片
            let categories = document.querySelectorAll(`#restaurant-${currentRestaurantIndex} .mySlides`);
            categories.forEach((slide) => {
                let category = slide.classList[1];  // Assumes the second class is the category
                slideIndex[category] = 1;
                showSlides(slideIndex[category], category);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            let categories = document.querySelectorAll('.mySlides');
            categories.forEach((slide) => {
                let category = slide.classList[1];  // Assumes the second class is the category
                if (!slideIndex[category]) {
                    slideIndex[category] = 1;
                }
                showSlides(slideIndex[category], category);
            });
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
