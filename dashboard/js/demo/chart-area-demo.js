// Set default options for charts
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.color = "#001b48";

// Area Chart
var ctx = document.getElementById("myAreaChart");
if (ctx) {
  var myLineChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
      datasets: [
        {
          label: "Aktivitas",
          lineTension: 0.3,
          backgroundColor: "rgba(1, 134, 171, 0.15)",
          borderColor: "#0186ab",
          pointRadius: 5,
          pointBackgroundColor: "#001b48",
          pointBorderColor: "#ffffff",
          pointBorderWidth: 2,
          pointHoverRadius: 7,
          pointHoverBackgroundColor: "#02457a",
          pointHoverBorderColor: "#ffffff",
          pointHoverBorderWidth: 2,
          pointDropRadius: 0,
          pointDataLabels: {
            align: "top",
            anchor: "end",
            backgroundColor: "rgba(1, 134, 171, 1)",
            borderColor: "#0186ab",
          },
          data: earningsData,
        },
      ],
    },
    options: {
      maintainAspectRatio: false,
      responsive: true,
      plugins: {
        legend: {
          display: true,
          labels: {
            font: {
              size: 12,
              weight: "bold",
              family: "'Poppins', sans-serif",
            },
            padding: 15,
            usePointStyle: true,
            pointStyle: "circle",
            color: "#001b48",
          },
        },
        tooltip: {
          mode: "index",
          intersect: false,
          backgroundColor: "rgba(0, 27, 72, 0.95)",
          padding: 12,
          titleFont: {
            size: 12,
            weight: "bold",
            family: "'Playfair Display', serif",
          },
          bodyFont: {
            size: 11,
            family: "'Poppins', sans-serif",
          },
          borderColor: "#0186ab",
          borderWidth: 1,
          displayColors: true,
          callbacks: {
            label: function (context) {
              return context.dataset.label + ": " + context.parsed.y;
            },
          },
        },
      },
      scales: {
        x: {
          grid: {
            display: true,
            drawBorder: true,
            color: "rgba(214, 232, 238, 0.1)",
            lineWidth: 1,
          },
          ticks: {
            font: {
              size: 11,
              family: "'Poppins', sans-serif",
            },
            color: "#001b48",
          },
        },
        y: {
          grid: {
            display: true,
            drawBorder: true,
            color: "rgba(214, 232, 238, 0.1)",
            lineWidth: 1,
          },
          ticks: {
            font: {
              size: 11,
              family: "'Poppins', sans-serif",
            },
            color: "#001b48",
            beginAtZero: true,
          },
        },
      },
    },
  });
}
