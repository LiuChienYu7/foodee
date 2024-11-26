// // Change Photo
// // Function to update the patterns with either environment or food images

let currentImage = "environment"; // 預設為環境照片

function switchPhoto(data, svg) {
    const environmentBtn = document.getElementById("environment");
    const foodBtn = document.getElementById("food");

    // 初始化圖片和按鈕狀態
    updatePatterns(currentImage, data, svg);
    updateSwitchBtn(currentImage);

    environmentBtn.addEventListener("click", function () {
        currentImage = "environment"; // 更新目前顯示的照片類型
        updatePatterns(currentImage, data, svg); // 更新照片
        updateSwitchBtn(currentImage); // 更新按鈕
    });

    foodBtn.addEventListener("click", function () {
        currentImage = "food"; // 更新目前顯示的照片類型
        updatePatterns(currentImage, data, svg); // 更新照片
        updateSwitchBtn(currentImage); // 更新按鈕
    });

    function updatePatterns(currentImage, data, svg) {
        data.forEach((d) => {
            let imageUrl;
            if(currentImage == "environment"){
                imageUrl = d.r_photo_env1;
            }
            else if(currentImage == "food"){
                imageUrl = d.r_photo_food1;
            }

            // 根據 Id 更新對應的圖片
            const patternId = `pattern-${d.r_id}`;
            svg.select(`#${patternId} image`).attr("xlink:href", imageUrl);
        });
    }

    function updateSwitchBtn(currentImage) {
        // 重置所有按鈕
        const buttons = document.querySelectorAll(".color-button");
        buttons.forEach((button) => button.classList.remove("active-color"));

        // 根據照片類型顯示對應的按鈕設計
        if (currentImage == "environment") {
            environmentBtn.classList.add("active-color");
        } else if (currentImage == "food") {
            foodBtn.classList.add("active-color");
        }
    }
}
