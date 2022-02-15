import React from "react";

function CurrencyConverter(props) {
  const { lastPrice } = props;
  // Set the Average Exchange Rate to use for the calculator.
  const avgExchangeRate = lastPrice.closingPrice;

  console.log(avgExchangeRate);

  return <>TODO: RENDER REACT APP TO BROWSER HERE</>;
}

export default CurrencyConverter;
