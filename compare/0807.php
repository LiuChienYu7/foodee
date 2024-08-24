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

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // Get restaurant IDs from GET parameters
    $r_ids = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_ids[] = intval($_GET["r_id$i"]);
        }
    }

    // Fetch images, names, vibes, dishes, and prices for each restaurant
    foreach ($r_ids as $r_id) {
        $query = "SELECT r_name, r_vibe, r_food_dishes, r_price_low, r_price_high, r_photo_env1, r_photo_env2, r_photo_env3, r_photo_food1, r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3,
                         special_comment_sum, notice_comment_sum
                  FROM additional
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

    <!-- map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" /> <!--leaflet css file-->
    <link rel="stylesheet" href="../map/compare_map.css">
    <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- edgeMarker -->
    <script src="../map/leaflet_edgeMarker.js"></script>

    <!-- openTime -->
    <link rel="stylesheet" href="../openTime/openTime.css" />
</head>

<body>
    <div class="container">
        <div class="gallery-container">
            <?php
            // Helper function to render image gallery for each restaurant
            function renderGallerySection($r_id, $restaurant_data)
            {
                echo "<div class='gallery-section'>";
                echo "<div class='restaurant-name'>";
                echo "<div>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";
                echo "</div>";

                // Display environment images
                echo "<h3>Environment</h3>";
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_vibe'])) {
                    $vibes = explode('，', $restaurant_data['r_vibe']);
                    foreach ($vibes as $vibe) {
                        echo "<div class='restaurant-tag'>" . htmlspecialchars(trim($vibe)) . "</div>";
                    }
                }
                echo "</div>";
                echo "<div class='image-container'>";
                foreach (['r_photo_env1', 'r_photo_env2', 'r_photo_env3', 'r_photo_door'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' data-category='environment-{$r_id}' data-index='$index' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='changeImage(this, -1)'>‹</span>";
                echo "<span class='nav-arrow next' onclick='changeImage(this, 1)'>›</span>";
                echo "</div>";

                // Display food images
                echo "<h3>Food</h3>";
                echo "<div class='food-tags'>";
                if (!empty($restaurant_data['r_food_dishes'])) {
                    $dishes = explode('、', $restaurant_data['r_food_dishes']);
                    foreach ($dishes as $dish) {
                        echo "<div class='restaurant-tag'>" . htmlspecialchars(trim($dish)) . "</div>";
                    }
                }
                echo "</div>";
                echo "<div class='image-container'>";
                foreach (['r_photo_food1', 'r_photo_food2', 'r_photo_food3', 'r_photo_food4', 'r_photo_food5'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' data-category='food-{$r_id}' data-index='$index' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='changeImage(this, -1)'>‹</span>";
                echo "<span class='nav-arrow next' onclick='changeImage(this, 1)'>›</span>";
                echo "</div>";

                // Display price range
                echo "<h3>MENU</h3>";
                echo "<div class='vibe-tags'>";
                if (!empty($restaurant_data['r_price_low']) && !empty($restaurant_data['r_price_high'])) {
                    echo "<div class='price-tag'>Price Range: $" . htmlspecialchars($restaurant_data['r_price_low']) . " ~ $" . htmlspecialchars($restaurant_data['r_price_high']) . "</div>";
                }
                echo "</div>";
                echo "<div class='image-container'>";
                foreach (['r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' data-category='menu-{$r_id}' data-index='$index' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='changeImage(this, -1)'>‹</span>";
                echo "<span class='nav-arrow next' onclick='changeImage(this, 1)'>›</span>";
                echo "</div>";

                // Display collapsible comments
                echo "<div class='collapsible-comments'>";
                echo "<button type='button' class='comments-button' onclick='toggleComments(this)'>Comments</button>";
                echo "<div class='content'>";
                echo "<h4>Special Comments</h4>";
                if (!empty($restaurant_data['special_comment_sum'])) {
                    $special_comments = explode('。', $restaurant_data['special_comment_sum']);
                    foreach ($special_comments as $comment) {
                        echo "<p> " . htmlspecialchars(trim($comment)) . "</p>";
                    }
                }
                echo "<h4>Notice Comments</h4>";
                if (!empty($restaurant_data['notice_comment_sum'])) {
                    $notice_comments = explode('。', $restaurant_data['notice_comment_sum']);
                    foreach ($notice_comments as $comment) {
                        echo "<p> " . htmlspecialchars(trim($comment)) . "</p>";
                    }
                }
                echo "</div>";
                echo "</div>";

                echo "</div>";
            }

            if ($all_restaurant_data) {
                foreach ($all_restaurant_data as $r_id => $restaurant_data) {
                    if ($restaurant_data) {
                        renderGallerySection($r_id, $restaurant_data);
                    } else {
                        echo "<p>No data available for restaurant ID: $r_id.</p>";
                    }
                }
            } else {
                echo "<p>No data available for the given restaurant IDs.</p>";
            }
            ?>
        </div>
        <!-- 評論使用的資料庫 -->
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
        if ($result) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
            }
        } else {
            echo "Error in query: " . $conn->error;
        }

        // 将数据转换为 JSON 格式
        $json_data = json_encode($data);
        // 关闭数据库连接
        $conn->close();
        ?>
        <!-- 評論使用資料庫 結束 -->

        <div class="info-container">
            <div class="upper-section">
                <div class="comment_comment">評論</div>
                <!-- <svg class="word_tree" width="300" height="200"></svg> -->
            </div>

            <!-- <div class="resizer-horizontal-1"></div> 新增的水平分隔條 -->

            <div class="middle-section">
                <svg class="spider" width="300" height="200"></svg>
                <!-- <svg class = "openDay_Time" width="300" height="300"></svg>  -->
            </div>

            <div class="middle-section2" style="flex: auto;">
                <?php include '../openTime/openTime.php'; ?>
            </div>
            <!-- <div class="resizer-horizontal-2"></div> 新增的水平分隔條 -->

            <div class="lower-section">
                <div id="map" width="300" height="250">
                    <svg class="map" width="280" height="280"></svg>
                </div>
            </div>

            <div class="button_container">
                <button id="shareButton">分享</button>
            </div>

            <script type="text/javascript">
                // 在PHP中将JSON数据传递给JS
                const reviewData = <?php echo $json_data; ?>;
            </script>
            <script type="module">
                // import '../word_tree/word_tree_modify.js';
<<<<<<< HEAD
                import '../comment/comment.js'
=======
>>>>>>> 7dc7bf3f09547a25cfda8cafb5ead3a6b4cec13c
                import '../spider/spider.js';
                import '../map/compare_map.js'
                import '../comment_new/comment2.0.js'
            </script>
        </div>
    </div>

    <!-- The Modal -->
    <div id="imageModal" class="modal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
        <span class="modal-prev" onclick="prevModalImage()">‹</span>
        <span class="modal-next" onclick="nextModalImage()">›</span>
    </div>

    <script>
        let currentCategory = '';
        let currentIndex = 0;
        let currentImgSrc = '';

        function openModal(element) {
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("modalImg");
            modal.style.display = "block";
            modalImg.src = element.src;
            currentCategory = element.dataset.category;
            currentIndex = parseInt(element.dataset.index, 10);
            currentImgSrc = element.src;
        }

        function closeModal() {
            const modal = document.getElementById("imageModal");
            modal.style.display = "none";
        }

        function prevImage(arrow) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            images[currentIndex].classList.add('active');
        }

        function nextImage(arrow) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + 1) % images.length;
            images[currentIndex].classList.add('active');
        }

        function changeImage(arrow, direction) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('img');
            let currentIndex = Array.from(images).findIndex(img => img.classList.contains('active'));
            images[currentIndex].classList.remove('active');
            currentIndex = (currentIndex + direction + images.length) % images.length;
            images[currentIndex].classList.add('active');
        }

        function prevModalImage() {
            const modalImg = document.getElementById("modalImg");
            const images = Array.from(document.querySelectorAll(`.image-container img[data-category='${currentCategory}']`));
            currentIndex = (currentIndex - 1 + images.length) % images.length;
            currentImgSrc = images[currentIndex].src;
            modalImg.src = currentImgSrc;
        }

        function nextModalImage() {
            const modalImg = document.getElementById("modalImg");
            const images = Array.from(document.querySelectorAll(`.image-container img[data-category='${currentCategory}']`));
            currentIndex = (currentIndex + 1) % images.length;
            currentImgSrc = images[currentIndex].src;
            modalImg.src = currentImgSrc;
        }

        function toggleComments(button) {
            const comments = button.nextElementSibling;
            const isExpanded = comments.classList.contains("show");

            if (isExpanded) {
                comments.classList.remove("show");
                comments.classList.remove("slide-down");
                comments.classList.add("slide-up");
                button.innerText = "Comments";
            } else {
                comments.classList.add("show");
                comments.classList.remove("slide-up");
                comments.classList.add("slide-down");
                button.innerText = "Hide Comments";
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const coll = document.getElementsByClassName("collapsible");
            for (let i = 0; i < coll.length; i++) {
                coll[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    const content = this.nextElementSibling;
                    const arrow = this.querySelector('.arrow');
                    if (content.classList.contains("show")) {
                        content.classList.remove("show");
                        arrow.classList.remove("arrow-up");
                    } else {
                        content.classList.add("show");
                        arrow.classList.add("arrow-up");
                    }
                });
            }
        });
    </script>
</body>

</html>
<?php
ob_end_flush();
?>
