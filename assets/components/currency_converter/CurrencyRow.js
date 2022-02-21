
import React from "react";

export default function CurrencyRow(props) {
  const {
    currency,
    onChangeAmount,
    amount,
  } = props;
  return (
    <div>
      <input
        type="number"
        className="input"
        value={amount}
        onChange={onChangeAmount}
      />
      <label>{currency}</label>
    </div>
  );
}
