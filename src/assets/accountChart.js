function initializeChart() {
    const url = window.location.href
    const id = url.substring(url.lastIndexOf('/') + 1)

    fetch(`/accounts/${id}/chart`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    }).then(data => {
        const canvas = document.getElementsByTagName('canvas');
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Montant',
                        data: data.values,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                        title: {
                        display: true,
                        text: 'Balance History'
                    }
                }
            }
        });
    }).catch(error => {
        console.error('Error fetching chart data:', error);
    });
}

initializeChart();
