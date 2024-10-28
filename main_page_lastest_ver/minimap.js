function drawMinimap(intervalPositions, totalHeight, intervalWidths, intervals, sortedTimes, data, currentIntervals) {
    const minimapWidth = 200;
    const minimapHeight = 85;
    const maxCircles = 20; // Maximum number of circles allowed per grid
    const minHeight = 10;  // Minimum height for each time interval
    const verticalOffset = 5; // Offset to move circles down slightly
    const circleRadius = 1.8; // Smaller circle radius
    const circlePadding = 3; // Padding between circles in each grid

    const colors = d3.scaleOrdinal()
        .domain(sortedTimes)
        .range(['#d6af99', '#a2cdab', '#7ea1dd', '#fdc85e', '#e67575', '#b295ab']);

    // Calculate circle counts and total circles for each time interval
    const intervalData = intervals.map(interval => {
        const timeData = {};
        let totalCircles = 0;
        sortedTimes.forEach(time => {
            const count = data.filter(d => d.r_time_low === time && categorizePrice(d.r_price_low) === interval).length;
            timeData[time] = count;
            totalCircles += count;
        });
        return {
            interval: interval,
            totalCircles: totalCircles,
            ...timeData
        };
    });

    // Total circle counts for the entire minimap (needed for Y-axis proportional heights)
    const totalCirclesAcrossTimes = d3.sum(intervalData, d => d.totalCircles);

    // Calculate the height for each time interval based on its proportion of the total circles
    const scaleYHeight = sortedTimes.map(time => {
        const totalCirclesForTime = d3.sum(intervalData, d => d[time]);
        const height = (totalCirclesForTime / totalCirclesAcrossTimes) * minimapHeight;
        return Math.max(height, minHeight);  // Ensure the height is at least minHeight
    });

    // Clear the old minimap and draw the new background
    d3.select("#minimap").selectAll("*").remove();

    const svg = d3.select("#minimap")
        .attr("width", minimapWidth)
        .attr("height", minimapHeight);

    svg.append("rect")
        .attr("width", minimapWidth)
        .attr("height", minimapHeight)
        .attr("fill", "#ddd");

    let accumulatedHeight = 0;

    // Create X scale for price intervals
    const scaleX = d3.scaleBand()
        .domain(intervals)
        .range([0, minimapWidth])
        .padding(0.1);

    // Calculate the positions of the red box
    const currentStart = currentIntervals[0];
    const currentEnd = currentIntervals[currentIntervals.length - 1];
    const xPositionStart = scaleX(currentStart);
    const xPositionEnd = scaleX(currentEnd) + scaleX.bandwidth();

    // Draw background rects for each dining time based on its calculated height
    sortedTimes.forEach((time, index) => {
        const height = scaleYHeight[index];

        intervals.forEach((interval, i) => {
            const isInRedBox = scaleX(interval) >= xPositionStart && scaleX(interval) <= xPositionEnd;

            // Draw the background rects with opacity applied to those outside the red box
            svg.append("rect")
                .attr("x", scaleX(interval))
                .attr("y", accumulatedHeight)
                .attr("width", scaleX.bandwidth())
                .attr("height", height)
                .attr("fill", colors(time))
                .attr("opacity", isInRedBox ? 1 : 0.5)  // Apply opacity based on whether it is inside the red box
                .attr("stroke", "none");
        });

        accumulatedHeight += height;
    });

    // Draw white vertical lines to separate price intervals
    intervals.forEach((interval, i) => {
        svg.append("line")
            .attr("x1", scaleX(interval))
            .attr("x2", scaleX(interval))
            .attr("y1", 0)
            .attr("y2", minimapHeight)
            .attr("stroke", "white")
            .attr("stroke-width", 1);
    });

    // Draw circles for each price interval and dining time in pairs (2 per row)
    intervals.forEach((interval, i) => {
        let yOffset = 0;
        const isInRedBox = scaleX(interval) >= xPositionStart && scaleX(interval) <= xPositionEnd;

        sortedTimes.forEach((time, j) => {
            const circleCount = Math.min(intervalData[i][time], maxCircles);  // Limit circle count to maxCircles
            const yHeight = scaleYHeight[j];

            for (let k = 0; k < circleCount; k++) {
                const row = Math.floor(k / 3);  // Determine the row (three circles per row)
                const col = k % 3;  // Determine the column (0, 1, or 2, for three per row)

                const cx = scaleX(interval) + scaleX.bandwidth() / 4 + col * (2 * circleRadius + circlePadding);  // X-position for each circle in a row
                const cy = yOffset + row * (2 * circleRadius + circlePadding) + verticalOffset;  // Y-position for each row

                svg.append("circle")
                    .attr("cx", cx)
                    .attr("cy", cy)
                    .attr("r", circleRadius)  // Use smaller circle radius
                    .attr("fill", "white")
                    .attr("stroke", "black")
                    .attr("stroke-width", 0.5)
                    .attr("opacity", isInRedBox ? 1 : 0.5);  // Apply opacity based on whether it is inside the red box
            }
            yOffset += yHeight; // Increment Y offset for the next dining time
        });
    });

    // Draw the red box
    const redBox = svg.append("rect")
        .attr("x", xPositionStart)
        .attr("y", 0)
        .attr("width", xPositionEnd - xPositionStart)
        .attr("height", minimapHeight)
        .attr("stroke", "red")
        .attr("stroke-width", 2)
        .attr("fill", "none");

    return redBox;  // Return the red box object
}
