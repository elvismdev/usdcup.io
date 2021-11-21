import { Controller } from "stimulus";
const { CountUp } = require("countup.js");

// Define an async function
async function fetchAsync(endpoint) {
  let uri = "/";
  uri += endpoint;
  let response = await fetch(uri, {
    method: "GET",
  });
  let data = await response.json();
  return data;
}

// Define countUp function.
function countUp(e, start, end, decimalPlaces) {
  const options = {
    startVal: start,
    decimalPlaces: decimalPlaces,
  };

  var countUp = new CountUp(e, end, options);
  if (!countUp.error) {
    countUp.start();
  } else {
    console.error(countUp.error);
  }
}

export default class extends Controller {
  connect() {
    var $body = document.querySelector("body");
    var $loadingParagraph = document.getElementById("loading-p");

    // Do stuff after page is fully loaded.
    window.addEventListener("load", function () {
      // Play initial animations on page load.
      window.setTimeout(function () {
        $body.classList.remove("is-preload");
      }, 100);

      // Get and display Average Price.
      let avPriceElement = document.getElementById("average-price");
      let adsQtyElement = document.getElementById("ads-qty");
      fetchAsync("api/get_average_price")
        .then(function (data) {
          // Remove loading dots.
          $loadingParagraph.classList.remove("loading-dots");

          // Count up from 0 and display value.
          countUp(avPriceElement, 0.0, data.average_price, 2);

          // Count down from 100.
          countUp(adsQtyElement, 100, data.total_ads_evaluated, 0);
        })
        .catch(function (error) {
          console.log(error);
        });
    });
  }
}
