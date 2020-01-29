<?php

namespace ITLLiveWeb\Http\Controllers\Division;

use Illuminate\Http\Request;
use ITLLiveWeb\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SalesReportsController extends Controller
{
    public function salesbySKUbydate(Request $request)
    {   
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');

        $sales = DB::table('sod_det')
            ->select(
                DB::raw('sod_nbr, sod_date, sod_depot, sod_cust, sod_line, sod_type, sod_lot, sod_netprice, sod_qty as total_qty, sod_amount as total_amount, 
                        (pt_mstr.pt_um_conv * sod_det.sod_qty) as total_kg, sod_part, pt_desc, pt_prod_line ')
            )
            ->leftJoin('pt_mstr', 'sod_det.sod_part', '=', 'pt_mstr.pt_part')
            ->leftJoin('si_mstr', 'sod_det.sod_depot', '=', 'si_mstr.si_site')
            ->where('si_division', '=', Auth::user()->user_name)
            ->where(function ($query) use ($startDate, $endDate) {
                if ($endDate !="" && $startDate !="") {
                    $query->whereBetween('sod_date', [$startDate, $endDate]);
                }
                return $query;
            })
            ->orderBy('sod_created', 'desc')
            ->orderBy('sod_line', 'asc')
            ->orderBy('sod_depot', 'desc')
            //->toSql();
            ->get();
        //dd($sales);

        return view('modules.division.reports.sales.salesbySKUbydate')->with('sales',$sales);
    }
}
