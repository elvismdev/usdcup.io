import { Controller } from "stimulus";
const { CountUp } = require("countup.js");

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
    // Get values from backend.
    const averagePrice = this.element.dataset.averagePrice;
    const totalAds = this.element.dataset.totalAds;

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

      window.setTimeout(function () {
        // Remove loading dots.
        $loadingParagraph.classList.remove("loading-dots");

        // Count up from 0 and display value.
        countUp(avPriceElement, 0.0, averagePrice, 2);

        // Count down from 100.
        countUp(adsQtyElement, 100, totalAds, 0);
      }, Math.floor(Math.random() * 1000) + 1000);
    });
  }
}
