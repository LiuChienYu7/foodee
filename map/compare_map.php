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
     <!-- edgeMarker -->
    <script src="leaflet_edgeMarker.js"></script> 
    <title>caompare_map</title>
</head>
<body>
    <div id="map" style="width: 600px; height: 400px;" ></div>
    
    <script>

        // 地圖的點應該要是變數 看使用者選到哪一家店 以那家店為中心
        const map = L.map('map').setView([22.631386, 120.301951], 13);

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
                    var marker = L.marker([restaurants.r_latitude, restaurants.r_longitude], {
                        isRestaurant: true // 設置 isRestaurant 屬性 讓edgeMarker辨別是餐廳
                    }).addTo(map);

                    // 顯示餐廳名稱
                    marker.bindPopup("name: " + restaurants.r_name).openPopup();
                });
            })
            .catch(error => console.error('Error loading restaurant data:', error));
        
        //加上捷運輕軌
        fetch('../connect_sql/get_data_map_json.php')
            .then(response => response.json())
            .then(data => {
                // var LRT_points = []
                var MRT_O_points = []
                var MRT_R_points = []
                var MRT = L.icon({
                            iconUrl: 'mrt.png',
                            iconSize:     [25, 25], // size of the icon
                            // iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
                            // shadowAnchor: [4, 62],  // the same for the shadow
                            // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
                });
                console.log(data);
                data.forEach(function(transportation) {
                    if(transportation.id.includes('C')){
                        // LRT_points.push([transportation.latitude, transportation.longitude])
                        // L.polyline(LRT_points).addTo(map)
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'green',
                            fillColor: 'green',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map);

                    }
                    else if(transportation.id.includes('O')){
                        MRT_O_points.push([transportation.latitude, transportation.longitude])
                        L.polyline(MRT_O_points,{
                            color:'orange',
                            weight: 2
                        }).addTo(map);
                        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map);
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'orange',
                            fillColor: 'orange',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map)
                    }
                    else{
                        MRT_R_points.push([transportation.latitude, transportation.longitude])
                        L.polyline(MRT_R_points,{
                            color:'red',
                            weight: 2
                        }).addTo(map);
                        
                        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map)
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map)
                    }
                    

                    circle.bindPopup("name: " + transportation.name).openPopup();
                });
            })
            .catch(error => console.error('Error loading restaurant data:', error));

        // add the EdgeMarker to the map. 箭頭
        var edgeMarkerLayer = L.edgeMarker({
            icon: L.icon({ // style markers
                iconUrl: 'edge_arrow_marker.png',
                clickable: true,
                iconSize: [25, 25],
                iconAnchor: [10, 10]
            }),
            rotateIcons: true, // rotate EdgeMarkers depending on their relative position
            layerGroup: null // you can specify a certain L.layerGroup to create the edge markers from.
        }).addTo(map);

        // if you want to remove the edge markers
        // edgeMarkerLayer.destroy();
            
    </script>
    
    
</body>
</html>