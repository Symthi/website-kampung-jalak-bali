// Set new default font family and font color to match theme
Chart.defaults.font.family = '"Poppins", sans-serif';
Chart.defaults.color = "#6b6458";

// Area Chart Example
const ctx = document.getElementById("myAreaChart");
if (ctx) {
  const myLineChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Ags", "Sep", "Okt", "Nov", "Des"],
      datasets: [
        {
          label: isAdminUser ? "Ikhtisar Aktivitas" : "Aktivitas Saya",
          lineTension: 0.3,
          backgroundColor: "rgba(76, 61, 25, 0.05)",
          borderColor: "rgba(76, 61, 25, 1)",
          pointRadius: 3,
          pointBackgroundColor: "rgba(76, 61, 25, 1)",
          pointBorderColor: "rgba(76, 61, 25, 1)",
          pointHoverRadius: 3,
          pointHoverBackgroundColor: "rgba(76, 61, 25, 1)",
          pointHoverBorderColor: "rgba(76, 61, 25, 1)",
          pointHitRadius: 10,
          pointBorderWidth: 2,
          data: earningsData,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 2,
      layout: {
        padding: {
          top: 5,
          bottom: 5,
          left: 0,
          right: 0,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            min: 0,
            max: Math.max(...earningsData, 10) + 5,
            maxTicksLimit: 5,
            stepSize: Math.ceil((Math.max(...earningsData, 10) + 5) / 5),
            color: "#6b6458",
            font: {
              family: '"Poppins", sans-serif',
              size: 12,
              weight: "500",
            },
            padding: 10,
          },
          grid: {
            color: "rgba(207, 187, 153, 0.2)",
            drawBorder: false,
            drawTicks: false,
          },
        },
        x: {
          ticks: {
            maxRotation: 0,
            maxTicksLimit: 7,
            color: "#6b6458",
            font: {
              family: '"Poppins", sans-serif',
              size: 12,
              weight: "500",
            },
            padding: 10,
          },
          grid: {
            color: "rgba(207, 187, 153, 0.1)",
            drawBorder: false,
            drawTicks: false,
          },
        },
      },
      plugins: {
        legend: {
          display: true,
          labels: {
            font: {
              family: '"Poppins", sans-serif',
              size: 13,
              weight: "600",
            },
            color: "#2d2a23",
            padding: 15,
            usePointStyle: true,
          },
        },
        tooltip: {
          backgroundColor: "rgba(76, 61, 25, 0.95)",
          titleFont: {
            family: '"Playfair Display", serif',
            size: 14,
            weight: "700",
          },
          bodyFont: {
            family: '"Poppins", sans-serif',
            size: 12,
          },
          titleColor: "#ffffff",
          bodyColor: "#ffffff",
          borderColor: "#cfbb99",
          borderWidth: 1,
          padding: 12,
          displayColors: true,
          xPadding: 15,
          yPadding: 15,
          caretPadding: 10,
          callbacks: {
            label: function (context) {
              return context.dataset.label + ": " + context.parsed.y + " aktivitas";
            },
          },
        },
      },
    },
  });
}
