<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
     integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
     crossorigin=""/> <!--leaflet css file-->
     <link  rel="stylesheet" src="compare_map.css" >
     <!-- Make sure you put this AFTER Leaflet's CSS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
     integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
     crossorigin=""></script>
     <title>caompare_map</title>
</head>
<body>
    <div id="map" style="width: 600px; height: 400px;" ></div>
    <?php
        //連資料庫
        $where_condition = "r_rate_value > 3";
        include("../connect_sql/get_data.php");

    ?>
    
    <script>

        // 地圖的點應該要是變數 看使用者選到哪一家店 以那家店為中心
        const map = L.map('map').setView([22.63151, 120.30132], 14);

        const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);
        
        // 從JSON文件加載數據並顯示在地圖上
        fetch('../connect_sql/get_data_json.php')
            .then(response => response.json())
            .then(data => {
                console.log(data);
                data.forEach(function(restaurants) {
                    var marker = L.marker([restaurants.r_latitude, restaurants.r_longitude]).addTo(map);
                    // 顯示餐廳名稱
                    marker.bindPopup("name: " + restaurants.r_name).openPopup();
                });
            })
            .catch(error => console.error('Error loading restaurant data:', error));
        

    </script>
    
    
</body>
</html>