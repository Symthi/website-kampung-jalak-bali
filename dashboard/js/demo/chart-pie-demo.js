// Pie Chart Example
const ctxPie = document.getElementById("myPieChart");
if (ctxPie) {
  // 7 warna unik sesuai theme - pastikan urutan konsisten
  const chartColors = [
    { bg: "rgba(76, 61, 25, 0.85)", hover: "rgba(76, 61, 25, 1)", label: "Brown" }, // Wisata
    { bg: "rgba(53, 64, 36, 0.85)", hover: "rgba(53, 64, 36, 1)", label: "Dark Green" }, // Komentar
    { bg: "rgba(207, 187, 153, 0.85)", hover: "rgba(207, 187, 153, 1)", label: "Tan" }, // Pesan
    { bg: "rgba(229, 215, 196, 0.85)", hover: "rgba(229, 215, 196, 1)", label: "Cream" }, // User
    { bg: "rgba(212, 165, 116, 0.85)", hover: "rgba(212, 165, 116, 1)", label: "Med Tan" }, // Produk
    { bg: "rgba(139, 119, 101, 0.85)", hover: "rgba(139, 119, 101, 1)", label: "Dark Tan" }, // Informasi
    { bg: "rgba(101, 84, 66, 0.85)", hover: "rgba(101, 84, 66, 1)", label: "D Brown" }, // Galeri
  ];

  let pieLabels = [];
  let pieDataValues = [];
  let pieColors = [];
  let pieHoverColors = [];

  // Pastikan categoryData adalah array dan valid
  if (Array.isArray(categoryData) && categoryData.length > 0) {
    categoryData.forEach((item, index) => {
      // Validasi data
      if (item && item.label && typeof item.value !== "undefined") {
        pieLabels.push(item.label);
        pieDataValues.push(parseInt(item.value) || 0);
        pieColors.push(chartColors[index % 7].bg);
        pieHoverColors.push(chartColors[index % 7].hover);
      }
    });
  }

  // Jika tidak ada data valid
  if (pieLabels.length === 0) {
    pieLabels = ["Tidak ada data"];
    pieDataValues = [1];
    pieColors = ["rgba(200, 200, 200, 0.8)"];
    pieHoverColors = ["rgba(200, 200, 200, 1)"];
  }

  const myPieChart = new Chart(ctxPie, {
    type: "doughnut",
    data: {
      labels: pieLabels,
      datasets: [
        {
          data: pieDataValues,
          backgroundColor: pieColors,
          hoverBackgroundColor: pieHoverColors,
          borderColor: "#ffffff",
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      aspectRatio: 1.2,
      layout: {
        padding: {
          top: 10,
          bottom: 10,
          left: 10,
          right: 10,
        },
      },
      plugins: {
        legend: {
          display: true,
          position: "bottom",
          maxHeight: 80,
          labels: {
            font: {
              family: "'Poppins', sans-serif",
              size: 11,
              weight: "500",
            },
            padding: 8,
            usePointStyle: true,
            pointStyle: "circle",
            color: "#2d2a23",
            boxWidth: 8,
            generateLabels: function (chart) {
              const data = chart.data;
              if (data.labels.length && data.datasets.length) {
                return data.labels.map((label, i) => ({
                  text: label,
                  fillStyle: data.datasets[0].backgroundColor[i],
                  hidden: false,
                  index: i,
                }));
              }
              return [];
            },
          },
        },
        tooltip: {
          backgroundColor: "rgba(76, 61, 25, 0.95)",
          titleFont: {
            family: "'Playfair Display', serif",
            size: 14,
            weight: "700",
          },
          bodyFont: {
            family: "'Poppins', sans-serif",
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
              if (context && context.label && typeof context.parsed !== "undefined") {
                return context.label + ": " + context.parsed + " item";
              }
              return "";
            },
          },
        },
      },
    },
  });
}
