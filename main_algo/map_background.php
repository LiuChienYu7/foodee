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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
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

        // 餐厅图标的更新函数
        function updateIconSize(marker, zoomLevel) {
            const size = zoomLevel * 5; // 调整倍率，按需修改
            const newIcon = marker.options.icon;
            newIcon.options.iconSize = [size, size];
            newIcon.options.iconAnchor = [size / 2, size / 2]; // 确保图标中心点正确对齐
            marker.setIcon(newIcon);
        }

        // 创建自定义的 Leaflet 图标
        function createLeafletD3Icon(restaurant) {
            const iconHtml = createD3Icon(restaurant); 
            return L.divIcon({
                className: 'custom-icon',
                html: iconHtml,
                iconAnchor: [20, 20] // 默认图标中心点
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // 加载餐厅数据并使用自定义图标
            fetch('../connect_sql/get_data_json.php')
                .then(response => response.json())
                .then(data => {
                    data.forEach((restaurant, index) => {
                        if (restaurant && Object.keys(restaurant).length > 0) {
                            // 创建餐厅标记
                            var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
                                icon: createLeafletD3Icon(restaurant)
                            }).addTo(map);

                            // 初始化图标大小
                            updateIconSize(marker, map.getZoom());

                            // 当地图缩放时更新图标大小
                            map.on('zoomend', function() {
                                updateIconSize(marker, map.getZoom());
                            });

                            // 设置弹出窗口内容
                            marker.bindPopup(`
                                <div style="width: 300px; max-width: 300px; padding: 10px;">
                                    <div style="display: flex; align-items: center;">
                                        <div id="carousel-${restaurant.r_id}" class="carousel" style="flex: 0 0 120px; margin-right: 10px;">
                                            <img src="${restaurant.r_photo_env1}" alt="環境照片1" style="width: 120px; height: 150px; border-radius: 8px; object-fit: cover;">
                                            <img src="${restaurant.r_photo_env2}" alt="環境照片2" style="width: 120px; height: 150px; border-radius: 8px; display: none; object-fit: cover;">
                                            <img src="${restaurant.r_photo_env3}" alt="環境照片3" style="width: 120px; height: 150px; border-radius: 8px; display: none; object-fit: cover;">
                                        </div>
                                        <div style="flex: 1;">
                                            <b style="font-size: 16px;">${restaurant.r_name}</b><br>
                                            <b style="font-size: 14px; color: #FFD700;">評分: ${restaurant.r_rating} 星</b><br>
                                            <div style="margin-top: 5px; word-wrap: break-word;">
                                                ${restaurant.r_food_dishes.split('、').slice(0, 3).map(dish => `
                                                    <span style="background-color: #FFD700; padding: 2px 5px; margin-right: 3px; margin-bottom: 3px; font-size: 12px; border-radius: 5px; display: inline-block;">
                                                        ${dish}
                                                    </span>
                                                `).join('')}
                                            </div>
                                            <div id="bar-chart-${restaurant.r_id}" style="margin-top: 10px; width: 100%;"></div>
                                        </div>
                                    </div>
                                </div>
                            `);

                            // 初始化评分图表和轮播图
                            marker.on('popupopen', function () {
                                const carousel = document.querySelector(`#carousel-${restaurant.r_id}`);
                                const items = carousel.querySelectorAll('img');  // 确保你选择的是所有的img元素
                                items.forEach((item, index) => {
                                    item.classList.add('carousel-item');
                                    item.style.display = index === 0 ? 'block' : 'none';
                                });

                                let currentIndex = 0;
                                if (items.length > 1) {
                                    setInterval(() => {
                                        items[currentIndex].style.display = 'none';
                                        currentIndex = (currentIndex + 1) % items.length;
                                        items[currentIndex].style.display = 'block';
                                    }, 2000);
                                }

                                const ratingHTML = `
                                    <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                                        <span style="width: 40px;">服務:</span>
                                        <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                            <div style="background-color: gold; width: ${restaurant.r_rate_service * 20}px; height: 8px;"></div>
                                        </div>
                                        <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_service} 星</span>
                                    </div>
                                    <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                                        <span style="width: 40px;">食物:</span>
                                        <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                            <div style="background-color: gold; width: ${restaurant.r_rating_food * 20}px; height: 8px;"></div>
                                        </div>
                                        <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rating_food} 星</span>
                                    </div>
                                    <div style="display: flex; align-items: center; margin-bottom: 0px; font-size: 12px; max-width: 200px;">
                                        <span style="width: 40px;">環境:</span>
                                        <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                            <div style="background-color: gold; width: ${restaurant.r_rate_atmosphere * 20}px; height: 8px;"></div>
                                        </div>
                                        <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_atmosphere} 星</span>
                                    </div>
                                    <div style="display: flex; align-items: center; font-size: 12px; max-width: 200px;">
                                        <span style="width: 40px;">衛生:</span>
                                        <div style="background-color: #ccc; width: 100px; height: 8px; border-radius: 5px; overflow: hidden; position: relative;">
                                            <div style="background-color: gold; width: ${restaurant.r_rate_clean * 20}px; height: 8px;"></div>
                                        </div>
                                        <span style="margin-left: 5px; white-space: nowrap;">${restaurant.r_rate_clean} 星</span>
                                    </div>
                                `;

                                // 将评分条形图添加到弹出窗口的某个元素中
                                const popupContent = document.querySelector(`#bar-chart-${restaurant.r_id}`);
                                popupContent.innerHTML = ratingHTML;
                            });
                        }
                    });
                })
                .catch(error => console.error('Error loading restaurant data:', error));
        });

        // 创建D3.js图标
        function createD3Icon(restaurant) {
            const svg = d3.create("svg")
                .attr("width", 40)
                .attr("height", 40)
                .attr("viewBox", "0 0 40 40");

            // 定义圆形遮罩
            svg.append("defs").append("clipPath")
                .attr("id", "circle-clip")
                .append("circle")
                .attr("cx", 20)
                .attr("cy", 20)
                .attr("r", 20);

            // 定义图片，使用clipPath裁剪
            svg.append("image")
                .attr("xlink:href", restaurant.r_photo_env1)
                .attr("width", 60)
                .attr("height", 60)
                .attr("x", -10)
                .attr("y", -10)
                .attr("clip-path", "url(#circle-clip)")
                .attr("preserveAspectRatio", "xMidYMid slice");

            // 添加圆形边框
            svg.append("circle")
                .attr("cx", 20)
                .attr("cy", 20)
                .attr("r", 20)
                .attr("stroke", "black")
                .attr("stroke-width", 2)
                .attr("fill", "none");

            // 添加星星图标
            const smallCircleRadius = 5;
            const iconOffset = 12;

            svg.append('circle')
                .attr('cx', 20 - iconOffset)
                .attr('cy', 20 + iconOffset)
                .attr('r', smallCircleRadius)
                .attr('fill', 'white')
                .attr('stroke', 'black')
                .attr('stroke-width', '1px');

            svg.append("text")
                .attr("x", 20 - iconOffset)
                .attr("y", 20 + iconOffset)
                .attr("text-anchor", "middle")
                .attr("alignment-baseline", "middle")
                .attr("font-size", "10px")
                .attr("class", "fas fa-star")
                .attr("fill", "#FFD400")
                .text('\uf005'); // Font Awesome - star (Unicode)

            svg.append("text")
                .attr("x", 20 - iconOffset)
                .attr("y", 20 + iconOffset)
                .attr("text-anchor", "middle")
                .attr("alignment-baseline", "middle")
                .attr("font-size", "8px")
                .attr("font-weight", "bold")
                .text(restaurant.r_rating !== undefined ? restaurant.r_rating : 'N/A');

            // 添加停车图标
            svg.append('circle')
                .attr('cx', 20 + iconOffset)
                .attr('cy', 20 + iconOffset)
                .attr('r', smallCircleRadius)
                .attr('fill', 'white')
                .attr('stroke', 'black')
                .attr('stroke-width', '1px');

            svg.append("text")
                .attr("x", 20 + iconOffset)
                .attr("y", 20 + iconOffset)
                .attr("text-anchor", "middle")
                .attr("alignment-baseline", "middle")
                .attr("font-size", "10px")
                .attr("class", "fas fa-parking")
                .attr('fill', restaurant.r_has_parking == 1 ? 'blue' : 'lightgrey')
                .text('\uf540'); // Font Awesome - parking (Unicode)

            return svg.node().outerHTML;
        }

        // 添加捷运和轻轨线路和标记
        fetch('../connect_sql/get_data_map_json.php')
            .then(response => response.json())
            .then(data => {
                var LRT_points = [];
                var MRT_O_points = [];
                var MRT_R_points = [];
                data.forEach(function (transportation) {
                    var circle = null;
                    if (transportation.id.includes('C')) {
                        LRT_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(LRT_points, {
                            color: 'green',
                            weight: 2
                        }).addTo(map);
                        circle = L.circleMarker([transportation.latitude, transportation.longitude], {
                            color: 'green',
                            fillColor: 'green',
                            fillOpacity: 0.9,
                            radius: 8  // 使用circleMarker，确保大小不受缩放影响
                        }).addTo(map);
                    } else if (transportation.id.includes('O')) {
                        MRT_O_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(MRT_O_points, {
                            color: 'orange',
                            weight: 2
                        }).addTo(map);
                        circle = L.circleMarker([transportation.latitude, transportation.longitude], {
                            color: 'orange',
                            fillColor: 'orange',
                            fillOpacity: 0.9,
                            radius: 8
                        }).addTo(map);
                    } else {
                        MRT_R_points.push([transportation.latitude, transportation.longitude]);
                        L.polyline(MRT_R_points, {
                            color: 'red',
                            weight: 2
                        }).addTo(map);
                        circle = L.circleMarker([transportation.latitude, transportation.longitude], {
                            color: 'red',
                            fillColor: '#f03',
                            fillOpacity: 0.9,
                            radius: 8
                        }).addTo(map);
                    }
                    circle.bindPopup("name: " + transportation.name).openPopup();
                });
            })
            .catch(error => console.error('Error loading MRT/LRT data:', error));

        // 添加 EdgeMarker 箭头
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

                            if (restaurant.r_MRT) {
                                const mrtStationName = restaurant.r_MRT.replace('站', '');
                                const mrtStation = stationMap[mrtStationName];
                                if (mrtStation) {
                                    const mrtDistance = parseFloat(restaurant.r_MRT_dist_km);
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
                                    if (lrtDistance < shortestDistance) {
                                        shortestDistance = lrtDistance;
                                        shortestLine = L.polyline([restaurantLatLng, [lrtStation.lat, lrtStation.lng]], {
                                            color: 'green'
                                        });
                                    }
                                }
                            }

                            if (shortestLine) {
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
