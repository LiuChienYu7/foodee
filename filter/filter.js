window.onload = function() {
    slideMin();
    slideMax();
    slideTimeMin();
    slideTimeMax();
};

// price filter functions
const minVal = document.querySelector(".min-val");
const maxVal = document.querySelector(".max-val");
const priceInputMin = document.querySelector(".min-input");
const priceInputMax = document.querySelector(".max-input");
const minTooltip = document.querySelector(".min-tooltip");
const maxTooltip = document.querySelector(".max-tooltip");
const minGap = 100;
const range = document.querySelector(".slider-track");
const sliderMinValue = parseInt(minVal.min);
const sliderMaxValue = parseInt(maxVal.max);

function slideMin() {
    let gap = parseInt(maxVal.value) - parseInt(minVal.value);
    if (gap <= minGap) {
        minVal.value = parseInt(maxVal.value) - minGap;
    }
    minTooltip.innerHTML = "$" + minVal.value;
    priceInputMin.value = minVal.value;
    setArea();
}

function slideMax() {
    let gap = parseInt(maxVal.value) - parseInt(minVal.value);
    if (gap <= minGap) {
        maxVal.value = parseInt(minVal.value) + minGap;
    }
    maxTooltip.innerHTML = "$" + maxVal.value;
    priceInputMax.value = maxVal.value;
    setArea();
}

function setArea() {
    range.style.left = (minVal.value / sliderMaxValue) * 100 + "%";
    minTooltip.style.left = (minVal.value / sliderMaxValue) * 100 + "%";
    range.style.right = 100 - (maxVal.value / sliderMaxValue) * 100 + "%";
    maxTooltip.style.right = 100 - (maxVal.value / sliderMaxValue) * 100 + "%";
}

function setMinInput() {
    let minPrice = parseInt(priceInputMin.value);
    if (minPrice < sliderMinValue) {
        priceInputMin.value = sliderMinValue;
    }
    minVal.value = priceInputMin.value;
    slideMin();
}

function setMaxInput() {
    let maxPrice = parseInt(priceInputMax.value);
    if (maxPrice > sliderMaxValue) {
        priceInputMax.value = sliderMaxValue;
    }
    maxVal.value = priceInputMax.value;
    slideMax();
}

// Time filter functions
const minTimeVal = document.querySelector(".min-time");
const maxTimeVal = document.querySelector(".max-time");
const timeInputMin = document.querySelector(".min-time-input");
const timeInputMax = document.querySelector(".max-time-input");
const minTimeTooltip = document.querySelector(".min-time-tooltip");
const maxTimeTooltip = document.querySelector(".max-time-tooltip");
const timeMinGap = 15;
const timeRange = document.querySelectorAll(".slider-track")[1]; // second slider track
const timeSliderMinValue = parseInt(minTimeVal.min);
const timeSliderMaxValue = parseInt(maxTimeVal.max);

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
    timeRange.style.left = (minTimeVal.value / timeSliderMaxValue) * 100 + "%";
    minTimeTooltip.style.left = (minTimeVal.value / timeSliderMaxValue) * 100 + "%";
    timeRange.style.right = 100 - (maxTimeVal.value / timeSliderMaxValue) * 100 + "%";
    maxTimeTooltip.style.right = 100 - (maxTimeVal.value / timeSliderMaxValue) * 100 + "%";
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

        if (checkedItems && checkedItems.length > 3) {
            btnText.innerText = `已選擇${checkedItems.length}個`;
        } else if (checkedItems && checkedItems.length > 0) {
            let selectedText = Array.from(checkedItems).map(itemTextElement => itemTextElement.innerText).join(', ');
            btnText.innerText = `${selectedText}`;
        } else {
            btnText.innerText = "選擇評分";
        }
    });
});

document.addEventListener("click", (event) => {
    // 如果點擊的不是 selectBtn 或 listItems，則隱藏 listItems
    if (!selectBtn.contains(event.target) && !listItems.contains(event.target)) {
        selectBtn.classList.remove("open");
        listItems.classList.remove("open");
    }
});



// panel function
document.getElementById("left-side-arr").addEventListener("click", function() {
    var leftPanel = document.getElementById("left-panel");
    if (leftPanel.classList.contains("collapsed")) {
        leftPanel.classList.remove("collapsed");
    } else {
        leftPanel.classList.add("collapsed");
    }
});

