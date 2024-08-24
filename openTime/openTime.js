const parseTime = d3.timeParse("%H%M");
const formatTime = d3.timeFormat("%H:%M");
const days = ["一", "二", "三", "四", "五", "六", "日"];

const colors = ["#d62828", "#00a896", "#5fa8d3"]; // 為三家餐廳指定紅色、綠色和藍色

function updateChart(restaurant_time) {
  const svgContainer = d3.select(".openTime");
  svgContainer.selectAll("*").remove();

  const svg = svgContainer
    .append("svg")
    .attr("width", 300)
    .attr("height", 400)
    .append("g")
    .attr("class", "openTime")
    .attr("transform", "translate(40,30)");

  const xScale = d3.scaleBand().domain(days).range([0, 200]).padding(0.1);
  const yScale = d3
    .scaleTime()
    .domain([parseTime("0000"), parseTime("2400")])
    .range([0, 180]);

    restaurant_time.forEach((restaurant, index) => {
    const extendedData = [];

    // 確保 r_hours_periods 是有效的 JSON 字符串
    const allHoursPeriods = JSON.parse(restaurant.r_hours_periods);

    allHoursPeriods.forEach((period) => {
      let start = parseTime(period.startTime);
      let end = parseTime(period.endTime);
      if (end < start) {
        extendedData.push({
          status: period.status,
          day: period.day,
          start: start,
          end: parseTime("2400"),
          nextDay: true,
        });
        extendedData.push({
          status: period.status,
          day: (period.day % 7) + 1,
          start: parseTime("0000"),
          end: end,
          nextDay: true,
        });
      } else {
        extendedData.push({
          status: period.status,
          day: period.day,
          start: start,
          end: end,
          nextDay: false,
        });
      }
    });

    svg
      .selectAll(".open-bar" + index)
      .data(extendedData)
      .enter()
      .append("rect")
      .attr("class", "open-bar open-bar-" + index)
      .attr("x", (d) => xScale(days[d.day - 1]))
      .attr("y", yScale(parseTime("2400"))) // 初始設置於底部
      .attr("width", xScale.bandwidth())
      .attr("height", 0) // 初始高度為0
      .attr("fill", colors[index]) // 使用指定的顏色
      .attr("opacity", 0.7)
      .transition() // 加入動畫效果
      .duration(750)
      .attr("y", (d) => yScale(d.start))
      .attr("height", (d) => yScale(d.end) - yScale(d.start));
  });

  const yAxis = d3.axisLeft(yScale).tickFormat(formatTime);
  svg.append("g").attr("class", "axis").call(yAxis);

  const xAxis = d3.axisTop(xScale);
  svg
    .append("g")
    .attr("class", "axis")
    .attr("transform", "translate(0,0)")
    .call(xAxis);
}

function highlightRestaurant(index) {
  d3.selectAll(".open-bar").classed("dim", true);
  d3.selectAll(".open-bar-" + index)
    .classed("highlight", true)
    .classed("dim", false);

  // 將按鈕顏色與圖表顏色對應
  d3.select(`#button-${restaurantIds[index]}`)
    .style("background-color", colors[index])
    .style("color", "#fff");
}

function resetHighlight() {
  d3.selectAll(".open-bar").classed("dim", false).classed("highlight", false);

  // 恢復按鈕原色
  d3.selectAll(".button-container button")
    .style("background-color", "#f0f0f0")
    .style("color", "#000");
}

function showRestaurantData(restaurantId) {
  const hoursPeriods = data[restaurantId];
  updateChart([hoursPeriods]);

  // Highlight the selected button
  document.querySelectorAll(".button-container button").forEach((button) => {
    button.classList.remove("selected");
  });
  document.getElementById(`button-${restaurantId}`).classList.add("selected");
}

document.addEventListener("DOMContentLoaded", () => {
  updateChart(Object.values(restaurant_time)); // 顯示所有餐廳的詳細營業時間
});
