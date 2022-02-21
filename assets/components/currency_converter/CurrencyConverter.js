import React, { useState } from "react";
import CurrencyRow from "./CurrencyRow";

function CurrencyConverter(props) {
  const { lastPrice } = props;
  // Set the Average Exchange Rate to use for the calculator.
  const avgExchangeRate = lastPrice.closingPrice;

  const [amount, setAmount] = useState(1);
  const [amountInFromCurrency, setAmountInFromCurrency] = useState(true);

  let toAmount, fromAmount;
  if (amountInFromCurrency) {
    fromAmount = amount;
    toAmount = amount * avgExchangeRate;
  } else {
    toAmount = amount;
    fromAmount = amount / avgExchangeRate;
  }

  function handleFromAmountChange(e) {
    setAmount(e.target.value);
    setAmountInFromCurrency(true);
  }

  function handleToAmountChange(e) {
    setAmount(e.target.value);
    setAmountInFromCurrency(false);
  }

  return (
    <>
      <h1>Convert</h1>
      <CurrencyRow
        currency="USD"
        onChangeAmount={handleFromAmountChange}
        amount={fromAmount}
      />
      <div className="equals">=</div>
      <CurrencyRow
        currency="CUP"
        onChangeAmount={handleToAmountChange}
        amount={toAmount}
      />
    </>
  );
}

export default CurrencyConverter;
