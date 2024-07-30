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

// 設定SVG的寬度和高度 大小
const width = 400;
const height = 150;
const margin = { left: 48 , right:30};
const svg = d3.select("svg.word_tree")
    .attr("width", width)
    .attr("height", height)
    .append('g')
    .attr("transform", `translate(${margin.left}, 0)`);

// 建立Cluster佈局
const clusterLayout = d3.cluster().size([height, width-110]);
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

// 點擊事件處理
function onClick(event, d) {
    if (d.depth === 0) {
        // 根節點：展開所有節點
        expandAll(root);
    } else {
        // 其他節點：僅顯示選中的節點和其子節點，隱藏其他同層節點
        toggleChildren(d);
    }
    update(root);
}

// function expandAll(d) {
//     if (d._children) {
//         d.children = d._children;
//         d._children = null;
//     }
//     if (d.children) {
//         d.children.forEach(expandAll);
//     }
// }
function expandAll(d) {
    if (d._children) {
        d.children = d._children;
        d._children = null;
    }

    if (d.temp) {
        d.temp.forEach(sibling => {
            addSibling(d.parent, sibling);
        });
        d.temp = null;
    }

    if (d.children) {
        d.children.forEach(expandAll);
    }
}

function getSiblingNodes(node) { //選取同一層沒被選到的node
    if (node.parent) {
        return node.parent.children.filter(sibling => sibling !== node);
    }
    return [];
}

function toggleChildren(d) {

    // if (d.depth == 1) {

    if (d.parent) { //同一個父節點的其他節點小孩會被收起來 並把兄弟節點們存起來
        if (!d.sibling_itself) d.sibling_itself = [];

        d.parent.children.forEach(sibling => {
            if (sibling !== d) {
                collapse(sibling);
            }
        });
        console.log(d.sibling_itself);
    }
    // }
    // else if (d.depth == 2) {

    // }
    // else { //d.depth == 3
    //     if (d.parent) {
    //         if (!d.sibling_itself) d.sibling_itself = [];

    //         d.parent.children.forEach(sibling => {
    //             if (sibling !== d) {
    //                 collapse(sibling);
    //                 d.sibling_itself.push(sibling);
    //             }
    //         });
    //         console.log(d.sibling_itself);
    //     }
    // }

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
    if (d.children) {
        d._children = d.children;
        d.children = null;

        if (!d.temp) d.temp = [];
        if (d.parent && d.parent.children) {
            d.parent.children.forEach(sibling => {
                if (sibling !== d) {
                    d.temp.push(sibling);
                }
            });
            d.parent.children = [d]; // 只保留当前节点 d
        }
    }

    if (d._children) {
        d._children.forEach(collapse);

        // //不知道這裡怎麼寫
        // d.temp.forEach(d.temp =>{
        //     addSibling(d.parent,d.temp);
        //     d.temp = null;
        // })

    }
    console.log("Current Node:", d);
    console.log("Parent's Children:", d.parent ? d.parent.children : null);
    console.log("Temp before pushing siblings:", d.temp);

}
// function toggleChildren(d) {

//     if (d.depth == 1) {

//         if (d.parent) { //同一個父節點的其他節點會被收起來
//             d.parent.children.forEach(sibling => {
//                 if (sibling !== d) {
//                     collapse(sibling);
//                     // console.log(sibling);
//                     // console.log(sibling);
//                 }
//             });
//         }
//     }
//     else if (d.depth == 2) {

//     }
//     else { //d.depth == 3
//         if (d.parent) {
//             d.parent.children.forEach(sibling => {
//                 if (sibling !== d) {
//                     collapse(sibling);
//                 }
//             });
//         }
//     }
//     update(d);
// }

// function collapse(d) {
//     if (d.children) {
//         d._children = d.children;
//         d.children = null;
//     }
//     else if (d.parent && d.parent.children) { //同一個父節點的其他節點會被收起來
//         if (!d.parent.tempChildren) d.parent.tempChildren = [];

//         d.parent.children.forEach(sibling => {
//             if (sibling !== d && !d.parent.tempChildren.includes(sibling)) {
//                 d.parent.tempChildren.push(sibling);
//                 console.log(d.parent.tempChildren);
//             }
//         });
//         d.parent.children = [d];
//     }
//     // if (d.parent && d.parent.children) {
//     //     // 确保 tempChildren 存在
//     //     if (!d.parent.tempChildren) d.parent.tempChildren = [];

//     //     d.parent.tempChildren = getSiblingNodes(d);
//     //     console.log(d.parent.tempChildren)
//     // // 确保不重复添加兄弟节点
//     // d.parent.children.forEach(child => {
//     //     if (child !== d && !d.parent.tempChildren.includes(child)) {
//     //         d.parent.tempChildren.push(child);
//     //         // console.log(d.parent.tempChildren)
//     //     }
//     // });

//     // 仅保留当前节点 d
//     // d.parent.children = [d];
//     // }

//     else if (d._children) {
//         d._children.forEach(collapse);
//     }
// }

// function expandAll(d) {
//     if (d._children) {
//         d.children = d._children;
//         d._children = null;
//     }

//     else if (d.tempChildren) {
//         if (!d.children) d.children = [];
//         d.children = d.children.concat(d.tempChildren);
//         d.tempChildren = null;
//     }

//     else if (d.children) {
//         d.children.forEach(expandAll);
//     }

// }
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
