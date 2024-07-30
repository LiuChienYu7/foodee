<?php
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // Get restaurant IDs from GET parameters
    $r_ids = [];
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_ids[] = intval($_GET["r_id$i"]);
        }
    }

    // Fetch images and names for each restaurant
    $all_restaurant_data = [];
    foreach ($r_ids as $r_id) {
        $query = "SELECT r_name, r_photo_env1, r_photo_env2, r_photo_env3, r_photo_food1, r_photo_food2, r_photo_food3, r_photo_food4, r_photo_food5, r_photo_door, r_photo_menu1, r_photo_menu2, r_photo_menu3 
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
    $all_restaurant_data = null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="compare_index.css">
</head>
<body>
    <div class="container">
        <div class="gallery-container">
            <?php
            // Helper function to render image gallery for each restaurant
            function renderGallerySection($r_id, $restaurant_data) {
                echo "<div class='gallery-section'>";
                echo "<div class='restaurant-name'>" . htmlspecialchars($restaurant_data['r_name']) . "</div>";

                // Display environment images
                echo "<h3>Environment</h3>";
                echo "<div class='image-container'>";
                foreach (['r_photo_env1', 'r_photo_env2', 'r_photo_env3', 'r_photo_door'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='prevImage(this)'>&#10094;</span>";
                echo "<span class='nav-arrow next' onclick='nextImage(this)'>&#10095;</span>";
                echo "</div>";

                // Display food images
                echo "<h3>Food</h3>";
                echo "<div class='image-container'>";
                foreach (['r_photo_food1', 'r_photo_food2', 'r_photo_food3', 'r_photo_food4', 'r_photo_food5'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='prevImage(this)'>&#10094;</span>";
                echo "<span class='nav-arrow next' onclick='nextImage(this)'>&#10095;</span>";
                echo "</div>";

                // Display other images
                echo "<h3>Others</h3>";
                echo "<div class='image-container'>";
                foreach (['r_photo_menu1', 'r_photo_menu2', 'r_photo_menu3'] as $index => $field) {
                    if (!empty($restaurant_data[$field])) {
                        $activeClass = $index === 0 ? 'active' : '';
                        echo "<img src='{$restaurant_data[$field]}' class='gallery-img $activeClass' onerror='this.onerror=null;this.src=\"fallback.jpg\";' onclick='openModal(this)' />";
                    }
                }
                echo "<span class='nav-arrow prev' onclick='prevImage(this)'>&#10094;</span>";
                echo "<span class='nav-arrow next' onclick='nextImage(this)'>&#10095;</span>";
                echo "</div>";

                echo "</div>";
            }

            // Display the galleries for each restaurant
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
        <div class="info-container">
            <!-- This section can be used for additional content as per your design. -->
            <h2>Additional Information</h2>
            <p>Details, charts, and other elements can go here.</p>
        </div>
    </div>

    <!-- The Modal -->
    <div id="myModal" class="modal">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <span class="modal-prev" onclick="changeModalImage(-1)">&#10094;</span>
        <span class="modal-next" onclick="changeModalImage(1)">&#10095;</span>
        <div class="modal-content">
            <img id="modalImage" src="" alt="Expanded Image">
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        let currentImageArray = [];

        // JavaScript for navigating images in gallery
        function prevImage(arrow) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('.gallery-img');
            images[currentImageIndex].classList.remove('active');
            currentImageIndex = (currentImageIndex -1 + images.length) % images.length;
            images[currentImageIndex].classList.add('active');
        }

        function nextImage(arrow) {
            const section = arrow.closest('.image-container');
            const images = section.querySelectorAll('.gallery-img');
            images[currentImageIndex].classList.remove('active');
            currentImageIndex = (currentImageIndex + 1) % images.length;
            images[currentImageIndex].classList.add('active');
        }

        // Function to open the modal and display the clicked image
        function openModal(imgElement) {
            currentImageIndex = Array.from(imgElement.parentNode.children).filter(child => child.tagName === 'IMG').indexOf(imgElement);
            currentImageArray = Array.from(imgElement.parentNode.children).filter(child => child.tagName === 'IMG');
            const modal = document.getElementById("myModal");
            const modalImg = document.getElementById("modalImage");
            modal.style.display = "block";
            modalImg.src = imgElement.src;
        }

        // Function to change modal image
        function changeModalImage(direction) {
            currentImageIndex = (currentImageIndex + direction + currentImageArray.length) % currentImageArray.length;
            document.getElementById("modalImage").src = currentImageArray[currentImageIndex].src;
        }

        // Function to close the modal
        function closeModal() {
            document.getElementById("myModal").style.display = "none";
        }
    </script>
</body>
</html>
