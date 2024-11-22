let isHidden = false;  // 全域變數，初始化為 false

function toggleCircles(innerRadius, circleRadius) {
    const button = document.getElementById("hide");

    // 初始化按鈕和圈圈狀態
    updateCircles(isHidden);
    updateButton(isHidden);

    button.addEventListener("click", function () {
        console.log("Before toggle, isHidden:", isHidden); // 顯示點擊前的狀態

        // 切換狀態
        isHidden = !isHidden;

        console.log("After toggle, isHidden:", isHidden); // 確認狀態是否已切換

        // 更新圈圈與按鈕
        updateCircles(isHidden);
        updateButton(isHidden);
    });

    function updateCircles(isHidden) {
        d3.selectAll(".circle-group").each(function (d, i) {
            const circleGroup = d3.select(this);
            const patternId = `pattern-${d.r_id}`;
            const patternGroup = d3.select(`#${patternId}`);

            if (isHidden) {
                circleGroup.selectAll("path.left, text.days, path.right, circle.small-circle, text.icon-text").style("display", "none");
                circleGroup.select("circle")
                    .transition()
                    .duration(500)
                    .attr("r", circleRadius);
                patternGroup.select("image")
                    .transition()
                    .duration(500)
                    .attr("width", 2 * circleRadius)
                    .attr("height", 2 * circleRadius);
            } else {
                circleGroup.selectAll("path.left, text.days, path.right, circle.small-circle, text.icon-text").style("display", "block");
                circleGroup.select("circle")
                    .transition()
                    .duration(500)
                    .attr("r", innerRadius);
                patternGroup.select("image")
                    .transition()
                    .duration(500)
                    .attr("width", 2 * innerRadius)
                    .attr("height", 2 * innerRadius);
            }
        });
    }

    function updateButton(isHidden) {
        if (isHidden) {
            button.style.backgroundColor = "rgb(255, 240, 179)";
            button.textContent = "顯示外圈";
        } else {
            button.style.backgroundColor = "";
            button.textContent = "隱藏外圈";
        }
    }
}
