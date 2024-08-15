<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <link rel="stylesheet" href="compare_map.css">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="leaflet_edgeMarker.js"></script>
    <script src="https://d3js.org/d3.v7.min.js"></script> <!-- 添加 D3.js -->
    <style>
        /* 定義CSS樣式 */
        .custom-icon {
            width: 40px;
            height: 40px;
        }
        #map {
            width: 1200px;
            height: 800px;
        }
    </style>
</head>
<body>
    <div id="map"></div>
    <script>
        // 初始化地圖
        const map = L.map('map').setView([22.631386, 120.301951], 13);
        const mapTilerLayer = L.tileLayer('https://api.maptiler.com/maps/dataviz/{z}/{x}/{y}.png?key=nVkGoaPMkOqdVRLChAnz', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.maptiler.com/">MapTiler</a> contributors'
        }).addTo(map);
        
        if (typeof L.edgeMarker === 'function') {
            var edgeMarker = L.edgeMarker({
                position: [51.5, -0.09],
                // 其他選項
            }).addTo(map);
        } else {
            console.error('L.edgeMarker is not defined.');
        }

        // 創建D3.js圖標
        function createD3Icon(url) {
            const svg = d3.create("svg")
                .attr("width", 40)
                .attr("height", 40)
                .attr("viewBox", "0 0 40 40");

            // 定義 mask
            svg.append("defs").append("mask")
                .attr("id", "circle-mask")
                .append("circle")
                .attr("cx", 20)
                .attr("cy", 20)
                .attr("r", 20)
                .attr("fill", "white");

            // 定義圖片
            svg.append("image")
                .attr("xlink:href", url)
                .attr("width", 40)
                .attr("height", 40)
                .attr("mask", "url(#circle-mask)");

            // 添加圓形邊框（可選）
            svg.append("circle")
                .attr("cx", 20)
                .attr("cy", 20)
                .attr("r", 20)
                .attr("stroke", "black")
                .attr("stroke-width", 2)
                .attr("fill", "none");

            return svg.node().outerHTML;
        }

        // 創建自定義的 Leaflet 圖標
        function createLeafletD3Icon(url) {
            const iconHtml = createD3Icon(url);
            return L.divIcon({
                className: 'custom-icon',
                html: iconHtml,
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // 加載餐廳數據並使用自定義圖標
            fetch('../connect_sql/get_data_json.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach(function (restaurant) {
                        var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
                            icon: createLeafletD3Icon(restaurant.r_photo_env1)
                        }).addTo(map);

                        // 顯示餐廳名稱
                        marker.bindPopup("name: " + restaurant.r_name).openPopup();
                    });
                })
                .catch(error => console.error('Error loading restaurant data:', error));
        });

        // 加上捷運輕軌
        fetch('../connect_sql/get_data_map_json.php')
            .then(response => response.json())
            .then(data => {
                var LRT_points = [];
                var MRT_O_points = [];
                var MRT_R_points = [];
                console.log(data);
                data.forEach(function (transportation) {
                    if (transportation.id.includes('C')) {
                        LRT_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(LRT_points, {
                            color: 'green',
                            weight: 2
                        }).addTo(map);
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'green',
                            fillColor: 'green',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map);
                    } else if (transportation.id.includes('O')) {
                        MRT_O_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(MRT_O_points, {
                            color: 'orange',
                            weight: 2
                        }).addTo(map);
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'orange',
                            fillColor: 'orange',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map);
                    } else {
                        MRT_R_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(MRT_R_points, {
                            color: 'red',
                            weight: 2
                        }).addTo(map);
                        var circle = L.circle([transportation.latitude, transportation.longitude], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.9,
                            radius: 35
                        }).addTo(map);
                    }
                    circle.bindPopup("name: " + transportation.name).openPopup();
                });
            })
            .catch(error => console.error('Error loading MRT/LRT data:', error));

        // add the EdgeMarker to the map. 箭頭
        var edgeMarkerLayer = L.edgeMarker({
            icon: L.icon({
                iconUrl: 'edge_arrow_marker.png',
                clickable: true,
                iconSize: [25, 25],
                iconAnchor: [10, 10]
            }),
            rotateIcons: true,
            layerGroup: null
        }).addTo(map);

        // 餐廳和捷運輕軌連線
        fetch('../connect_sql/get_data_json.php')
            .then(response => response.json())
            .then(restaurants => {
                fetch('../connect_sql/get_data_map_json.php')
                    .then(response => response.json())
                    .then(transportations => {
                        const stationMap = {};
                        transportations.forEach(station => {
                            stationMap[station.name] = {
                                lat: station.latitude,
                                lng: station.longitude
                            };
                        });

                        const lineGroup = L.layerGroup().addTo(map);

                        restaurants.forEach(restaurant => {
                            const restaurantLatLng = [restaurant.r_latitude, restaurant.r_longitude];

                            let shortestDistance = Infinity;
                            let shortestLine = null;

                            console.log(`Processing restaurant: ${restaurant.r_name}`);

                            if (restaurant.r_MRT) {
                                const mrtStationName = restaurant.r_MRT.replace('站', '');
                                const mrtStation = stationMap[mrtStationName];
                                if (mrtStation) {
                                    const mrtDistance = parseFloat(restaurant.r_MRT_dist_km);
                                    console.log(`MRT Station: ${mrtStationName}, Distance: ${mrtDistance} km`);
                                    if (mrtDistance < shortestDistance) {
                                        shortestDistance = mrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [mrtStation.lat, mrtStation.lng]], {
                                            color: 'blue'
                                        });
                                    }
                                }
                            }

                            if (restaurant.r_LRT) {
                                const lrtStationName = restaurant.r_LRT;
                                const lrtStation = stationMap[lrtStationName];
                                if (lrtStation) {
                                    const lrtDistance = parseFloat(restaurant.r_LRT_dist_km);
                                    console.log(`LRT Station: ${lrtStationName}, Distance: ${lrtDistance} km`);
                                    if (lrtDistance < shortestDistance) {
                                        shortestDistance = lrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [lrtStation.lat, lrtStation.lng]], {
                                            color: 'green'
                                        });
                                    }
                                }
                            }

                            if (shortestLine) {
                                console.log(`Shortest Line for ${restaurant.r_name}: ${shortestDistance} km`);
                                lineGroup.addLayer(shortestLine);
                            }
                        });

                        const zoomThreshold = 15;

                        map.on('zoomend', () => {
                            if (map.getZoom() >= zoomThreshold) {
                                map.addLayer(lineGroup);
                            } else {
                                map.removeLayer(lineGroup);
                            }
                        });

                        if (map.getZoom() >= zoomThreshold) {
                            map.addLayer(lineGroup);
                        } else {
                            map.removeLayer(lineGroup);
                        }
                    })
                    .catch(error => console.error('Error loading MRT/LRT data:', error));
            })
            .catch(error => console.error('Error loading restaurant data:', error));
    </script>
</body>
</html>
