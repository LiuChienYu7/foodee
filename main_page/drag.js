// drag function
function dragElement(circles, x, y) {
    circles.call(d3.drag()
        .on("start", function (event, d) {
            d.originalX = event.x;
            d.originalY = event.y;
        })
        .on("drag", function (event, d) {
            d3.select(this).attr("transform", `translate(${event.x}, ${event.y})`);
        })
        .on("end", function (event, d) {
            const box4 = document.getElementById("box4");
            const box4Rect = box4.getBoundingClientRect();
            const circleBounds = this.getBoundingClientRect();

            // Check if circle overlaps with box4
            if (
                circleBounds.right > box4Rect.left &&
                circleBounds.left < box4Rect.right &&
                circleBounds.bottom > box4Rect.top &&
                circleBounds.top < box4Rect.bottom
            ) {
                box4.classList.add("expanded");

                const popupContent = document.getElementById("popup-content");
                const restaurantInfo = document.createElement("div");
                restaurantInfo.className = "restaurant-info";
                restaurantInfo.innerHTML = `
                <h4>${d.r_name}</h4>
                <img src="${d.r_photo_food1}" alt="${d.r_name}" style="width: 80px; height: 80px; object-fit: cover;">
            `;
                popupContent.appendChild(restaurantInfo);
            }
            else {
                // Return circle to original position
                d3.select(this).attr("transform", `translate(${x(d.r_price_low)}, ${y(d.r_time_low) + y.bandwidth() / 2})`);
            }


        })
    );

}

/*
// Toggle box4 on click
document.querySelector(".box4-header").addEventListener("click", function () {
    box4.classList.toggle("expanded");
});
*/