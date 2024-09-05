function dragElement(circles, x, y) {
    circles.call(d3.drag()
        .on("start", function (event, d) {
            // save the starting position of the element
            d.originalX = event.x;
            d.originalY = event.y;
        })
        .on("drag", function (event, d) {
            // update the current position of the element
            d3.select(this).attr("transform", `translate(${event.x}, ${event.y})`);
        })
        .on("end", function (event, d) {
            const box4 = document.getElementById("box4");
            const box4Rect = box4.getBoundingClientRect();
            const circleBounds = this.getBoundingClientRect();
            const restaurantInfo = document.getElementById("restaurant-info");
            const defaultText = document.getElementById("defaultText");
            const box4Header = document.querySelector(".box4-header");
            const compareBtn = document.getElementById("compare-btn");

            // check if the element is in box4
            if (
                circleBounds.right > box4Rect.left &&
                circleBounds.left < box4Rect.right &&
                circleBounds.bottom > box4Rect.top &&
                circleBounds.top < box4Rect.bottom
            ) {
                box4.classList.add("expanded");
                defaultText.style.display = "none";

                const restaurant = document.createElement("div");
                restaurant.className = "restaurant";
                restaurant.innerHTML = `
                    <button class="delete-restaurant"><i class="fas fa-x"></i></button>
                    <h4>${d.r_name}</h4>
                    <img src="${d.r_photo_food1}" alt="${d.r_name}" style="width: 300px; height: 300px; object-fit: cover;">
                `;
                restaurantInfo.appendChild(restaurant);
                d3.select(this).style("visibility", "hidden");

                // Update the header with the number of restaurants
                updateRestaurantCount();

                restaurant.querySelector(".delete-restaurant").addEventListener("click", function () {
                    restaurant.remove();
                    d3.select(circles.filter(cd => cd === d).nodes()[0]).style("visibility", "visible");
                    if (restaurantInfo.children.length === 1) {
                        defaultText.style.display = "block";
                    }
                    updateRestaurantCount();
                });
            }

            d3.select(this).attr("transform", `translate(${x(d.r_price_low)}, ${y(d.r_time_low) + y.bandwidth() / 2})`);

            // Function to update the restaurant count in the header
            function updateRestaurantCount() {
                const count = restaurantInfo.children.length - 1; // Exclude the defaultText element
                box4Header.textContent = `拖移餐廳看更多餐廳資訊 已選${count}間餐廳`;
            }
        })
    );
}
