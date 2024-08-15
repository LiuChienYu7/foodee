<?php
ob_start(); // Start output buffering

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

    // 獲取GET參數中的餐廳ID
    $r_ids = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_ids[] = intval($_GET["r_id$i"]);
        }
    }

    // 根據餐廳ID獲取圖片、名稱、vibe、菜餚、價格和評分
    foreach ($r_ids as $r_id) {
        $query = "
            SELECT r_name, r_vibe, r_food_dishes, r_price_low, r_price_high, 
                   r_photo_env1, r_photo_env2, r_photo_env3, 
                   r_photo_food1, r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, 
                   r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3,
                   r_rating, special_comment_sum, notice_comment_sum, r_has_parking
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
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        /* 基本樣式設置 */
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

        /* 圖片輪播的樣式 */
        .slideshow-container {
            position: relative;
            max-width: 100%;
            margin: auto;
        }

        .mySlides img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .prev, .next {
            cursor: pointer;
            position: absolute;
            top: 20%;
            width: auto;
            margin-top: -22px;
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
            margin: 0 auto;
        }

        .restaurant-name {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0px;
        }

        .star-rating img {
            height: 20px;
            vertical-align: middle;
        }

        .vibe-tags, .food-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin: 5px 0;
            font-size: 15px;
            font-weight: normal;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="gallery-container">
            <?php
            function renderGallerySection($r_id, $restaurant_data, $index) {
                $activeClass = $index === 0 ? 'active-restaurant' : '';
                echo "<div class='restaurant-section $activeClass' id='restaurant-$index'>";

                // 圖片輪播容器
                echo "<div class='slideshow-container'>";

                // 環境圖片
                $env_images = [
                    'r_photo_food1', 'r_photo_food2', 'r_photo_food3', 'r_photo_food4', 'r_photo_food5', 
                    'r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3', 
                    'r_photo_env1', 'r_photo_env2', 'r_photo_env3', 'r_photo_door'
                ];
                foreach ($env_images as $field_index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        echo "<div class='mySlides environment-{$r_id}'>";
                        echo "<img src='" . htmlspecialchars($restaurant_data[$field]) . "' alt='Restaurant Image'>";
                        echo "</div>";
                    }
                }

                // 餐廳名稱、評分和價格標籤
                echo "<div class='info'>";
                
                // 餐廳名稱
                echo "<div class='restaurant-name'>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";
                
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
                        }
                    }
                    
                    echo "</div>";
                }
                

                // 顯示vibe標籤
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

                // 顯示價格範圍
                echo "<div class='price-tag'>";
                // 顯示停車資訊
                if (isset($restaurant_data['r_has_parking'])) {
                    $parkingImage = $restaurant_data['r_has_parking'] == 1 ? 'parking.png' : 'no_parking.png';
                    echo "<div><img src='$parkingImage' alt='Parking Info' width='20px'></div>";
                }
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "$" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']);
                }
                echo "</div>";

                

                echo "</div>"; // 結束 info
                

                // 圖片導航箭頭
                echo "<a class='prev' onclick='plusSlides(-1, \"environment-{$r_id}\")'>&#10094;</a>";
                echo "<a class='next' onclick='plusSlides(1, \"environment-{$r_id}\")'>&#10095;</a>";

                echo "</div>"; // 結束 slideshow-container
                echo "</div>"; // 結束 restaurant-section
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
                let category = slide.classList[1];
                slideIndex[category] = 1;
                showSlides(slideIndex[category], category);
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            let categories = document.querySelectorAll('.mySlides');
            categories.forEach((slide) => {
                let category = slide.classList[1];
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
