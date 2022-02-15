import { Controller } from "stimulus";
import ReactDOM from "react-dom";
import React from "react";
import CurrencyConverter from "../components/currency_converter/CurrencyConverter";

export default class extends Controller {
  static values = {
    lastPrice: Object,
  };

  connect() {
    ReactDOM.render(
      <CurrencyConverter lastPrice={this.lastPriceValue} />,
      this.element
    );
  }
}
