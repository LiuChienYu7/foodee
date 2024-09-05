let currentPage = 1;
const restaurantsPerPage = 4;
// 記錄選中的餐廳ID
let selectedRestaurantIds = [];

function dragElement(circles, x, y) {
  circles.call(
    d3
      .drag()
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

          const upperBlock = document.createElement("div");
          upperBlock.className = "upperBlock";

          // Create and append the restaurant name
          const restaurantName = document.createElement("h4");
          restaurantName.className = "drag-name";
          restaurantName.textContent = d.r_name;
          upperBlock.appendChild(restaurantName);

          //add star
          const star = document.createElement("div");
          star.className = "drag-star";
          addStars(star, d.r_rating);
          upperBlock.appendChild(star);

          //add parking
          const parking = document.createElement("divs");
          parking.className = "drag-parking";

          const parkingSvg = `
          <svg fill="${
            d.r_has_parking == 1 ? "#0000FF" : "#A9A9A9"
          }" width="20px" height="20px" viewBox="0 0 454 454" xmlns="http://www.w3.org/2000/svg">
              <g>
                  <g>
                      <path d="M228.062,154.507h-34.938v65.631h34.938c18.094,0,32.814-14.72,32.814-32.814
                          C260.877,169.23,246.156,154.507,228.062,154.507z"/>
                      <path d="M0,0v454h454V0H0z M228.062,279.648h-34.938v79.398h-59.512V94.952l94.451,0.043c50.908,0,92.325,41.418,92.325,92.328
                          C320.388,238.232,278.971,279.648,228.062,279.648z"/>
                  </g>
              </g>
          </svg>`;

          parking.innerHTML = parkingSvg;
          upperBlock.appendChild(parking);

          // Create and append the delete button
          const deleteButton = document.createElement("button");
          deleteButton.className = "delete-restaurant";
          deleteButton.innerHTML = '<i class="fas fa-x"></i>';
          upperBlock.appendChild(deleteButton);

          restaurant.appendChild(upperBlock);

          // Create and append the restaurant image
          const restaurantImage = document.createElement("img");
          restaurantImage.src = d.r_photo_food1;
          restaurantImage.alt = d.r_name;
          restaurantImage.style.width = "200px";
          restaurantImage.style.height = "200px";
          restaurantImage.style.objectFit = "cover";
          restaurant.appendChild(restaurantImage);

          const vibeTitle = document.createElement("h5");
          vibeTitle.className = "drag-vibe-title";
          vibeTitle.textContent = "氣氛";

          const vibeTagsDiv = document.createElement("div");
          vibeTagsDiv.className = "drag-vibe-tag";
          vibeTagsDiv.appendChild(vibeTitle);

          // 從 r_vibe 分割出來的標籤
          if (d.r_vibe) {
            const vibes = d.r_vibe.split("，");
            vibes.forEach((vibe) => {
              const tagDiv = document.createElement("div");
              tagDiv.className = "drag-restaurant-tag";
              tagDiv.style.cursor = "default";
              tagDiv.textContent = vibe.trim();
              vibeTagsDiv.appendChild(tagDiv);
            });
          }
          restaurant.appendChild(vibeTagsDiv);

          //food 標籤
          const foodTitle = document.createElement("h5");
          foodTitle.className = "drag-food-title";
          foodTitle.textContent = "食物";

          const foodTagsDiv = document.createElement("div");
          foodTagsDiv.className = "drag-food-tag";
          foodTagsDiv.appendChild(foodTitle);

          // 從 r_vibe 分割出來的標籤
          if (d.r_food_dishes) {
            const foods = d.r_food_dishes.split("、");
            foods.forEach((food) => {
              const tagDiv = document.createElement("div");
              tagDiv.className = "drag-restaurant-tag";
              tagDiv.style.cursor = "default";
              tagDiv.textContent = food.trim();
              foodTagsDiv.appendChild(tagDiv);
            });
          }
          restaurant.appendChild(foodTagsDiv);

          //用餐時間和價錢
          const priceAndDiningTime = document.createElement("div");
          priceAndDiningTime.className = "price-diningTime";

          const diningTimeElement = document.createElement("div");
          const diningTime = d.r_time_low ? `${d.r_time_low} 分鐘` : "未限時";
          diningTimeElement.innerHTML = `
            <svg fill="#000000" width="20px" height="20px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path d="M20,3a1,1,0,0,0,0-2H4A1,1,0,0,0,4,3H5.049c.146,1.836.743,5.75,3.194,8-2.585,2.511-3.111,7.734-3.216,10H4a1,1,0,0,0,0,2H20a1,1,0,0,0,0-2H18.973c-.105-2.264-.631-7.487-3.216-10,2.451-2.252,3.048-6.166,3.194-8Zm-6.42,7.126a1,1,0,0,0,.035,1.767c2.437,1.228,3.2,6.311,3.355,9.107H7.03c.151-2.8.918-7.879,3.355-9.107a1,1,0,0,0,.035-1.767C7.881,8.717,7.227,4.844,7.058,3h9.884C16.773,4.844,16.119,8.717,13.58,10.126ZM12,13s3,2.4,3,3.6V20H9V16.6C9,15.4,12,13,12,13Z"/>
            </svg> 
            ${diningTime}`;
          priceAndDiningTime.appendChild(diningTimeElement);

          const price = document.createElement("div");
          price.className = "drag-price";
          price.innerHTML = `
            <svg height="20px" width="20px" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 235.517 235.517" fill="#f9f053">
                <path d="M118.1,235.517c7.898,0,14.31-6.032,14.31-13.483c0-7.441,0-13.473,0-13.473 c39.069-3.579,64.932-24.215,64.932-57.785v-0.549c0-34.119-22.012-49.8-65.758-59.977V58.334c6.298,1.539,12.82,3.72,19.194,6.549 c10.258,4.547,22.724,1.697,28.952-8.485c6.233-10.176,2.866-24.47-8.681-29.654c-11.498-5.156-24.117-8.708-38.095-10.236V8.251 c0-4.552-6.402-8.251-14.305-8.251c-7.903,0-14.31,3.514-14.31,7.832c0,4.335,0,7.843,0,7.843 c-42.104,3.03-65.764,25.591-65.764,58.057v0.555c0,34.114,22.561,49.256,66.862,59.427v33.021 c-10.628-1.713-21.033-5.243-31.623-10.65c-11.281-5.755-25.101-3.72-31.938,6.385c-6.842,10.1-4.079,24.449,7.294,30.029 c16.709,8.208,35.593,13.57,54.614,15.518v13.755C103.79,229.36,110.197,235.517,118.1,235.517z M131.301,138.12 c14.316,4.123,18.438,8.257,18.438,15.681v0.555c0,7.979-5.776,12.651-18.438,14.033V138.12z M86.999,70.153v-0.549 c0-7.152,5.232-12.657,18.71-13.755v29.719C90.856,81.439,86.999,77.305,86.999,70.153z"/>
            </svg> 
            ${d.r_price_low}元 ~ ${d.r_price_high}元`;
          priceAndDiningTime.appendChild(price);

          restaurant.appendChild(priceAndDiningTime);

          restaurantInfo.appendChild(restaurant);
          d3.select(this).style("visibility", "hidden");

          // Update the header with the number of restaurants
          updateRestaurantCount();
          updatePagination();

          restaurant
            .querySelector(".delete-restaurant")
            .addEventListener("click", function () {
              restaurant.remove();
              d3.select(circles.filter((cd) => cd === d).nodes()[0]).style(
                "visibility",
                "visible"
              );
              if (restaurantInfo.children.length === 1) {
                defaultText.style.display = "block";
              }
              updateRestaurantCount();
              updatePagination();
            });

          // Function to add star SVGs based on rating
          function addStars(container, rating) {
            const fullStarSVG = `
                <svg height="10px" width="10px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94" xml:space="preserve">
                    <path style="fill:#ED8A19;" d="M26.285,2.486l5.407,10.956c0.376,0.762,1.103,1.29,1.944,1.412l12.091,1.757
                    c2.118,0.308,2.963,2.91,1.431,4.403l-8.749,8.528c-0.608,0.593-0.886,1.448-0.742,2.285l2.065,12.042
                    c0.362,2.109-1.852,3.717-3.746,2.722l-10.814-5.685c-0.752-0.395-1.651-0.395-2.403,0l-10.814,5.685
                    c-1.894,0.996-4.108-0.613-3.746-2.722l2.065-12.042c0.144-0.837-0.134-1.692-0.742-2.285l-8.749-8.528
                    c-1.532-1.494-0.687-4.096,1.431-4.403l12.091-1.757c0.841-0.122,1.568-0.65,1.944-1.412l5.407-10.956
                    C22.602,0.567,25.338,0.567,26.285,2.486z"/>
                </svg>
            `;

            const halfStarSVG = `
                <svg height="10px" width="10px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 47.94 47.94" xml:space="preserve">
                    <defs>
                        <clipPath id="half-star">
                            <rect x="0" y="0" width="50%" height="100%"></rect> <!-- 只顯示左半邊 -->
                        </clipPath>
                    </defs>
                    <path style="fill:#ED8A19;" d="M26.285,2.486l5.407,10.956c0.376,0.762,1.103,1.29,1.944,1.412l12.091,1.757
                    c2.118,0.308,2.963,2.91,1.431,4.403l-8.749,8.528c-0.608,0.593-0.886,1.448-0.742,2.285l2.065,12.042
                    c0.362,2.109-1.852,3.717-3.746,2.722l-10.814-5.685c-0.752-0.395-1.651-0.395-2.403,0l-10.814,5.685
                    c-1.894,0.996-4.108-0.613-3.746-2.722l2.065-12.042c0.144-0.837-0.134-1.692-0.742-2.285l-8.749-8.528
                    c-1.532-1.494-0.687-4.096,1.431-4.403l12.091-1.757c0.841-0.122,1.568-0.65,1.944-1.412l5.407-10.956
                    C22.602,0.567,25.338,0.567,26.285,2.486z" clip-path="url(#half-star)"/>
                </svg>
            `;

            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;

            // Add full stars
            for (let i = 0; i < fullStars; i++) {
              const fullStar = document.createElement("div");
              fullStar.innerHTML = fullStarSVG;
              container.appendChild(fullStar);
            }

            // Add half star if needed
            if (hasHalfStar) {
              const halfStar = document.createElement("div");
              halfStar.innerHTML = halfStarSVG;
              container.appendChild(halfStar);
            }
          }
          addRestaurant(d.r_id);
          console.log("id有沒有正確放入", selectedRestaurantIds);
          restaurant
            .querySelector(".delete-restaurant")
            .addEventListener("click", function () {
              restaurant.remove();

              // 呼叫 removeRestaurant，傳入餐廳的 r_id
              removeRestaurant(d.r_id);

              d3.select(circles.filter((cd) => cd === d).nodes()[0]).style(
                "visibility",
                "visible"
              );
            });
        }

        d3.select(this).attr(
          "transform",
          `translate(${x(d.r_price_low)}, ${
            y(d.r_time_low) + y.bandwidth() / 2
          })`
        );

        // Function to update the restaurant count in the header
        function updateRestaurantCount() {
          const count = restaurantInfo.children.length - 1; // Exclude the defaultText element
          box4Header.textContent = `拖移餐廳看更多餐廳資訊 已選${count}間餐廳`;
          compareBtn.textContent = `去比較(${count}/3)`;
        }

        // 換頁
        function updatePagination() {
          const restaurantInfo = document.getElementById("restaurant-info");
          const restaurants =
            restaurantInfo.getElementsByClassName("restaurant");
          const totalRestaurants = restaurants.length;
          const totalPages = Math.ceil(totalRestaurants / restaurantsPerPage);

          // Hide all restaurants initially
          for (let i = 0; i < totalRestaurants; i++) {
            restaurants[i].style.display = "none";
          }

          // Show only the current page's restaurants
          const start = (currentPage - 1) * restaurantsPerPage;
          const end = start + restaurantsPerPage;
          for (let i = start; i < end && i < totalRestaurants; i++) {
            restaurants[i].style.display = "block";
          }

          // Handle arrow buttons visibility
          const leftArrow = document.getElementById("left-arrow");
          const rightArrow = document.getElementById("right-arrow");
          leftArrow.style.display = currentPage > 1 ? "block" : "none";
          rightArrow.style.display =
            currentPage < totalPages ? "block" : "none";

          leftArrow.onclick = () => {
            if (currentPage > 1) {
              currentPage--;
              updatePagination();
            }
          };

          rightArrow.onclick = () => {
            if (currentPage < totalPages) {
              currentPage++;
              updatePagination();
            }
          };
        }
        

        // 當餐廳添加或刪除時，更新按鈕狀態
        function updateCompareButtonState() {
          const compareBtn = document.getElementById("compare-btn");

          if (selectedRestaurantIds.length === 3) {
            // 恰好選擇了三家餐廳，按鈕可用
            compareBtn.style.opacity = "1";
            compareBtn.disabled = false;

            // 設置點擊事件，跳轉到比較頁面
            compareBtn.onclick = function () {
              const url = `http://localhost/my_foodee/compare/0807.php?r_id1=${selectedRestaurantIds[0]}&r_id2=${selectedRestaurantIds[1]}&r_id3=${selectedRestaurantIds[2]}`;
              window.location.href = url;
            };
          } else {
            // 非三家餐廳，按鈕不可用，設置為半透明
            compareBtn.style.opacity = "0.5";
            compareBtn.disabled = true;

            // 禁止點擊事件
            compareBtn.onclick = null;
          }
        }

        // 當添加餐廳時，記錄ID並更新按鈕狀態
        function addRestaurant(restaurantId) {
          if (!selectedRestaurantIds.includes(restaurantId)) {
            selectedRestaurantIds.push(restaurantId);
            updateCompareButtonState();
          }
        }

        // 當刪除餐廳時，移除ID並更新按鈕狀態
        function removeRestaurant(restaurantId) {
          selectedRestaurantIds = selectedRestaurantIds.filter(
            (id) => id !== restaurantId
          );
          updateCompareButtonState();
        }

        // 假設這裡是添加餐廳的代碼，每次添加餐廳時調用 addRestaurant()
        function onAddRestaurant(d) {
          addRestaurant(d.r_id); // 這裡使用餐廳的 ID
        }

        // 假設這裡是刪除餐廳的代碼，每次刪除餐廳時調用 removeRestaurant()
        function onDeleteRestaurant(d) {
          removeRestaurant(d.r_id); // 這裡使用餐廳的 ID
        }
      })
  );
}