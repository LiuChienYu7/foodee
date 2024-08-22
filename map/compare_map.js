console.log('detail', restaurant_data_detail);

// 使用第一家餐廳的經緯度設置地圖的中心點
const firstRestaurant = restaurant_data_detail[0];
const r_lat = firstRestaurant.r_latitude;
const r_long = firstRestaurant.r_longitude;

// 設置地圖的初始視圖，以第一家餐廳為中心
const map = L.map("map").setView([r_lat, r_long], 13);
// const map = L.map("map").setView(id1Coordinates, 13);

const tiles = L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  maxZoom: 19,
  attribution:
    '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
}).addTo(map);

// 從JSON文件加載數據並顯示在地圖上
// fetch("../connect_sql/get_data_json.php")
//   .then((response) => response.json())
//   .then((data) => {
//     const filteredRestaurants = data.filter((restaurant) =>
//       restaurantIds.includes(restaurant.r_id)
//     );

//     filteredRestaurants.forEach(function (restaurant) {
//       var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
//         isRestaurant: true, // 設置 isRestaurant 屬性 讓edgeMarker辨別是餐廳
//       }).addTo(map);

//       // 顯示餐廳名稱
//       marker.bindPopup("name: " + restaurant.r_name).openPopup();
//     });
//   })
//   .catch((error) => console.error("Error loading restaurant data:", error));

// 只顯示頁面上的三家餐廳資訊
restaurant_data_detail.forEach(function (restaurant) {
  var marker = L.marker([restaurant.r_latitude, restaurant.r_longitude], {
      isRestaurant: true,  // 設置 isRestaurant 屬性 讓edgeMarker辨別是餐廳
  }).addTo(map);

  // 顯示餐廳名稱
  marker.bindPopup("name: " + restaurant.r_name).openPopup();
});

//加上捷運輕軌
fetch("../connect_sql/get_data_map_json.php")
  .then((response) => response.json())
  .then((data) => {
    var LRT_points = [];
    var MRT_O_points = [];
    var MRT_R_points = [];
    var MRT = L.icon({
      iconUrl: "mrt.png",
      iconSize: [25, 25], // size of the icon
      // iconAnchor:   [22, 94], // point of the icon which will correspond to marker's location
      // shadowAnchor: [4, 62],  // the same for the shadow
      // popupAnchor:  [-3, -76] // point from which the popup should open relative to the iconAnchor
    });
    // console.log(data);
    data.forEach(function (transportation) {
      if (transportation.id.includes("C")) {
        LRT_points.push([transportation.latitude, transportation.longitude]);
        L.polyline(LRT_points, {
          color: "green",
          weight: 2,
        }).addTo(map);
        var circle = L.circle(
          [transportation.latitude, transportation.longitude],
          {
            color: "green",
            fillColor: "green",
            fillOpacity: 0.9,
            radius: 35,
          }
        ).addTo(map);
      } else if (transportation.id.includes("O")) {
        MRT_O_points.push([transportation.latitude, transportation.longitude]);
        L.polyline(MRT_O_points, {
          color: "orange",
          weight: 2,
        }).addTo(map);
        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map);
        var circle = L.circle(
          [transportation.latitude, transportation.longitude],
          {
            color: "orange",
            fillColor: "orange",
            fillOpacity: 0.9,
            radius: 35,
          }
        ).addTo(map);
      } else {
        MRT_R_points.push([transportation.latitude, transportation.longitude]);
        L.polyline(MRT_R_points, {
          color: "red",
          weight: 2,
        }).addTo(map);

        // L.marker([transportation.latitude, transportation.longitude], {icon: MRT}).addTo(map)
        var circle = L.circle(
          [transportation.latitude, transportation.longitude],
          {
            color: "red",
            fillColor: "#f03",
            fillOpacity: 0.9,
            radius: 35,
          }
        ).addTo(map);
      }

      circle.bindPopup("name: " + transportation.name).openPopup();
    });
  })
  .catch((error) => console.error("Error loading restaurant data:", error));

// add the EdgeMarker to the map. 箭頭
var edgeMarkerLayer = L.edgeMarker({
  icon: L.icon({
    // style markers
    iconUrl: "../map/edge_arrow_marker.png",
    clickable: true,
    iconSize: [25, 25],
    iconAnchor: [10, 10],
  }),
  rotateIcons: true, // rotate EdgeMarkers depending on their relative position
  layerGroup: null, // you can specify a certain L.layerGroup to create the edge markers from.
}).addTo(map);

// if you want to remove the edge markers
// edgeMarkerLayer.destroy();

// 餐廳和捷運輕軌連線
fetch("../connect_sql/get_data_json.php")
  .then((response) => response.json())
  .then((restaurants) => {
    fetch("../connect_sql/get_data_map_json.php")
      .then((response) => response.json())
      .then((transportations) => {
        // 建立捷運輕軌站點名稱與位置的對應字典
        const stationMap = {};
        transportations.forEach((station) => {
          // 保留輕軌站名稱中的"站"
          stationMap[station.name] = {
            lat: station.latitude,
            lng: station.longitude,
          };
        });

        // 用於保存所有線條的圖層群組
        const lineGroup = L.layerGroup().addTo(map);

        // 繪製餐廳到捷運輕軌站的最短連線
        restaurants.forEach((restaurant) => {
          // 餐廳位置
          const restaurantLatLng = [
            restaurant.r_latitude,
            restaurant.r_longitude,
          ];

          let shortestDistance = Infinity;
          let shortestLine = null;

          // console.log(`Processing restaurant: ${restaurant.r_name}`);

          // 處理捷運站
          if (restaurant.r_MRT) {
            const mrtStationName = restaurant.r_MRT.replace("站", "");
            const mrtStation = stationMap[mrtStationName];
            if (mrtStation) {
              const mrtDistance = parseFloat(restaurant.r_MRT_dist_km);
              // console.log(`MRT Station: ${mrtStationName}, Distance: ${mrtDistance} km`);
              if (mrtDistance < shortestDistance) {
                shortestDistance = mrtDistance;
                shortestLine = L.polyline(
                  [restaurantLatLng, [mrtStation.lat, mrtStation.lng]],
                  {
                    color: "blue",
                  }
                );
              }
            }
          }

          // 處理輕軌站
          if (restaurant.r_LRT) {
            const lrtStationName = restaurant.r_LRT;
            const lrtStation = stationMap[lrtStationName];
            if (lrtStation) {
              const lrtDistance = parseFloat(restaurant.r_LRT_dist_km);
              // console.log(`LRT Station: ${lrtStationName}, Distance: ${lrtDistance} km`);
              if (lrtDistance < shortestDistance) {
                shortestDistance = lrtDistance;
                shortestLine = L.polyline(
                  [restaurantLatLng, [lrtStation.lat, lrtStation.lng]],
                  {
                    color: "green",
                  }
                );
              }
            }
          }

          // 將最短的線添加到圖層群組中
          if (shortestLine) {
            // console.log(`Shortest Line for ${restaurant.r_name}: ${shortestDistance} km`);
            lineGroup.addLayer(shortestLine);
          }
        });

        // 設置當前的放大級別閾值
        const zoomThreshold = 15;

        // 當地圖縮放級別改變時觸發事件
        map.on("zoomend", () => {
          if (map.getZoom() >= zoomThreshold) {
            // 顯示線條
            map.addLayer(lineGroup);
          } else {
            // 隱藏線條
            map.removeLayer(lineGroup);
          }
        });

        // 初始化檢查縮放級別並設置初始線條顯示狀態
        if (map.getZoom() >= zoomThreshold) {
          map.addLayer(lineGroup);
        } else {
          map.removeLayer(lineGroup);
        }
      })
      .catch((error) => console.error("Error loading MRT/LRT data:", error));
  })
  .catch((error) => console.error("Error loading restaurant data:", error));
