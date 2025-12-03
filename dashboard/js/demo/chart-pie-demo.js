// Pie Chart Example
const ctxPie = document.getElementById("myPieChart");
if (ctxPie) {
  // 7 warna biru sesuai theme - pastikan urutan konsisten
  const chartColors = [
    { bg: "rgba(1, 134, 171, 0.85)", hover: "rgba(1, 134, 171, 1)", label: "Teal-Blue" }, // Wisata
    { bg: "rgba(2, 69, 122, 0.85)", hover: "rgba(2, 69, 122, 1)", label: "Navy-Blue" }, // Komentar
    { bg: "rgba(0, 27, 72, 0.85)", hover: "rgba(0, 27, 72, 1)", label: "Dark-Blue" }, // Pesan
    { bg: "rgba(126, 200, 217, 0.85)", hover: "rgba(126, 200, 217, 1)", label: "Light-Blue" }, // User
    { bg: "rgba(214, 232, 238, 0.85)", hover: "rgba(214, 232, 238, 1)", label: "Pale-Blue" }, // Produk
    { bg: "rgba(231, 243, 255, 0.85)", hover: "rgba(231, 243, 255, 1)", label: "Very-Light-Blue" }, // Informasi
    { bg: "rgba(74, 143, 173, 0.85)", hover: "rgba(74, 143, 173, 1)", label: "Medium-Blue" }, // Galeri
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
            color: "#001b48",
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
          backgroundColor: "rgba(0, 27, 72, 0.95)",
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
          borderColor: "#0186ab",
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
