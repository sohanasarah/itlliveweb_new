@extends('layouts.master')

@section('content-title')
Dashboard
@endsection

@section('content-body')
    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <!-- Small boxes (Stat box) -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-info">
                        <div class="inner">
                            <span style="font-size: 18px; ">Sales Yesterday</span>
                            <p class="panel-heading">
                                <span class="title">
                                    {{ number_format($ystrday_data['sales']['amount'],2) }}
                                </span>
                                tk.
                            </p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-ios-cart"></i>
                        </div>
                        <a href="#" class="small-box-footer">Details <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-success">
                        <div class="inner">
                            <span style="font-size: 18px; ">Collection Yesterday</span>
                            <p class="panel-heading">
                                <span class="title">
                                    {{ number_format($ystrday_data['coll']['amount'],2) }}
                                </span>
                                tk.
                            </p>
                        </div>
                        <div class="icon">
                            <i class="ion ion-cash"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <span style="font-size: 18px; ">Remittance Yesterday</span>
                            <p class="panel-heading">
                                <span class="title">
                                    {{ number_format($ystrday_data['rem']['amount'],2) }}
                                </span>
                                tk.
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
                <div class="col-lg-3 col-6">
                    <!-- small box -->
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <span style="font-size: 18px; ">Expenses Yesterday</span>
                            <p class="panel-heading">
                                <span class="title">
                                    {{ number_format($ystrday_data['exp']['amount'],2) }}
                                </span>
                                tk.
                            </p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                    </div>
                </div>
                <!-- ./col -->
            </div>
            <!-- /.row -->
            <!-- main row-->

            <div class="row">
                <div class="col-sm-12 col-lg-6">
                    <div class="card" style="height: 500px;">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Stock</h3>
                                <a href="javascript:void(0);">View Report</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">12,0000</span>
                                    <span>total stock
                                    </span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-success">
                                        <i class="fas fa-arrow-up"></i> 12.5%
                                    </span>
                                    <span class="text-muted">Since last week</span>
                                </p>
                            </div>
                            <!-- /.d-flex -->

                            <div>
                                <div id="stock_chart" style="height: 350px;width: 100%;">
                                </div>
                            </div>

                            <!-- <div class="d-flex flex-row justify-content-end">
                                <span class="mr-2">
                                    <i class="fas fa-square text-primary"></i> This Week
                                </span>

                                <span>
                                    <i class="fas fa-square text-gray"></i> Last Week
                                </span>
                            </div> -->
                        </div>
                    </div>
                    <!-- /.card -->

                    <div class="card" style="height: 500px;">
                        <div class="card-header border-0">
                            <h3 class="card-title">Top Products</h3>
                            <div class="card-tools">
                                <a href="#" class="btn btn-tool btn-sm">
                                    <i class="fas fa-bars"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div id="product_chart" class="chart">
                                    {{-- {{ $products }} --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col-md-6 -->
                <div class="col-sm-12 col-lg-6">
                    <div class="card" style="height: 500px;">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between">
                                <h3 class="card-title">Sales </h3>
                                <a href="javascript:void(0);">View Report</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">$18,230.00</span>
                                    <span>Sales Over Time</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-success">
                                        <i class="fas fa-arrow-up"></i> 33.1%
                                    </span>
                                    <span class="text-muted">Since last month</span>
                                </p>
                            </div>
                            <!-- /.d-flex -->
                            <div class="mb-4">
                                <div id="sales_chart" style="height: 400px;width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card -->

                    <div class="card">
                        <div class="card-header border-0">
                            <h3 class="card-title">Cash-in-hand vs Outstanding vs Sales</h3>
                            <div class="card-tools">
                                <a href="#" class="btn btn-sm btn-tool">
                                    <i class="fas fa-bars"></i>
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="myTable" class="table table-striped table-valign-middle table-content">
                                    <thead>
                                        <tr>
                                            <th width="30%">Depot </th>
                                            <th width="20%">Cash In Hand</th>
                                            <th width="20%">Outstanding</th>
                                            <th width="30%">Sales This Month</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cih_ar as $value)
                                        @php
                                        $site_details = Helper::get_site_desc($value->depot);
                                        // var_dump($site_details->si_desc);
                                        @endphp
                                        <tr>
                                            <td>
                                                {{$site_details->si_desc}}
                                            </td>
                                            <td>
                                                {{number_format($value->closing_cih,2)}}
                                            </td>
                                            <td>
                                                {{number_format($value->closing_ar,2)}}
                                            </td>
                                            <td>
                                                {{number_format($value->monthly_sales,2)}}
                                                {{-- <small class="text-success mr-1">
                                                    <i class="fas fa-arrow-up"></i>
                                                    12%
                                                </small> --}}
                                            </td>
                                        </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection

@section('page-script')
<script type="text/javascript">
    $(document).ready(function () {
    var url = window.location;
    //alert(url);
    // for sidebar menu but not for treeview submenu
    $('ul.sidebar-menu a').filter(function() {
        return this.href == url;
    }).parent().addClass('active');
    // for treeview which is like a submenu
    $('ul.treeview-menu a').filter(function() {
        return this.href == url;
    }).parentsUntil(".sidebar-menu > .treeview-menu").addClass('active');

    $('#myTable').DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": false,
        "ordering": true,
        "lengthMenu": [
            [5, 10, 30, 50, -1],
            [5, 10, 30, 50, "All"]
        ],
        "pageLength": 5,
    });
})
</script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    var stock_data = '';
    var sales_data = '';
    stock_data = {!!$stock!!};
    sales_data = {!!$sales!!};
    product_data = {!!$products!!};

    google.charts.load('current', {
        'packages': ['bar']
    });
    google.charts.setOnLoadCallback(drawStockChart);
    google.charts.setOnLoadCallback(drawSalesChart);
    google.charts.setOnLoadCallback(drawProdChart);

    function drawStockChart() {
        var data = new google.visualization.arrayToDataTable(stock_data);

        //var yMin;
        var yMax;
        var columnRange = data.getColumnRange(1);
        if ((columnRange.max - columnRange.min) < 100) { 
                //yMin=((columnRange.max + columnRange.min) / 2) - 50;
                yMax=((columnRange.max + columnRange.min) / 2) + 50; 
        } else { 
                //yMin=columnRange.min; 
                yMax=columnRange.max; 
        }

        var options = {
            height: 300,
            width: "100%",
            chart: {
                //subtitle: 'Tea, Food and Agro Stock Value As On Today',
            },
            bars: 'vertical',
            isStacked: 'true',
            bar: {
                groupWidth: 75  // Set the width for each bar
            },
            vAxis: {
                minValue: 0,
                format: 'short',
                viewWindow: {
                        min: 0,
                        max: yMax
                },
                gridlines: {
                    color: 'transparent'
                }
            },
            colors: ['#1b9e77', '#d95f02', '#7570b3']
            
        };

        var chart = new google.charts.Bar(document.getElementById('stock_chart'));
        chart.draw(data, google.charts.Bar.convertOptions(options));

        $(window).resize(function() {
            drawStockChart();
        });
    }

    function drawSalesChart() {
        var data = google.visualization.arrayToDataTable(sales_data);
        //var yMin;
        var yMax;
        var columnRange = data.getColumnRange(1);
        if ((columnRange.max - columnRange.min) < 100) { 
                //yMin=((columnRange.max + columnRange.min) / 2) - 50;
                yMax=((columnRange.max + columnRange.min) / 2) + 50; 
        } else { 
                //yMin=columnRange.min; 
                yMax=columnRange.max; 
        }
        var options = {
            height: 300,
            width: "100%",
            legend: {
                position: 'none'
            },
            chart: {
                title: 'Sales By Depot ',
                subtitle: 'This Month',
            },
            pointsVisible: true,
            bars: 'vertical',
            vAxis: {
                minValue: 0,
                title: 'Sales Amount',
                format: 'short',
                viewWindow: {
                        min: 0,
                        max: yMax
                },
                gridlines: {
                    color: 'transparent'
                }
            },
            hAxis: {
                textStyle: {
                    fontSize: 10
                },
                slantedTextAngle: 30
            },
            chartArea: {
                width: '80%'
            },
            bar: {
                groupWidth: '75%'
            }
        };

        var chart = new google.charts.Bar(document.getElementById('sales_chart'));

        chart.draw(data, google.charts.Bar.convertOptions(options));

        $(window).resize(function() {
            drawSalesChart();
        });
    }

    function drawProdChart() {
        var data = google.visualization.arrayToDataTable(product_data);
        //var yMin;
        var yMax;
        var columnRange = data.getColumnRange(1);
        if ((columnRange.max - columnRange.min) < 100) { 
                //yMin=((columnRange.max + columnRange.min) / 2) - 50;
                yMax=((columnRange.max + columnRange.min) / 2) + 50; 
        } else { 
                //yMin=columnRange.min; 
                yMax=columnRange.max; 
        }
        var options = {
            height: 400,
            width: "100%",
            legend: {
                position: 'none'
            },
            chart: {
                subtitle: 'Top 10 Products for current month',
            },
            bars: 'horizontal', // Required for Material Bar Charts.
            colors: ['#7570b3', '#d95f02'],
            hAxis: {
                title: 'Qty in KG',
                position: 'top',
                format: 'short',
                viewWindow: {
                        min: 0,
                        max: yMax
                },
                gridlines: {
                    color: 'transparent'
                }
            },
            vAxis: {
                title: 'Item Name', 
                textStyle: {
                    color: 'grey',
                    fontSize: '15',
                },
                titleTextStyle: { 
                    color: 'black',
                    fontSize: '12',
                }
            },
            bar: { groupWidth: "75%" },
           
        };
        
        var chart = new google.charts.Bar(document.getElementById('product_chart'));
        
        chart.draw(data, google.charts.Bar.convertOptions(options));
    };
</script>

@endsection