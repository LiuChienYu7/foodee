// price filter functions
const minVal = document.querySelector(".min-val");
const maxVal = document.querySelector(".max-val");
const priceInputMin = document.querySelector(".min-input");
const priceInputMax = document.querySelector(".max-input");
const minTooltip = document.querySelector(".min-tooltip");
const maxTooltip = document.querySelector(".max-tooltip");
const minGap = 100;
const range = document.querySelector(".slider-track");
const sliderMinValue = parseInt(minVal.min, 10);
const sliderMaxValue = parseInt(maxVal.max, 10);

document.addEventListener("DOMContentLoaded", function() {
    minVal.value = 50; // 初始值
    maxVal.value = 2000; // 初始值
    priceInputMin.value = minVal.value; // 設置 min_input 初始值
    priceInputMax.value = maxVal.value; // 設置 max_input 初始值
    slideMin();
    slideMax();
});

// 更新滑块和输入框的值
function slideMin() {
    let gap = parseInt(maxVal.value, 10) - parseInt(minVal.value, 10);
    if (gap <= minGap) {
        minVal.value = parseInt(maxVal.value, 10) - minGap;
    }
    minTooltip.innerHTML = "$" + minVal.value;
    priceInputMin.value = minVal.value;
    setArea();
}


function slideMax() {
    let gap = parseInt(maxVal.value, 10) - parseInt(minVal.value, 10);
    if (gap <= minGap) {
        maxVal.value = parseInt(minVal.value, 10) + minGap;
    }
    maxTooltip.innerHTML = "$" + maxVal.value;
    priceInputMax.value = maxVal.value;
    setArea();
}

// 更新价格范围和工具提示
function setArea() {
    // 取得滑桿的最小值和最大值的百分比位置
    const minPricePercent = (minVal.value - minVal.min) / (minVal.max - minVal.min) * 100;
    const maxPricePercent = (maxVal.value - minVal.min) / (maxVal.max - minVal.min) * 100;
    
    // 更新範圍條的寬度和位置
    range.style.left = minPricePercent + "%";
    range.style.width = (maxPricePercent - minPricePercent) + "%";
    
    // 更新工具提示的位置
    minTooltip.style.left = minPricePercent + "%";
    maxTooltip.style.left = maxPricePercent + "%";

    // range.style.left = (minVal.value / sliderMaxValue) * 100 + "%";
    // minTooltip.style.left = (minVal.value / sliderMaxValue) * 100 + "%";
    // range.style.right = 100 - (maxVal.value / sliderMaxValue) * 100 + "%";
    // maxTooltip.style.right = 100 - (maxVal.value / sliderMaxValue) * 100 + "%";
}


function setMinInput() {
    let minPrice = parseInt(priceInputMin.value, 10);
    if (minPrice < sliderMinValue) {
        priceInputMin.value = sliderMinValue;
    }
    minVal.value = minPrice;
    slideMin();
}

function setMaxInput() {
    let maxPrice = parseInt(priceInputMax.value, 10);
    if (maxPrice > sliderMaxValue) {
        priceInputMax.value = sliderMaxValue;
        // maxPrice = sliderMaxValue;
    }
    maxVal.value = maxPrice;
    slideMax();
}



// Time filter functions
const minTimeVal = document.querySelector(".min-time");
const maxTimeVal = document.querySelector(".max-time");
const timeInputMin = document.querySelector(".min-time-input");
const timeInputMax = document.querySelector(".max-time-input");
const minTimeTooltip = document.querySelector(".min-time-tooltip");
const maxTimeTooltip = document.querySelector(".max-time-tooltip");
const timeMinGap = 10;
const timeRange = document.querySelectorAll(".slider-track")[1]; // second slider track
const timeSliderMinValue = parseInt(minTimeVal.min);
const timeSliderMaxValue = parseInt(maxTimeVal.max);

document.addEventListener("DOMContentLoaded", function() {
    minTimeVal.value = 60; // 確保初始化時設置值
    maxTimeVal.value = 180; // 確保初始化時設置值
    slideTimeMin();
    slideTimeMax();
});

function slideTimeMin() {
    let gap = parseInt(maxTimeVal.value) - parseInt(minTimeVal.value);
    if (gap <= timeMinGap) {
        minTimeVal.value = parseInt(maxTimeVal.value) - timeMinGap;
    }
    minTimeTooltip.innerHTML = minTimeVal.value + "時";
    timeInputMin.value = minTimeVal.value;
    setTimeArea();
}

function slideTimeMax() {
    let gap = parseInt(maxTimeVal.value) - parseInt(minTimeVal.value);
    if (gap <= timeMinGap) {
        maxTimeVal.value = parseInt(minTimeVal.value) + timeMinGap;
    }
    maxTimeTooltip.innerHTML = maxTimeVal.value + "時";
    timeInputMax.value = maxTimeVal.value;
    setTimeArea();
}

function setTimeArea() {
    // 取得滑桿的最小值和最大值的百分比位置
    const minPercent = (minTimeVal.value - minTimeVal.min) / (minTimeVal.max - minTimeVal.min) * 100;
    const maxPercent = (maxTimeVal.value - minTimeVal.min) / (maxTimeVal.max - minTimeVal.min) * 100;
    
    // 更新範圍條的寬度和位置
    timeRange.style.left = minPercent + "%";
    timeRange.style.width = (maxPercent - minPercent) + "%";
    
    // 更新工具提示的位置
    minTimeTooltip.style.left = minPercent + "%";
    maxTimeTooltip.style.left = maxPercent + "%";
}


function setMinTimeInput() {
    let minTime = parseInt(timeInputMin.value);
    if (minTime < timeSliderMinValue) {
        timeInputMin.value = timeSliderMinValue;
    }
    minTimeVal.value = timeInputMin.value;
    slideTimeMin();
}

function setMaxTimeInput() {
    let maxTime = parseInt(timeInputMax.value);
    if (maxTime > timeSliderMaxValue) {
        timeInputMax.value = timeSliderMaxValue;
    }
    maxTimeVal.value = timeInputMax.value;
    slideTimeMax();
}



// document.addEventListener("click", (event) => {
//     // 如果點擊的不是 selectBtn 或 listItems，則隱藏 listItems
//     if (!selectBtn.contains(event.target) && !listItems.contains(event.target)) {
//         selectBtn.classList.remove("open");
//         listItems.classList.remove("open");
//     }
// });

// // panel function
// document.getElementById("left-side-arr").addEventListener("click", function() {
//     var leftPanel = document.getElementById("left-panel");
//     if (leftPanel.classList.contains("collapsed")) {
//         leftPanel.classList.remove("collapsed");
//     } else {
//         leftPanel.classList.add("collapsed");
//     }
// });

document.addEventListener('DOMContentLoaded', function () {
    const maxSelection = 5;
    const colors = ['color-1', 'color-2', 'color-3', 'color-4', 'color-5'];
    const vibeButtons = document.querySelectorAll('.vibe-button');
    const parkingCheckbox = document.getElementById('parking-checkbox');
    const minTimeSlider = document.querySelector('.min-time');
    const maxTimeSlider = document.querySelector('.max-time');
    const minTimeInput = document.querySelector('.min-time-input');
    const maxTimeInput = document.querySelector('.max-time-input');
    let selectedButtons = [];
    let availableColors = [...colors];
    let isNoLimitActive = false;
    const noLimitBtn = document.getElementById('no-limit-btn');
    noLimitBtn.classList.add('active');  // 頁面加載時添加 active 類
    isNoLimitActive = true;  // 同步狀態變量
    const checkboxes = document.querySelectorAll('.opening-hours input[type="checkbox"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters();
        });
    });
    const dayLabels = document.querySelectorAll('.day-label');

    dayLabels.forEach(label => {
        label.addEventListener('click', function() {
            label.classList.toggle('active');  // 切換選中狀態
            applyFilters();  // 調用篩選函數
        });
    });

    // 頁面加載時預設勾選所有 checkbox
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;  // 設置 checkbox 為已選擇狀態
    });

    // 更新篩選條件
    function applyFilters() {

        console.log("applyFilters function is defined");
        const minTime = minTimeSlider.value;
        const maxTime = maxTimeSlider.value;
        const hasParking = parkingCheckbox.checked ? 1 : 0;
        const selectedRatings = Array.from(document.querySelectorAll('.filter-star .l-item.checked .item-text'))
            .map(el => el.innerText.replace('⭐', '').trim());
        const selectedVibes = Array.from(vibeButtons)
            .filter(button => button.classList.contains('selected'))
            .map(button => button.innerText.trim());
        const minPrice = parseInt(minVal.value, 10);
        const maxPrice = parseInt(maxVal.value, 10);
        var button = document.getElementById("no-limit-btn");
        button.classList.add("selected");
        const selectedDays = Array.from(document.querySelectorAll('.day-label.active'))
            .map(label => label.getAttribute('data-day'));


        let url = 'http://localhost/food_project/filter/data.php?';
    
        if (selectedVibes.length > 0) {
            const vibeQuery = selectedVibes.map(vibe => encodeURIComponent(vibe)).join(',');
            url += `vibes=${vibeQuery}&`; // 應該使用 `vibes` 而不是 `vibe`
        }

        if (selectedRatings.length > 0) {
            const ratingQuery = selectedRatings.map(rating => encodeURIComponent(rating)).join(',');
            url += `ratings=${ratingQuery}&`;
            console.log('Selected Ratings:', selectedRatings); // 打印選中的 ratings
            console.log('Ratings Query:', ratingQuery); // 打印 ratings 查詢字符串
        }

        if (hasParking) {
            url += `hasParking=${hasParking}&`;
        }

        if (minTime && maxTime) {
            url += `min_time=${minTime}&max_time=${maxTime}&`;
        }

        if (minPrice && maxPrice) {
            url += `min_price=${minPrice}&max_price=${maxPrice}&`;
        }    

        if (isNoLimitActive) {
            url += 'no_limit=1&';
        }

        // 篩選資料中符合選中日期的項目
        if (selectedDays.length > 0) {
            url += `selectedDays=${selectedDays.join(',')}&`;
        }

        url = url.endsWith('&') ? url.slice(0, -1) : url;

        console.log('Request URL:', url); // 輸出請求 URL 以便檢查

        fetch(url)
            .then(response => response.json())
            .then(data => {
                // 清空顯示區域
                svg.selectAll("*").remove();

                // 更新顯示的數據
                svg.selectAll("text")
                    .data(data)
                    .enter()
                    .append("text")
                    .attr("x", () => 50)
                    .attr("y", (d, i) => 30 + i * 20)
                    .text(d => d.r_name);
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    // 監聽每個按鈕的點擊事件
    vibeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (button.classList.contains('selected')) {
                button.classList.remove('selected');
                const index = selectedButtons.indexOf(button);
                selectedButtons.splice(index, 1);
                const colorClass = colors.find(color => button.classList.contains(color));
                button.classList.remove(colorClass);
                availableColors.push(colorClass);
                availableColors.sort((a, b) => colors.indexOf(a) - colors.indexOf(b));
            } else {
                if (selectedButtons.length < maxSelection && availableColors.length > 0) {
                    button.classList.add('selected');
                    selectedButtons.push(button);
                    const colorClass = availableColors.shift();
                    button.classList.add(colorClass);
                    console.log(`Button selected: ${button.innerText.trim()}, Color class added: ${colorClass}`);
                } else if (selectedButtons.length >= maxSelection) {
                    alert(`最多只能選擇 ${maxSelection} 個選項`);
                }
            }
            console.log(`Button state: ${button.innerText.trim()}, Classes: ${Array.from(button.classList).join(', ')}`);
            applyFilters();
        });
    });


    // 監聽每個 checkbox 的變化事件
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            applyFilters(); // 當 checkbox 狀態改變時，重新篩選
        });
    });

    // 監聽勾選框變化
    parkingCheckbox.addEventListener('change', applyFilters);

    // 監聽滑桿和輸入框變化
    minVal.addEventListener('input', applyFilters);
    maxVal.addEventListener('input', applyFilters);
    priceInputMin.addEventListener('change', applyFilters);
    priceInputMax.addEventListener('change', applyFilters);

    // 監聽滑桿和輸入框變化
    minTimeSlider.addEventListener('input', applyFilters);
    maxTimeSlider.addEventListener('input', applyFilters);
    minTimeInput.addEventListener('change', applyFilters);
    maxTimeInput.addEventListener('change', applyFilters);

    // 當「無限制」按鈕被點擊時
    noLimitBtn.addEventListener('click', function () {
        isNoLimitActive = !isNoLimitActive;
        noLimitBtn.classList.toggle('active', isNoLimitActive);
        applyFilters();
    });

    function updateTimeSliders() {
        minTimeSlider.value = parseInt(minTimeInput.value, 10);
        maxTimeSlider.value = parseInt(maxTimeInput.value, 10);
    }



    // stars selection
    const selectBtn = document.querySelector(".select-btn");
    const listItems = document.querySelector(".list-items");
    const items = document.querySelectorAll(".l-item");

    selectBtn.addEventListener("click", (event) => {
        selectBtn.classList.toggle("open");
        listItems.classList.toggle("open");
        event.stopPropagation(); // 阻止事件冒泡
    });

    items.forEach((item) => {
        item.addEventListener("click", () => {
            item.classList.toggle("checked");

            let checkedItems = document.querySelectorAll(".l-item.checked .item-text"),
                btnText = document.querySelector(".btn-text");

            if (checkedItems && checkedItems.length > 0) {
                // 獲取所有選中的星等
                let selectedText = Array.from(checkedItems).map(itemTextElement => itemTextElement.innerText.replace('⭐', '')).join('、');
                btnText.innerText = `${selectedText}⭐`;
            } else {
                btnText.innerText = "選擇評分";
            }

            // 调用 applyFilters 函数
            applyFilters();
        });
    });
    // 初始化時顯示滑桿和輸入框的值
    updateTimeSliders();
    applyFilters();
});

// window.onload = function() {
//     console.log("Window loaded");
//     applyFilters();
//     slideMin();
//     slideMax();
//     minTimeVal.value = 60; // 設置 min slider 預設值
//     maxTimeVal.value = 120; // 設置 max slider 預設值
//     slideTimeMin();
//     slideTimeMax();
//     setTimeArea();
// };


