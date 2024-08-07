<?php
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
    <title>Restaurant Hours</title>
    <link rel="stylesheet" href="openTime.css">
    <script src="https://d3js.org/d3.v7.min.js"></script>
</head>
<body>
    <h1>Restaurant Operating Hours</h1>
    <div class="button-container">
        <?php if (!empty($restaurant_ids)): ?>
            <?php foreach ($restaurant_ids as $index => $r_id): ?>
                <button id="button-<?php echo $r_id; ?>" onclick="showRestaurantData(<?php echo $r_id; ?>)">
                    <?php echo htmlspecialchars($restaurant_names[$r_id]); ?>
                </button>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No restaurants found.</p>
        <?php endif; ?>
    </div>

    <div class="toggle-buttons">
        <button onclick="showWeekdays()">Show Weekdays</button>
        <button onclick="showDetailedHours()">Show Detailed Hours</button>
    </div>

    <div id="chart" class="chart-container"></div>

    <script>
        const parseTime = d3.timeParse("%H%M");
        const formatTime = d3.timeFormat("%H:%M");
        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        const data = <?php echo json_encode($all_restaurant_data); ?>;
        const restaurantNames = <?php echo json_encode($restaurant_names); ?>;
        let displayMode = 'detailed';

        function updateChart(hoursPeriods) {
            const allHoursPeriods = JSON.parse(hoursPeriods.replace(/'/g, '"'));
            const extendedData = [];

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

            const svgContainer = d3.select("#chart");
            svgContainer.selectAll("*").remove();

            const svg = svgContainer.append("svg")
                .attr("width", displayMode === 'detailed' ? 1200 : 0)
                .attr("height", displayMode === 'detailed' ? 500 : 200)
                .append("g")
                .attr("transform", "translate(50,50)");

            const xScale = d3.scaleBand().domain(days).range([0, displayMode === 'detailed' ? 1100 : 800]).padding(0.1);
            const yScale = d3.scaleTime()
                .domain([parseTime("0000"), parseTime("2400")])
                .range([0, displayMode === 'detailed' ? 400 : 200]);

            if (displayMode === 'detailed') {
                svg.selectAll(".closed-background")
                    .data(days)
                    .enter().append("rect")
                    .attr("class", "closed")
                    .attr("x", d => xScale(d))
                    .attr("y", 0)
                    .attr("width", xScale.bandwidth())
                    .attr("height", 400);

                svg.selectAll(".open-bar")
                    .data(extendedData)
                    .enter().append("rect")
                    .attr("class", "open")
                    .attr("x", d => xScale(days[d.day - 1]))
                    .attr("y", d => yScale(d.start))
                    .attr("width", xScale.bandwidth())
                    .attr("height", d => yScale(d.end) - yScale(d.start))
                    .transition()
                    .duration(500)
                    .attr("y", d => yScale(d.start))
                    .attr("height", d => yScale(d.end) - yScale(d.start));

                svg.selectAll(".label")
                    .data(extendedData)
                    .enter().append("text")
                    .attr("class", "label")
                    .attr("x", d => xScale(days[d.day - 1]) + xScale.bandwidth() / 2)
                    .attr("y", d => (yScale(d.start) + yScale(d.end)) / 2)
                    .text(d => d.status === 'open' ? 'Open' : 'Closed')
                    .attr("text-anchor", "middle")
                    .attr("alignment-baseline", "middle");

                const yAxis = d3.axisLeft(yScale).tickFormat(formatTime);
                svg.append("g")
                    .attr("class", "axis")
                    .call(yAxis);

                const xAxis = d3.axisTop(xScale);
                svg.append("g")
                    .attr("class", "axis")
                    .attr("transform", "translate(0,0)")
                    .call(xAxis);
            } else if (displayMode === 'weekdays') {
                const weekdayData = days.map(day => {
                    const dayData = extendedData.find(d => d.day === days.indexOf(day) + 1);
                    return {
                        day: day,
                        status: dayData ? dayData.status : 'closed'
                    };
                });

                const xScale = d3.scaleBand().domain(days).range([0, 800]).padding(0.1);
                const svg = d3.select("#chart").append("svg")
                    .attr("width", 900)
                    .attr("height", 200)
                    .append("g")
                    .attr("transform", "translate(50,50)");

                svg.selectAll(".status-circle")
                    .data(days)
                    .enter().append("circle")
                    .attr("class", d => {
                        const dayData = weekdayData.find(day => day.day === d);
                        return dayData && dayData.status === 'open' ? 'open' : 'closed';
                    })
                    .attr("cx", d => xScale(d) + xScale.bandwidth() / 2)
                    .attr("cy", 50)
                    .attr("r", 20);

                svg.selectAll(".label")
                    .data(days)
                    .enter().append("text")
                    .attr("class", "label")
                    .attr("x", d => xScale(d) + xScale.bandwidth() / 2)
                    .attr("y", 110)
                    .text(d => d)
                    .attr("text-anchor", "middle");

                const xAxis = d3.axisTop(xScale);
                svg.append("g")
                    .attr("class", "axis")
                    .attr("transform", "translate(0,30)")
                    .call(xAxis);
            }
        }

        function showRestaurantData(restaurantId) {
            const hoursPeriods = data[restaurantId];
            updateChart(hoursPeriods);

            // Highlight the selected button
            document.querySelectorAll('.button-container button').forEach(button => {
                button.classList.remove('selected');
            });
            document.getElementById(`button-${restaurantId}`).classList.add('selected');
        }

        function showWeekdays() {
            displayMode = 'weekdays';
            const restaurantId = <?php echo reset($restaurant_ids); ?>; // 默認顯示第一家餐廳
            showRestaurantData(restaurantId);
        }

        function showDetailedHours() {
            displayMode = 'detailed';
            const restaurantId = <?php echo reset($restaurant_ids); ?>; // 默認顯示第一家餐廳
            showRestaurantData(restaurantId);
        }

        document.addEventListener('DOMContentLoaded', () => {
            showWeekdays();
        });
    </script>
</body>
</html>
