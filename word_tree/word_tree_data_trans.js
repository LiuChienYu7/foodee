fetch(jsonUrl)
  .then(response => response.json())
  .then(rawData => {
    // 初始化資料結構
    const data = {
      name: "評論",
      children: [
        {
          name: "食物",
          children: [
            {
              name: restaurant.food_comment_sum,
              children: [
                { name: restaurant.food_review1 },
                { name: restaurant.food_review2 },
                { name: restaurant.food_review3 }
              ]
            }
          ]
        },
        {
          name: "服務",
          children: [
            {
              name: restaurant.service_comment_sum,
              children: [
                { name: restaurant.service_review1 },
                { name: restaurant.service_review2 },
                { name: restaurant.service_review3 }
              ]
            }
          ]
        },
        {
          name: "划算",
          children: [
            {
              name: restaurant.value_comment_sum,
              children: [
                { name: restaurant.value_review1 },
                { name: restaurant.value_review2 },
                { name: restaurant.value_review3 }
              ]
            }
          ]
        },
        {
          name: "環境",
          children: [
            {
              name: restaurant.atmosphere_comment_sum,
              children: [
                { name: restaurant.atmosphere_review1 },
                { name: restaurant.atmosphere_review2 },
                { name: restaurant.atmosphere_review3 }
              ]
            }
          ]
        }
      ]
    };

    // 呼叫 cluster 方法進行可視化
    renderTree(data);
  })
  .catch(error => console.error('Error loading the data:', error));