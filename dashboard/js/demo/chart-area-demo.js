// Set default options for charts
Chart.defaults.font.family = "'Poppins', sans-serif";
Chart.defaults.color = '#6b6458';

// Area Chart
var ctx = document.getElementById("myAreaChart");
if (ctx) {
    var myLineChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Aktivitas',
                lineTension: 0.3,
                backgroundColor: 'rgba(136, 144, 99, 0.1)',
                borderColor: 'rgb(136, 144, 99)',
                pointRadius: 5,
                pointBackgroundColor: 'rgb(53, 64, 36)',
                pointBorderColor: 'rgb(255, 255, 255)',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: 'rgb(76, 61, 25)',
                pointHoverBorderColor: 'rgb(255, 255, 255)',
                pointHoverBorderWidth: 2,
                pointDropRadius: 0,
                pointDataLabels: {
                    align: 'top',
                    anchor: 'end',
                    backgroundColor: 'rgba(75, 192, 192, 1)',
                    borderColor: 'rgb(75, 192, 192)',
                },
                data: earningsData,
            }],
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
                            weight: 'bold',
                            family: "'Poppins', sans-serif"
                        },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#354024'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(76, 61, 25, 0.9)',
                    padding: 12,
                    titleFont: {
                        size: 12,
                        weight: 'bold',
                        family: "'Playfair Display', serif"
                    },
                    bodyFont: {
                        size: 11,
                        family: "'Poppins', sans-serif"
                    },
                    borderColor: '#cfbb99',
                    borderWidth: 1,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        drawBorder: true,
                        color: 'rgba(207, 187, 153, 0.1)',
                        lineWidth: 1
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: "'Poppins', sans-serif"
                        },
                        color: '#6b6458'
                    }
                },
                y: {
                    grid: {
                        display: true,
                        drawBorder: true,
                        color: 'rgba(207, 187, 153, 0.1)',
                        lineWidth: 1
                    },
                    ticks: {
                        font: {
                            size: 11,
                            family: "'Poppins', sans-serif"
                        },
                        color: '#6b6458',
                        beginAtZero: true
                    }
                }
            }
        }
    });
}

// Pie Chart
var pieCtx = document.getElementById("myPieChart");
if (pieCtx) {
    var myPieChart = new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: categoryData.map(item => item.label),
            datasets: [{
                data: categoryData.map(item => item.value),
                backgroundColor: [
                    'rgb(53, 64, 36)',      // Dark Green
                    'rgb(136, 144, 99)',    // Muted Green
                    'rgb(207, 187, 153)',   // Tan
                    'rgb(76, 61, 25)',      // Brown
                    'rgb(101, 89, 52)',     // Dark Brown
                    'rgb(169, 153, 122)',   // Light Tan
                    'rgb(142, 133, 110)'    // Medium Brown
                ],
                borderColor: [
                    'rgb(255, 255, 255)',
                    'rgb(255, 255, 255)',
                    'rgb(255, 255, 255)',
                    'rgb(255, 255, 255)',
                    'rgb(255, 255, 255)',
                    'rgb(255, 255, 255)',
                    'rgb(255, 255, 255)'
                ],
                borderWidth: 2,
                borderRadius: 5,
                hoverBorderColor: 'rgba(76, 61, 25, 0.8)',
                hoverBorderWidth: 3,
                hoverOffset: 10
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12,
                            weight: '500',
                            family: "'Poppins', sans-serif"
                        },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        color: '#354024',
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    return {
                                        text: label + ': ' + value,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor[i],
                                        lineWidth: data.datasets[0].borderWidth,
                                        hidden: false,
                                        index: i
                                    };
                                });
                            }
                            return [];
                        }
                    },
                    onClick: null
                },
                tooltip: {
                    enabled: true,
                    mode: 'index',
                    backgroundColor: 'rgba(76, 61, 25, 0.9)',
                    padding: 12,
                    titleFont: {
                        size: 12,
                        weight: 'bold',
                        family: "'Playfair Display', serif"
                    },
                    bodyFont: {
                        size: 11,
                        family: "'Poppins', sans-serif"
                    },
                    borderColor: '#cfbb99',
                    borderWidth: 1,
                    displayColors: true,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed;
                        }
                    }
                }
            }
        }
    });
}