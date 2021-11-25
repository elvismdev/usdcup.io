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
    const amountChange = this.element.dataset.amountChange;
    const percentChange = this.element.dataset.percentChange;

    var $body = document.querySelector("body");
    var $loadingParagraph = document.getElementById("loading-p");

    // Set collapse effect for price difference display.
    const collapse = document.querySelector(".collapse");
    const wrapper = document.querySelector(".collapse-wrapper");
    const content = document.querySelector(".collapse-content");
    let open = false;

    collapse.style.minHeight = `${content.getBoundingClientRect().height}px`;

    // Set initial height to content height, if shown by default
    if (!open) {
      wrapper.style.height = "0px";
    }

    function toggleOpen() {
      if (!open) {
        const height = content.getBoundingClientRect().height;
        wrapper.style.height = `${height}px`;
        open = true;
      } else {
        wrapper.style.height = "0px";
        open = false;
      }
    }

    // Do stuff after page is fully loaded.
    window.addEventListener("load", function () {
      // Play initial animations on page load.
      window.setTimeout(function () {
        $body.classList.remove("is-preload");
      }, 100);

      // Get and display Average Price.
      let avPriceElement = document.getElementById("average-price");
      let adsQtyElement = document.getElementById("ads-qty");
      let amountChangeElement = document.getElementById("amount-change");
      let percentChangeElement = document.getElementById("percent-change");

      window.setTimeout(function () {
        // Remove loading dots.
        $loadingParagraph.classList.remove("loading-dots");

        // Count up from 0 and display value.
        countUp(avPriceElement, 0.0, averagePrice, 2);

        // Count down from 100.
        countUp(adsQtyElement, 100, totalAds, 0);

        // Count up price difference values.
        toggleOpen();
        countUp(amountChangeElement, 0.0, amountChange, 2);
        countUp(percentChangeElement, 0.0, percentChange, 2);
      }, Math.floor(Math.random() * 1000) + 1000);
    });
  }
}
