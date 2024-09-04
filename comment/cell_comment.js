// 確保這個變數已經在前面PHP中定義了
if (typeof reviewData !== "undefined") {
} else {
  console.error("reviewData is not defined.");
}

initializeReviews(reviewData);

// 在点击“评论”时再调取这个函数
d3.select(".comment_comment").on("click", function () {
  // 对现有内容执行淡出动画
  d3.selectAll(".upper-section svg")
    .style("opacity", 0) // 设置动画目标样式
    .remove(); // 在动画结束后移除元素

  initializeReviews(reviewData);
});

function initializeReviews(reviewData) {
  const data = [
    {
      id: 1,
      text: "食物總評",
      reviews: reviewData.map((d) => d.food_comment_sum),
      details: reviewData.map((d) => [
        d.food_review1,
        d.food_review2,
        d.food_review3,
      ]),
    },
    {
      id: 2,
      text: "服務總評",
      reviews: reviewData.map((d) => d.service_comment_sum),
      details: reviewData.map((d) => [
        d.service_review1,
        d.service_review2,
        d.service_review3,
      ]),
    },
    {
      id: 3,
      text: "划算總評",
      reviews: reviewData.map((d) => d.value_comment_sum),
      details: reviewData.map((d) => [
        d.value_review1,
        d.value_review2,
        d.value_review3,
      ]),
    },
    {
      id: 4,
      text: "環境總評",
      reviews: reviewData.map((d) => d.atmosphere_comment_sum),
      details: reviewData.map((d) => [
        d.atmosphere_review1,
        d.atmosphere_review2,
        d.atmosphere_review3,
      ]),
    },
  ];

  console.log(data);

  d3.select(".comment_comment")
    .on("mouseover", function () {
      d3.select(this).style("cursor", "pointer").style("color", "#DC8686"); // 使用 style 来设置颜色
    })
    .on("mouseout", function () {
      d3.select(this).style("color", "black"); // 恢复原始颜色，可以设置为空或原来的颜色值
    });

  const svg = d3
    .select(".upper-section")
    .append("svg")
    .attr("width", 600)
    .attr("height", 220);

  let Fixed = false;
  const groups = svg
    .selectAll(".block-group")
    .data(data)
    .enter()
    .append("g")
    .attr("class", "block-group")
    .attr("transform", (d, i) => `translate(0, ${i * 45})`)
    .on("mouseover", function (event, d) {
      if (!Fixed) {
        // 清除之前的線條和區塊
        svg
          .selectAll(".link, .review-block, .detail-link, .detail-block")
          .remove();
        //標籤變化 - 背景
        d3.select(this).select("rect").attr("fill", "#E0D4C2"); // 當滑鼠懸停時變深
        // - 文字
        d3.select(this)
          .select("text")
          .attr("fill", "#7B6F5A") // 文字變深
          .attr("font-weight", "bold");

        // 只有在未固定時才顯示評論細節
        showReviews(svg, d, this);
      }
    })
    .on("mouseout", function () {
      if (!Fixed) {
        svg.selectAll(`.detail-group`).remove();
        d3.select(this).select("rect").attr("fill", "#F8EDE3"); // 恢復原背景顏色
        d3.select(this)
          .select("text")
          .attr("fill", "black") // 恢復原文字顏色
          .attr("font-weight", "normal");
        // 只有在未固定時才隱藏評論細節
        svg.selectAll(".link, .review-group").remove();
      }
    })
    .on("click", function (event, d) {
      // 點擊時鎖定顯示評論細節
      if (Fixed) {
        //要收起來時
        //全部標籤變正常
        d3.selectAll(".block-group").select("rect").attr("fill", "#F8EDE3"); // 恢復原背景顏色
        d3.selectAll(".block-group")
          .select("text")
          .attr("fill", "black") // 恢復原文字顏色
          .attr("font-weight", "normal");

        // 當取消固定時，隱藏所有評論細節
        svg.selectAll(".link, .review-group").remove();
        Fixed = false; // 切換固定狀態
      } else {
        // 點擊時保留已顯示的評論細節
        showReviews(svg, d, this);
        Fixed = true;
      }
    });

  groups
    .append("rect")
    .attr("class", "block")
    .attr("width", 100)
    .attr("height", 30)
    .attr("rx", 15) // 圓角矩形的半徑
    .attr("ry", 15)
    .attr("fill", "#F8EDE3"); // 正確設定背景顏色

  groups
    .append("text")
    .attr("class", "block-text")
    .attr("x", 50)
    .attr("y", 18)
    .attr("text-anchor", "middle")
    .attr("dominant-baseline", "middle")
    .attr("fill", "black")
    .text((d) => d.text);

  const colors = ["#FF70AE", "#85B4FF", "#FFCE47"]; // 定義顏色陣列

  // 一開始就顯示第一個類別的總評和第一家餐廳的評論細節
  const firstCategory = data[0];
  console.log("spider", firstCategory);
  showReviews(svg, firstCategory, groups.nodes()[0]); // 顯示第一個類別的總評
}

function showReviews(svg, d, blockGroup) {
  console.log("Data passed to showReviews:", d); // 检查传入的数据
  if (!d.reviews || !Array.isArray(d.reviews)) {
    console.error("Reviews data is missing or not an array:", d.reviews);
    return;
  }
  // 清除之前的线条和区块
  svg.selectAll(".link, .review-block, .detail-link, .detail-block").remove();

  const blockX = 20;
  const blockY = 30; // 这里可以根据需要调整Y坐标 一开始高度
  const blockWidth = 150;
  const colors = ["#FF70AE", "#85B4FF", "#FFCE47"]; // 定义颜色数组

  // 获取总评区块的位置，作为连线的起点
  const blockTransform = d3.select(blockGroup).attr("transform");
  const [translateX, translateY] = blockTransform
    .match(/translate\(([^,]+),([^)]+)\)/)
    .slice(1)
    .map(Number);

  let previousEndY = blockY + 20; // 初始Y位置

  d.reviews.forEach((review, i) => {
    // 计算文本高度
    const tempText = svg
      .append("text")
      .attr("class", "temp-text")
      .attr("x", 0) // 放在视图外，避免影响布局
      .attr("y", 0)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "hanging")
      .attr("visibility", "hidden")
      .attr("width", 130) // 根据区块宽度设置文本宽度
      .text(review);

    wrapText(tempText, 130);  // 自动换行

    const bbox = tempText.node().getBBox();
    const textHeight = bbox.height + 10; // 加上适当的 padding
    tempText.remove();

    // 根据文本高度调整区块高度
    const blockHeight = Math.max(textHeight, 45); // 设置最小高度为 45，避免过小
    const startX = translateX - 50 + blockWidth;
    const startY = translateY + 15; //线连在同一个点上
    const endX = blockX + 120;
    const endY = previousEndY + i * 3; // 更新endY位置

    // 更新下一个区块的起始位置
    previousEndY += blockHeight + 5; // 20 是区块之间的间距

    // 绘制非直线曲线并添加动画效果
    svg
      .append("path")
      .attr("class", "link")
      .attr("fill", "none")
      .attr("stroke", colors[i]) // 使用颜色数组中的颜色
      .attr("stroke-width", 2)
      .attr(
        "d",
        d3
          .linkHorizontal()
          .x((d) => d[0])
          .y((d) => d[1])({
          source: [startX, startY],
          target: [startX, startY], // 动画起始点在源点
        })
      )
      .transition() // 动画过渡
      .duration(300) // 持续时间300ms
      .attr(
        "d",
        d3
          .linkHorizontal()
          .x((d) => d[0])
          .y((d) => d[1])({
          source: [startX, startY],
          target: [endX - 20, endY - 10],
        })
      );

    const reviewGroup = svg
      .append("g")
      .attr("class", `review-group review-${i + 1}`)
      .attr(
        "transform",
        `translate(${endX + 10}, ${endY - 40})`
      ) //调整区块间距
      .datum(d);

    // 添加区块
    reviewGroup
      .append("rect")
      .attr("width", 0) // 动画起始宽度为0
      .attr("height", blockHeight)
      .attr("rx", 10)
      .attr("ry", 10)
      .attr("fill", colors[i]) // 使用颜色数组中的颜色作为背景
      .attr("fill-opacity", 0.5)
      .attr("transform", "translate(-30, 0)") // 向左移动区块20像素
      .transition() // 动画过渡
      .duration(300) // 持续时间300ms
      .attr("width", 190) // 最终宽度为190
      .attr("height", blockHeight);

    // 添加文字
    reviewGroup
      .append("text")
      .attr("class", "block-text")
      .attr("x", -20)
      .attr("y", 20)
      .attr("text-anchor", "start")
      .attr("dominant-baseline", "middle")
      .text(review)
      .transition() // 动画过渡
      .duration(300) // 持续时间300ms
      .call(wrapText, 180) // 自动换行
      .attr("opacity", 1); // 最终不透明度为1
  });
}

// 自动换行函数
function wrapText(text, width) {
  text.each(function () {
    const text = d3.select(this);
    const words = text.text().split(/(?=。)/).reverse(); // 根据句号进行分割
    let word,
      line = [],
      lineNumber = 0,
      lineHeight = 1.1, // ems
      y = text.attr("y"),
      x = text.attr("x"),
      dy = parseFloat(text.attr("dy") || 0),
      tspan = text
        .text(null)
        .append("tspan")
        .attr("x", x)
        .attr("y", y)
        .attr("dy", dy + "em");

    while ((word = words.pop())) {
      line.push(word);
      tspan.text(line.join(""));

      // 检查是否达到换行宽度或者遇到句号，并且确保句号不是文本的最后一个
      if (tspan.node().getComputedTextLength() > width || (word.includes("。") && words.length > 0)) {
        line.pop(); // 移除最后一个字符，让它移到下一行
        tspan.text(line.join(""));
        line = [word];
        tspan = text
          .append("tspan")
          .attr("x", x)
          .attr("y", y)
          .attr("dy", ++lineNumber * lineHeight + dy + "em")
          .text(word);
      }
    }
  });
}


