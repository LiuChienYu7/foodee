// Change Photo
// Function to update the patterns with either environment or food images

function updatePatterns(buttonId, data, svg) {
    data.forEach((d) => {
        let imageUrl;
        if (buttonId === "environment") {
            imageUrl = d.r_photo_env1;
        } else if (buttonId === "food") {
            imageUrl = d.r_photo_food1;
        }
        const patternId = `pattern-${d.r_id}`;
        
        // 根據 patternId 更新對應的圖片 URL
        svg.select(`#${patternId} image`).attr("xlink:href", imageUrl);
    });
}


// Add event listeners for both buttons

// document.getElementById("environment").addEventListener("click", function () {
//     updatePatterns("environment", data, svg); // Pass the ID of the clicked button
// });

// document.getElementById("food").addEventListener("click", function () {
//     updatePatterns("food", data, svg); // Pass the ID of the clicked button
// });
