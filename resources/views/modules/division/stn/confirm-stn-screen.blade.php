@extends('layouts.master')
@section('content-title')
Confirmation Screen
@endsection
@section('content-body')
<div class="col-md-8">
    <div class="card">
        <div class="card-header">
            <h5>Confirm STN</h5>
        </div>
        <div class="card-body ">
            <form role="form" id="requestForm">
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                            <label for="transporter" class="control-label">Transporter</label>
                            <select name="transporter" id="transporter" class="form-control">
                                <option value="">--Select--</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label for="truck_no" class="control-label">Truck No</label>
                            <input type="text" name="truck_no" id="truck_no" class="form-control" />
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label for="vat_challan" class="control-label">Vat Challan</label>
                            <input type="text" name="vat_challan" id="vat_challan" class="form-control"
                                value="<?php  ?>" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="table-responsive">
                            <table id="myTable" width="100%" class="table table-bordered" border="0" cellspacing="0"
                                cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Description</th>
                                        <th>Qty Requested (PCs)</th>
                                        <th>Lot</th>
                                        <th>Shipped Qty (PCs)</th>

                                    </tr>
                                </thead>
                                <tbody>
                                    
                                    @foreach ($postData['id'] as $idx => $ind_id)
                                    <tr>
                                        <td>{{$postData['item'][$idx]}}</td>
                                        <td>{{$postData['item'][$idx]}}</td>
                                        <td>{{$postData['qty_req'][$idx]}}</td>
                                        <td>{{$postData['lot'][$idx]}}</td>
                                        <td>{{$postData['qty_ship'][$idx]}}</td>
                                    </tr>
                                    @endforeach
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </form>
        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-danger btn-block" onclick="window.history.back();">
                        <i class="fa fa-angle-double-left"></i>Back
                    </button>
                </div>

                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-success btn-block subbtn" name="subbtn">
                        <i class="fa fa-ship"></i>Confirm & Save
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection