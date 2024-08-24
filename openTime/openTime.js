const parseTime = d3.timeParse("%H%M");
const formatTime = d3.timeFormat("%H:%M");
const days = ["一", "二", "三", "四", "五", "六", "日"];

const colors = ["#d62828", "#00a896", "#5fa8d3"]; // 为三家餐厅指定红色、绿色和蓝色

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
      .attr("y", yScale(parseTime("2400")))
      .attr("width", xScale.bandwidth())
      .attr("height", 0)
      .attr("fill", colors[index])
      .attr("opacity", 0.7)
      .transition()
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
}

function resetHighlight() {
  d3.selectAll(".open-bar").classed("dim", false).classed("highlight", false);
}

function addRestaurantButtons(restaurantIds, restaurant_time) {
  const buttonContainer = d3.select(".button-container");
  
  restaurantIds.forEach((restaurantId, index) => {
    const restaurantName = restaurant_time[restaurantId].r_name;
    const button = buttonContainer
      .append("button")
      .attr("id", `button-${index}`)
      .attr("class", "restaurant-button")
      .text(restaurantName)
      .on("mouseover", () => highlightRestaurant(index))
      .on("mouseout", () => resetHighlight());
  });
}

document.addEventListener("DOMContentLoaded", () => {
  updateChart(Object.values(restaurant_time));
  addRestaurantButtons(Object.keys(restaurant_time), restaurant_time);
});

const buttonColors = ["#FF70AE", "#85B4FF", "#FFCE47"];
const margin = 10;
const svg_openTime = d3
  .select("svg.openTime")
  .attr("width", 300)
  .attr("height", 250);

function addButtons(restaurantNames) {
  const buttonGroup = svg_openTime
    .append("g")
    .attr("transform", `translate(${margin},${margin})`);

  let xPosition = 10;
  let yPosition = 10;
  const buttonWidth = 35;
  const buttonHeight = 23;
  const buttonPositions = [];

  restaurantNames.forEach((name, i) => {
    let punctuation = name.length;
    if (name.includes("-")) punctuation -= 2;
    else if (name.includes("(")) punctuation -= 1.5;
    const expandedWidth = punctuation * 14.5 + 15;

    buttonPositions.push(xPosition);

    const buttonContainer = buttonGroup
      .append("g")
      .attr("class", `button-container-${i + 1}`)
      .attr("transform", `translate(${xPosition},${yPosition})`)
      .style("cursor", "default")
      .on("mouseover", function () {
        if (!isLocked) {
          d3.select(this)
            .select("rect")
            .transition()
            .duration(100)
            .style("fill-opacity", 1)
            .attr("width", expandedWidth);

          for (let j = i + 1; j < restaurantNames.length; j++) {
            d3.select(`.button-container-${j + 1}`)
              .transition()
              .duration(100)
              .attr(
                "transform",
                `translate(${buttonPositions[j] + (expandedWidth - buttonWidth)},${yPosition})`
              );
          }

          d3.select(this)
            .select("text")
            .transition()
            .duration(100)
            .style("visibility", "visible");

          d3.select(`.levels_rate.restaurant-${i + 1}`).raise();
          d3.select(`.levels_rate.restaurant-${i + 1} path`)
            .transition()
            .duration(100)
            .style("fill-opacity", 0.5);

          d3.selectAll(`.levels_rate:not(.restaurant-${i + 1}) path`).style(
            "fill-opacity",
            0.1
          );

          d3.selectAll(`.levels_rate.restaurant-${i + 1} circle`)
            .transition()
            .duration(100)
            .style("fill-opacity", 1);

          d3.selectAll(`.levels_rate:not(.restaurant-${i + 1}) circle`)
            .transition()
            .duration(100)
            .style("fill-opacity", 0.2);

          d3.select(".axis-ticks").raise();
        }
      })
      .on("mouseleave", function () {
        if (!isLocked) {
          d3.select(this)
            .select("rect")
            .style("fill-opacity", 0.5)
            .attr("width", buttonWidth);

          for (let j = i + 1; j < restaurantNames.length; j++) {
            d3.select(`.button-container-${j + 1}`)
              .transition()
              .duration(100)
              .attr(
                "transform",
                `translate(${buttonPositions[j]},${yPosition})`
              );
          }

          d3.select(this).select("text").style("visibility", "hidden");

          d3.selectAll(`.levels_rate path`).style("fill-opacity", 0);
          d3.selectAll(`.levels_rate circle`)
            .transition()
            .duration(100)
            .style("fill-opacity", 1);
        }
      })
      .on("click", function () {
        if (!isLocked) {
          d3.select(this)
            .select("rect")
            .transition()
            .duration(100)
            .style("fill-opacity", 1)
            .attr("width", expandedWidth);

          for (let j = i + 1; j < restaurantNames.length; j++) {
            d3.select(`.button-container-${j + 1}`)
              .transition()
              .duration(100)
              .attr(
                "transform",
                `translate(${buttonPositions[j] + (expandedWidth - buttonWidth)},${yPosition})`
              );
          }

          d3.select(this)
            .select("text")
            .transition()
            .duration(100)
            .style("visibility", "visible");

          d3.select(`.levels_rate.restaurant-${i + 1}`).raise();
          d3.select(`.levels_rate.restaurant-${i + 1} path`)
            .transition()
            .duration(100)
            .style("fill-opacity", 0.5);

          d3.selectAll(`.levels_rate:not(.restaurant-${i + 1}) path`).style(
            "fill-opacity",
            0.1
          );

          d3.selectAll(`.levels_rate.restaurant-${i + 1} circle`)
            .transition()
            .duration(100)
            .style("fill-opacity", 1);

          d3.selectAll(`.levels_rate:not(.restaurant-${i + 1}) circle`)
            .transition()
            .duration(100)
            .style("fill-opacity", 0.2);

          d3.select(".axis-ticks").raise();
          isLocked = true;
        } else {
          d3.select(this)
            .select("rect")
            .style("fill-opacity", 0.5)
            .attr("width", buttonWidth);

          for (let j = i + 1; j < restaurantNames.length; j++) {
            d3.select(`.button-container-${j + 1}`)
              .transition()
              .duration(100)
              .attr(
                "transform",
                `translate(${buttonPositions[j]},${yPosition})`
              );
          }

          d3.select(this).select("text").style("visibility", "hidden");

          d3.selectAll(`.levels_rate path`).style("fill-opacity", 0);

          d3.selectAll(`.levels_rate circle`)
            .transition()
            .duration(100)
            .style("fill-opacity", 1);
          isLocked = false;
        }
      });

    buttonContainer
      .append("rect")
      .attr("width", buttonWidth)
      .attr("height", buttonHeight)
      .attr("fill", buttonColors[i])
      .style("fill-opacity", 0.5)
      .attr("rx", 5)
      .attr("ry", 5)
      .style("cursor", "pointer");

    buttonContainer
      .append("text")
      .attr("x", 10)
      .attr("y", buttonHeight / 1.3)
      .attr("class", `button-text-${i + 1}`)
      .style("visibility", "hidden")
      .attr("font-size", 14)
      .style("cursor", "pointer")
      .text(name);

    xPosition += buttonWidth + 10;
  });
}
