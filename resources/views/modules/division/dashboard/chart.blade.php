<html>

<head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        var stock_data = <?php echo $stock; ?>;
        console.log(stock_data);
        google.charts.load('current', {
            'packages': ['corechart']
        });
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = google.visualization.arrayToDataTable(stock_data);
            var options = {
                title: 'Stock Value By Site',
                isStacked: true,
                tooltip: {
                    trigger: 'focus',
                    isHtml: true
                },
                legend: {
                    position: 'top',
                    maxLines: 3
                },
                vAxis: {
                    minValue: 0,
                    format: 'short'
                },
                hAxis: {
                    textStyle: {
                        fontSize: 10
                    },
                    slantedTextAngle: 30
                },
                bar: {
                    groupWidth: '45%'
                },
                chartArea: {
                    width: '80%'
                }
            };
            var chart = new google.visualization.ColumnChart(document.getElementById('linechart'));
            chart.draw(data, options);

            google.charts.setOnLoadCallback(drawStockChart);

            $(window).resize(function() {
                drawStockChart();
            });
        }
    </script>
</head>

<body>
    <div id="linechart" style="width: 900px; height: 500px"></div>
</body>

</html>