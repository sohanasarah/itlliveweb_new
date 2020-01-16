<?php

namespace App\Http\Controllers\Division;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function dashboard()
    {
        $ystrday_data = $this->data_ystrday();
        $sales_data = $this->monthly_sales();
        $stock_data = $this->depot_stock();
        $product_data = $this->top_products();
        $cih_ar = $this->depot_cih_ar();
   
        return view('modules.division.dashboard.dashboard')
            ->with('ystrday_data', $ystrday_data)
            ->with('stock', $stock_data)
            ->with('sales', $sales_data)
            ->with('products', $product_data)
            ->with('cih_ar', $cih_ar);
    }

    public function data_ystrday()
    {
        $sales_ystrday = DB::table('sod_det')
            ->select(
                DB::raw('count(sod_depot) AS total_depot'),
                DB::raw('SUM(IFNULL(sod_qty,0)) as total_qty'),
                DB::raw('SUM(IFNULL(sod_amount,0)) as total_amount')
            )
            ->leftJoin('si_mstr', 'sod_det.sod_depot', '=', 'si_mstr.si_site')
            ->where('sod_date', '=', Carbon::yesterday())
            //->whereMonth('sod_date', '=', Carbon::now()->month)
            ->where('si_division', '=', Auth::user()->user_name)
            ->get();

        $coll_ystrday = DB::table('collections')
            ->select(
                DB::raw('count(coll_depot) AS total_depot'),
                DB::raw('SUM(IFNULL(coll_amount,0)) as total_amount')
            )
            ->leftJoin('si_mstr', 'collections.coll_depot', '=', 'si_mstr.si_site')
            ->where('coll_date', '=', Carbon::yesterday())
            //->whereMonth('coll_date', '=', Carbon::now()->month)
            ->where('si_division', '=', Auth::user()->user_name)
            ->get();

        $rem_ystrday = DB::table('remittance')
            ->select(
                DB::raw('count(rem_depot) AS total_depot'),
                DB::raw('SUM(IFNULL(rem_amount,0)) as total_amount')
            )
            ->leftJoin('si_mstr', 'remittance.rem_depot', '=', 'si_mstr.si_site')
            ->where('rem_date', '=', Carbon::yesterday())
            //->whereMonth('rem_date', '=', Carbon::now()->month)
            ->where('si_division', '=', Auth::user()->user_name)
            ->get();
        
        $expnse_ystrday = DB::table('expenses')
            ->select(
                DB::raw('count(exp_depot) AS total_depot'),
                DB::raw('SUM(CASE WHEN exp_status = "approved" THEN apprv_amount ELSE exp_amount END) as total_amount')
            )
            ->leftJoin('si_mstr', 'expenses.exp_depot', '=', 'si_mstr.si_site')
            ->where('exp_date', '=', Carbon::yesterday())
            //->whereMonth('exp_created', '=', Carbon::now()->month)
            ->where('si_division', '=', Auth::user()->user_name)
            ->get();

        foreach ($sales_ystrday as $key => $value) {
            $result['sales']['depot'] = $value->total_depot;
            $result['sales']['amount'] = $value->total_amount;
        }

        foreach ($coll_ystrday as $key => $value) {
            $result['coll']['depot'] = $value->total_depot;
            $result['coll']['amount'] = $value->total_amount;
        }

        foreach ($rem_ystrday as $key => $value) {
            $result['rem']['depot'] = $value->total_depot;
            $result['rem']['amount'] = $value->total_amount;
        }

        foreach ($expnse_ystrday as $key => $value) {
            $result['exp']['depot'] = $value->total_depot;
            $result['exp']['amount'] = $value->total_amount;
        }
        
        //dd($result);
        return $result;
    }

    public function depot_stock()
    {
        $stock =  DB::table('ld_det')
            ->select(
                DB::raw('ld_site AS site'),
                DB::raw('SUM(IFNULL(CASE WHEN pt_prod_line = "TEA" THEN (ld_qty_oh * lot_netprice) ELSE 0 END,0)) AS tea_amount'),
                DB::raw('SUM(IFNULL(CASE WHEN pt_prod_line = "FOOD" THEN (ld_qty_oh * lot_netprice) ELSE 0 END,0)) AS food_amount'),
                DB::raw('SUM(IFNULL(CASE WHEN pt_prod_line = "AGRO" THEN (ld_qty_oh * lot_netprice) ELSE 0 END,0)) AS agro_amount')
            )
            ->leftJoin('pt_mstr', 'ld_det.ld_part', '=', 'pt_mstr.pt_part')
            ->leftJoin('lot_mstr', function ($join) {
                $join->on('ld_det.ld_part', '=', 'lot_mstr.lot_part');
                $join->on('ld_det.ld_lot', '=', 'lot_mstr.lot_lot');
            })
            ->leftJoin('si_mstr', 'ld_det.ld_site', '=', 'si_mstr.si_site')
            ->where('si_division', '=', Auth::user()->user_name)
            ->where('si_type', '=', 'depot')
            ->groupBy('ld_site')
            ->get();
        // $stock = $stock->toSql();
        //dd($stock);

        $result[] = ['Site', 'Tea', 'Food', 'Agro'];
        foreach ($stock as $key => $value) {
            $result[++$key] = [$value->site, (int) $value->tea_amount, (int) $value->food_amount, (int) $value->agro_amount];
        }

        return json_encode($result);
    }

    public function monthly_sales()
    {
        $sales =  DB::table('sod_det')
            ->select(
                DB::raw('sod_depot AS depot'),
                DB::raw('SUM(IFNULL(sod_amount,0)) as monthly_amount')
            )
            ->leftJoin('si_mstr', 'sod_det.sod_depot', '=', 'si_mstr.si_site')
            ->whereMonth('sod_date', '=', Carbon::now()->month)
            ->whereYear('sod_date', '=', Carbon::now()->year)
            ->where('si_division', '=', Auth::user()->user_name)
            ->groupBy('sod_depot')
            ->get();
        //dd($sales);

        $result[] = ['Division', 'This Month'];

        foreach ($sales as $key => $value) {
            $result[++$key] = [$value->depot, (int) $value->monthly_amount];
        }
        
        return json_encode($result);
    }

    public function top_products()
    {
        $top_products =  DB::table('sod_det')
            ->select(
                DB::raw('sod_part AS item'),
                DB::raw('pt_desc AS item_name'),
                DB::raw('SUM(sod_qty * pt_um_conv) as qty_kg')
            )
            ->leftJoin('pt_mstr', 'sod_det.sod_part', '=', 'pt_mstr.pt_part')
            ->leftJoin('si_mstr', 'sod_det.sod_depot', '=', 'si_mstr.si_site')
            ->whereMonth('sod_date', '=', Carbon::now()->month)
            ->whereYear('sod_date', '=', Carbon::now()->year)
            ->where('si_division', '=', Auth::user()->user_name)
            ->groupBy('item')
            ->orderBy('qty_kg', 'desc')
            ->limit(10)
            ->get();

        //dd($top_products);

        $result[] = ['Item', 'Qty'];

        foreach ($top_products as $key => $value) {
            $result[++$key] = [$value->item_name, (int) $value->qty_kg];
        }
        //dd($result);
        return json_encode($result);
    }

    public function depot_cih_ar()
    {
        $cih_ar =  DB::table('si_mstr')
            ->select(
                DB::raw('si_site AS depot'),
                DB::raw('closing_cih'),
                DB::raw('closing_ar'),
                DB::raw('SUM(IFNULL(sod_amount,0)) as monthly_sales')
            )
            ->leftJoin(
                DB::raw(
                    '(SELECT
                si_site AS depot,
                IFNULL( si_closing_cih, 0 ) AS closing_cih,
                IFNULL( SUM( cm_balance ), 0 ) AS closing_ar
                FROM si_mstr
                LEFT JOIN cm_mstr ON cm_mstr.cm_depot = si_mstr.si_site 
                GROUP BY cm_depot) A'
                ),
                function ($join) {
                    $join->on('A.depot', '=', 'si_mstr.si_site');
                }
            )
            ->leftJoin('sod_det', function ($join) {
                $join->on('sod_det.sod_depot', '=', 'si_mstr.si_site')
                    ->whereMonth('sod_date', '=', Carbon::now()->month)
                    ->whereYear('sod_date', '=', Carbon::now()->year);
            })
            ->where('si_division', '=', Auth::user()->user_name)
            ->where('si_type', '=', 'depot')
            ->groupBy('si_site')
            ->orderBy('si_site', 'asc')
            ->get();
        //dd($cih_ar);
        
        foreach ($cih_ar as $key => $value) {
            $result[$key] = $value;
        }
        //dd($result);
        return $result;
    }
}