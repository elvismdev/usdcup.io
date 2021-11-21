const Highcharts = require("highcharts/highstock"); // or require('highcharts/highstock');
const moment = require("moment");
// Get locale from document and set it to moment().
moment.locale(document.documentElement.lang);
// Capitalize only the first letter of the string.
function capitalizeFirstLetter(array) {
  return array.map((a) => a.charAt(0).toUpperCase() + a.substr(1));
}
// Set translations.
Highcharts.setOptions({
  lang: {
    months: capitalizeFirstLetter(moment.months()),
    weekdays: capitalizeFirstLetter(moment.weekdays()),
    shortMonths: capitalizeFirstLetter(moment.monthsShort()),
  },
});
// Set theme.
Highcharts.theme = {
  colors: [
    "#ffd300",
    "#90ee7e",
    "#f45b5b",
    "#7798BF",
    "#aaeeee",
    "#ff0066",
    "#eeaaee",
    "#55BF3B",
    "#DF5353",
    "#7798BF",
    "#aaeeee",
  ],
  chart: {
    backgroundColor: {
      linearGradient: { x1: 0, y1: 0, x2: 1, y2: 1 },
      stops: [
        [0, "#000"],
        [1, "#000"],
      ],
    },
    style: {
      fontFamily: "'Roboto', sans-serif",
    },
    plotBorderColor: "#606063",
  },
  title: {
    style: {
      color: "#E0E0E3",
      textTransform: "uppercase",
      fontSize: "30px",
    },
  },
  subtitle: {
    style: {
      color: "#E0E0E3",
      textTransform: "uppercase",
    },
  },
  xAxis: {
    gridLineColor: "#707073",
    labels: {
      style: {
        color: "#E0E0E3",
        fontSize: "20px",
      },
    },
    lineColor: "#707073",
    minorGridLineColor: "#505053",
    tickColor: "#707073",
    title: {
      style: {
        color: "#A0A0A3",
      },
    },
  },
  yAxis: {
    gridLineColor: "#707073",
    labels: {
      style: {
        color: "#E0E0E3",
        fontSize: "20px",
      },
    },
    lineColor: "#707073",
    minorGridLineColor: "#505053",
    tickColor: "#707073",
    tickWidth: 1,
    title: {
      style: {
        color: "#A0A0A3",
        fontSize: "18px",
      },
    },
  },
  tooltip: {
    backgroundColor: "rgba(0, 0, 0, 0.85)",
    style: {
      color: "#F0F0F0",
      fontSize: "18px",
    },
  },
  plotOptions: {
    series: {
      dataLabels: {
        color: "#F0F0F3",
        style: {
          fontSize: "13px",
        },
      },
      marker: {
        lineColor: "#333",
      },
    },
    boxplot: {
      fillColor: "#505053",
    },
    candlestick: {
      lineColor: "white",
    },
    errorbar: {
      color: "white",
    },
  },
  legend: {
    backgroundColor: "rgba(0, 0, 0, 0.5)",
    itemStyle: {
      color: "#E0E0E3",
    },
    itemHoverStyle: {
      color: "#FFF",
    },
    itemHiddenStyle: {
      color: "#606063",
    },
    title: {
      style: {
        color: "#C0C0C0",
      },
    },
  },
  credits: {
    style: {
      color: "#666",
    },
  },
  labels: {
    style: {
      color: "#707073",
    },
  },
  drilldown: {
    activeAxisLabelStyle: {
      color: "#F0F0F3",
    },
    activeDataLabelStyle: {
      color: "#F0F0F3",
    },
  },
  navigation: {
    buttonOptions: {
      symbolStroke: "#DDDDDD",
      theme: {
        fill: "#505053",
      },
    },
  },
  // scroll charts
  rangeSelector: {
    buttonTheme: {
      fill: "#505053",
      stroke: "#000000",
      style: {
        color: "#CCC",
        fontSize: "16px",
      },
      states: {
        hover: {
          fill: "#707073",
          stroke: "#000000",
          style: {
            color: "white",
            fontSize: "16px",
          },
        },
        select: {
          fill: "#000003",
          stroke: "#000000",
          style: {
            color: "white",
            fontSize: "16px",
          },
        },
      },
    },
    inputBoxBorderColor: "#505053",
    inputStyle: {
      backgroundColor: "#333",
      color: "silver",
      fontSize: "16px",
    },
    labelStyle: {
      color: "silver",
    },
  },
  navigator: {
    handles: {
      backgroundColor: "#666",
      borderColor: "#AAA",
    },
    outlineColor: "#CCC",
    maskFill: "rgba(255,255,0,0.1)",
    series: {
      color: "#7798BF",
      lineColor: "#A6C7ED",
    },
    xAxis: {
      gridLineColor: "#505053",
    },
  },
  scrollbar: {
    barBackgroundColor: "#808083",
    barBorderColor: "#808083",
    buttonArrowColor: "#CCC",
    buttonBackgroundColor: "#606063",
    buttonBorderColor: "#606063",
    rifleColor: "#FFF",
    trackBackgroundColor: "#404043",
    trackBorderColor: "#404043",
  },
};
Highcharts.setOptions(Highcharts.theme);
window.Highcharts = Highcharts;
