// 初始化資料結構
const data = {
    name: "評論",
    children: [
        {
            name: "食物",
            children: [{
                name: "食物總評",
                children: [
                    { name: "美味" },
                    { name: "新鮮" },
                    { name: "多樣化" }
                ]
            }]
        },
        {
            name: "服務",
            children: [{
                name: "服務總評",
                children: [
                    { name: "友善" },
                    { name: "迅速" },
                    { name: "專業" }
                ]
            }]
        },
        {
            name: "划算",
            children: [{
                name: "划算總評",
                children: [
                    { name: "便宜" },
                    { name: "超值" },
                    { name: "合理" }
                ]
            }]
        },
        {
            name: "環境",
            children: [{
                name: "環境總評",
                children: [
                    { name: "舒適" },
                    { name: "安靜" },
                    { name: "溫馨" }
                ]
            }]
        }
    ]
};

// fetch(jsonUrl)
//     .then(response => response.json())
//     .then(rawData => {
//         // 初始化資料結構
//         const data = {
//             name: "評論",
//             children: [
//                 {
//                     name: "食物",
//                     children: [
//                         {
//                             name: restaurant.food_comment_sum,
//                             children: [
//                                 { name: restaurant.food_review1 },
//                                 { name: restaurant.food_review2 },
//                                 { name: restaurant.food_review3 }
//                             ]
//                         }
//                     ]
//                 },
//                 {
//                     name: "服務",
//                     children: [
//                         {
//                             name: restaurant.service_comment_sum,
//                             children: [
//                                 { name: restaurant.service_review1 },
//                                 { name: restaurant.service_review2 },
//                                 { name: restaurant.service_review3 }
//                             ]
//                         }
//                     ]
//                 },
//                 {
//                     name: "划算",
//                     children: [
//                         {
//                             name: restaurant.value_comment_sum,
//                             children: [
//                                 { name: restaurant.value_review1 },
//                                 { name: restaurant.value_review2 },
//                                 { name: restaurant.value_review3 }
//                             ]
//                         }
//                     ]
//                 },
//                 {
//                     name: "環境",
//                     children: [
//                         {
//                             name: restaurant.atmosphere_comment_sum,
//                             children: [
//                                 { name: restaurant.atmosphere_review1 },
//                                 { name: restaurant.atmosphere_review2 },
//                                 { name: restaurant.atmosphere_review3 }
//                             ]
//                         }
//                     ]
//                 }
//             ]
//         }
//     })
// 設定SVG的寬度和高度 大小
const word_tree_width = 400;
const word_tree_height = 150;
const margin = { left: 48, right: 30 };
const svg = d3.select("svg.word_tree")
    .attr("width", word_tree_width)
    .attr("height", word_tree_height)
    .append('g')
    .attr("transform", `translate(${margin.left}, 0)`);

// 建立Cluster佈局
const clusterLayout = d3.cluster().size([word_tree_height, word_tree_width - 110]);
const root = d3.hierarchy(data);
clusterLayout(root);

// 繪製連接線
let link = svg.selectAll('path.link')
    .data(root.links())
    .enter()
    .append('path')
    .attr('class', 'link')
    .attr('d', d3.linkHorizontal()
        .x(d => d.y)
        .y(d => d.x));

// 繪製節點和節點標籤
let node = svg.selectAll('g.node')
    .data(root.descendants())
    .enter()
    .append('g')
    .attr('class', 'node')
    .attr('transform', d => `translate(${d.y},${d.x})`)
    .on("click", onClick);

// 文字 初始狀態不顯示圓圈
node.append('text')
    .attr('dy', '0.32em')
    .text(d => d.data.name)
    .attr('dx', d => d.depth === 0 ? -10 : d.children ? -8 : 10)
    .style('text-anchor', d => d.children || d.depth === 0 ? 'end' : 'start')
    .style('fill', 'black')
    .style('font-size', d => 20 - (d.depth * 5) + 'px');

// 全局变量
// let temp = [];

// 點擊事件處理
function onClick(event, d) {
    if (d.depth === 0) {
        // 根節點：展開所有節點
        expandAll(root);
        // location.reload();
    } else {
        // 其他節點：僅顯示選中的節點和其子節點，隱藏其他同層節點
        toggleChildren(d);
    }
    update(root);
}


function expandAll(d) {
    console.log("Expanding Node:", d.data.name);

    // 恢复当前节点的所有子节点
    if (d._children) {
        d.children = d._children;
        d._children = null;
    }

    // 恢复父节点的所有兄弟节点
    if (d.temp) {
        d.temp.forEach(sibling => {
            console.log("Restoring sibling:", sibling.data.name);
            if (sibling.parent) {
                sibling.parent.children.push(sibling);
            }
        });
        d.temp = []; // 清空 temp
    } else {
        console.log("d.temp is undefined or empty");
    }

    // 递归恢复所有子节点
    if (d.children) {
        d.children.forEach(expandAll);
    }

    console.log("After Expand:", d);
    console.log("d.children:", d.children);
    // console.log("d.temp:", d.temp);
    // if (d.temp) {
    //     d.temp.forEach((sibling, index) => {
    //         console.log(`Index ${index}:`, sibling);
    //     });
    // } else {
    //     console.log("d.temp is undefined or empty");
    // }
}

// function expandAll(d) {
//     console.log("Expanding Node:", d.data.name);
//     if (d._children) {
//         d.children = d._children;
//         d._children = null;
//     }

//     if (d.parent && Array.isArray(d.temp) && d.temp.length > 0) {
//         d.temp.forEach(sibling => {
//             addSibling(d.parent, sibling);
//         });
//         // d.parent.temp = null;
//         d.temp = null;
//     }

//     if (d.children) {
//         d.children.forEach(expandAll);
//     }
//     console.log("After Expand:", d);
// }

function getSiblingNodes(node) { //選取同一層沒被選到的node
    if (node.parent) {
        return node.parent.children.filter(sibling => sibling !== node);
    }
    return [];
}

function toggleChildren(d) {

    if (d.parent) { //同一個父節點的其他節點小孩會被收起來 並把兄弟節點們存起來

        d.parent.children.forEach(sibling => {
            if (sibling !== d) {
                collapse(sibling);
            }
        });
        // 确保仅保留当前节点
        d.parent.children = [d];
    }

    // 展开当前节点的子节点
    if (d._children) {
        d.children = d._children;
        d._children = null;
    }

}

//把踢跳的兄弟節點 加回tree裡面
function addSibling(parentNode, newNode) {
    // 确保 parentNode 有 children 属性
    if (!parentNode.children) {
        parentNode.children = [];
    }

    // 将新的兄弟节点添加到父节点的 children 数组中
    parentNode.children.push(newNode);

    // 更新树视图
    update(root);
}

function collapse(d) {
    console.log("Collapsing Node:", d.data.name);
    if (d.children) {
        d._children = d.children;
        d.children = null;

        // 單個sibling被存起來  但這裡好像沒存好
        if (!d.temp) d.temp = [];
        // if (d.parent && d.parent.children) {
        //     console.log("temp in collapse: ", d.temp);
        //     d.parent.children.forEach(sibling => {
        //         if (sibling !== d) {
        //             d.temp.push(sibling);
        //         }
        //     });
        //     d.parent.children = [d]; // 只保留当前节点 d
        // }
        if (d.parent) {
            d.temp.push(d);
            // d.parent.children = [d]; // 只保留当前节点 d
        }
        console.log("d.parent.children",d.parent.children);
        // console.log("temp in collapse: ", d.temp);
    }

    if (d._children) {
        d._children.forEach(collapse);
    }
    
    console.log("After Collapse:", d);
    console.log("d._children:", d._children);
    // console.log("d.temp:", d.temp);
    // if (d.temp) {
    //     d.temp.forEach((sibling, index) => {
    //         console.log(`Index ${index}:`, sibling);
    //     });
    // }
}

// 更新函數
function update(source) {
    // var node = root.descendants().filter(d => !d.hidden);
    // var link = root.links().filter(d => !d.source.hidden && !d.target.hidden);
    // 計算新的布局
    clusterLayout(root);

    // 更新節點 等等要調整一下
    node = svg.selectAll('g.node')
        .data(root.descendants(), d => d.data.name)
        .join(
            enter => enter.append('g')
                .attr('class', 'node')
                .attr('transform', d => `translate(${d.y},${d.x})`)
                .on("click", onClick)
                .call(enter => enter.append('text')
                    .attr('dy', '0.32em')
                    .text(d => d.data.name)
                    .attr('dx', d => d.depth === 0 ? -10 : d.children ? -8 : 10)
                    .style('text-anchor', d => d.children || d.depth === 0 ? 'end' : 'start')
                    .style('fill', 'black')
                    .style('font-size', d => 27 - (d.depth * 6) + 'px')
                ),
            update => update.transition().duration(500)
                .attr('transform', d => `translate(${d.y},${d.x})`)
                .select('text')
                // .style('font-size', d => d.depth === 0 ? '20px' : '10px'),
                .style('font-size', d => 20 - (d.depth * 3) + 'px'), //這裡在寫一個function
            exit => exit.remove()
        );

    // 更新連接線
    link = svg.selectAll('path.link')
        .data(root.links(), d => `${d.source.data.name}-${d.target.data.name}`)
        .join(
            enter => enter.append('path')
                .attr('class', 'link')
                .attr('d', d3.linkHorizontal()
                    .x(d => d.y)
                    .y(d => d.x)
                ),
            update => update.transition().duration(500)
                .attr('d', d3.linkHorizontal()
                    .x(d => d.y)
                    .y(d => d.x)
                ),
            exit => exit.remove()
        );
}

// 初始化圖形
update(root);
