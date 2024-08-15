<?php
ob_start(); // 開啟緩衝區
header('Content-Type: text/html; charset=UTF-8');

// 資料庫連線設置
$host = 'localhost';
$dbuser = 'root';
$dbpassword = '';
$dbname = 'foodee';

// 建立資料庫連線
$link = mysqli_connect($host, $dbuser, $dbpassword, $dbname);

// 初始化變數
$all_restaurant_data = [];
$restaurant_ids = [];
$restaurant_names = [];

if ($link) {
    mysqli_query($link, 'SET NAMES utf8');

    // 從 URL 查詢參數獲取餐廳 ID
    for ($i = 1; $i <= 3; $i++) {
        if (isset($_GET["r_id$i"])) {
            $r_id = intval($_GET["r_id$i"]);
            $restaurant_ids[] = $r_id;
            
            // 查詢每個餐廳的名稱
            $query_name = "SELECT r_name FROM detail WHERE r_id = $r_id";
            $result_name = mysqli_query($link, $query_name);

            if ($result_name) {
                $row_name = mysqli_fetch_assoc($result_name);
                $restaurant_names[$r_id] = $row_name['r_name'];
            } else {
                echo "Error in query: " . mysqli_error($link);
                $restaurant_names[$r_id] = 'Unknown';
            }

            // 查詢每個餐廳的營業時間
            $query_hours = "SELECT r_hours_periods FROM detail WHERE r_id = $r_id";
            $result_hours = mysqli_query($link, $query_hours);

            if ($result_hours) {
                $row_hours = mysqli_fetch_assoc($result_hours);
                $all_restaurant_data[$r_id] = $row_hours['r_hours_periods'];
            } else {
                echo "Error in query: " . mysqli_error($link);
                $all_restaurant_data[$r_id] = null;
            }
        }
    }
} else {
    echo "Failed to connect to the database: " . mysqli_connect_error();
}
mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Info</title>
    <link rel="stylesheet" href="./openTime.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
    <style>
        .button-container {
            display: flex;
            flex-wrap: wrap;
        }
        .button-container button {
            margin: 5px;
            padding: 10px;
            position: relative;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            max-width: 150px;
            cursor: pointer;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .button-container button:hover {
            color: #fff;
        }
        .chart-container {
            width: auto;
        }
        .highlight {
            opacity: 1 !important;
        }
        .dim {
            opacity: 0.2;
        }
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
            top: 50%;
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
    </style>
</head>
<body>
    <div class="button-container">
        <?php if (!empty($restaurant_ids)): ?>
            <?php foreach ($restaurant_ids as $index => $r_id): ?>
                <button id="button-<?php echo $r_id; ?>" 
                        onmouseover="highlightRestaurant(<?php echo $index; ?>)" 
                        onmouseout="resetHighlight()"
                        onclick="showRestaurantData(<?php echo $r_id; ?>)">
                    <?php echo htmlspecialchars($restaurant_names[$r_id]); ?>
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No restaurants found.</p>
        <?php endif; ?>
    </div>

    <div id="chart" class="chart-container"></div>

    <div class="slideshow-container">
        <div class="mySlides">
            <img src="image1.jpg" style="width:100%">
        </div>

        <div class="mySlides">
            <img src="image2.jpg" style="width:100%">
        </div>

        <div class="mySlides">
            <img src="image3.jpg" style="width:100%">
        </div>

        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <?php include '../openTime/openTime.php'; ?>

    <script>
        const parseTime = d3.timeParse("%H%M");
        const formatTime = d3.timeFormat("%H:%M");
        const days = ['1', '2', '3', '4', '5', '6', '7'];

        const data = <?php echo json_encode($all_restaurant_data); ?>;
        const restaurantNames = <?php echo json_encode($restaurant_names); ?>;
        const colors = ['#d62828', '#00a896', '#5fa8d3'];

        function updateChart(restaurantsData) {
            const svgContainer = d3.select("#chart");
            svgContainer.selectAll("*").remove();

            const svg = svgContainer.append("svg")
                .attr("width", 400)
                .attr("height", 400)
                .append("g")
                .attr("transform", "translate(50,50)");

            const xScale = d3.scaleBand().domain(days).range([0, 300]).padding(0.1);
            const yScale = d3.scaleTime()
                .domain([parseTime("0000"), parseTime("2400")])
                .range([0, 300]);

            restaurantsData.forEach((hoursPeriods, index) => {
                const extendedData = [];
                const allHoursPeriods = JSON.parse(hoursPeriods.replace(/'/g, '"'));

                allHoursPeriods.forEach(period => {
                    let start = parseTime(period.startTime);
                    let end = parseTime(period.endTime);
                    if (end < start) {
                        extendedData.push({
                            status: period.status,
                            day: period.day,
                            start: start,
                            end: parseTime("2400"),
                            nextDay: true
                        });
                        extendedData.push({
                            status: period.status,
                            day: (period.day % 7) + 1,
                            start: parseTime("0000"),
                            end: end,
                            nextDay: true
                        });
                    } else {
                        extendedData.push({
                            status: period.status,
                            day: period.day,
                            start: start,
                            end: end,
                            nextDay: false
                        });
                    }
                });

                svg.selectAll(".open-bar" + index)
                    .data(extendedData)
                    .enter().append("rect")
                    .attr("class", "open-bar open-bar-" + index)
                    .attr("x", d => xScale(days[d.day - 1]))
                    .attr("y", yScale(parseTime("2400")))
                    .attr("width", xScale.bandwidth())
                    .attr("height", 0)
                    .attr("fill", colors[index])
                    .attr("opacity", 0.7)
                    .transition()
                    .duration(750)
                    .attr("y", d => yScale(d.start))
                    .attr("height", d => yScale(d.end) - yScale(d.start));
            });

            const yAxis = d3.axisLeft(yScale).tickFormat(formatTime);
            svg.append("g")
                .attr("class", "axis")
                .call(yAxis);

            const xAxis = d3.axisTop(xScale);
            svg.append("g")
                .attr("class", "axis")
                .attr("transform", "translate(0,0)")
                .call(xAxis);
        }

        function highlightRestaurant(index) {
            d3.selectAll('.open-bar').classed('dim', true);
            d3.selectAll('.open-bar-' + index).classed('highlight', true).classed('dim', false);
            
            d3.select(`#button-${restaurantIds[index]}`)
                .style("background-color", colors[index])
                .style("color", "#fff");
        }

        function resetHighlight() {
            d3.selectAll('.open-bar').classed('dim', false).classed('highlight', false);

            d3.selectAll('.button-container button')
                .style("background-color", "#f0f0f0")
                .style("color", "#000");
        }

        function showRestaurantData(restaurantId) {
            const hoursPeriods = data[restaurantId];
            updateChart([hoursPeriods]);

            document.querySelectorAll('.button-container button').forEach(button => {
                button.classList.remove('selected');
            });
            document.getElementById(`button-${restaurantId}`).classList.add('selected');
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateChart(Object.values(data));

            // 初始化圖片切換功能
            let slideIndex = 1;
            showSlides(slideIndex);

            document.querySelectorAll('.prev').forEach(element => {
                element.addEventListener('click', () => plusSlides(-1));
            });

            document.querySelectorAll('.next').forEach(element => {
                element.addEventListener('click', () => plusSlides(1));
            });
        });

        function plusSlides(n) {
            showSlides(slideIndex += n);
        }

        function showSlides(n) {
            let i;
            let slides = document.getElementsByClassName("mySlides");
            if (n > slides.length) { slideIndex = 1; }
            if (n < 1) { slideIndex = slides.length; }
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            slides[slideIndex-1].style.display = "block";  
        }

        const restaurantIds = <?php echo json_encode($restaurant_ids); ?>;
    </script>
</body>
</html>
<?php
ob_end_flush();
?>
