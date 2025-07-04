<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Gráficos</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        .chart-container {
            width: 400px;
            height: 300px;
            margin: 20px;
            padding: 20px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <h1>Teste de Gráficos</h1>
    
    <div class="chart-container">
        <h3>Teste 1 - Gráfico de Barras</h3>
        <canvas id="teste1"></canvas>
    </div>
    
    <div class="chart-container">
        <h3>Teste 2 - Gráfico de Pizza</h3>
        <canvas id="teste2"></canvas>
    </div>
    
    <script>
        console.log('Chart.js carregado:', typeof Chart);
        
        // Teste 1 - Gráfico de Barras
        const ctx1 = document.getElementById('teste1');
        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: ['Janeiro', 'Fevereiro', 'Março'],
                datasets: [{
                    label: 'Vendas',
                    data: [12, 19, 3],
                    backgroundColor: '#8B5CF6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
        
        // Teste 2 - Gráfico de Pizza
        const ctx2 = document.getElementById('teste2');
        new Chart(ctx2, {
            type: 'pie',
            data: {
                labels: ['Vermelho', 'Azul', 'Verde'],
                datasets: [{
                    data: [300, 50, 100],
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
