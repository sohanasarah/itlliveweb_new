<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class Helpers
{
    public static function bob($string)
    {
        return '<strong> ' . $string . '?! Is that you?!</strong>';
    }

    public static function get_site_desc($site_code)
    {
        $query =  DB::table('si_mstr');
        $query->select(DB::raw('si_desc'))
            ->where('si_site', '=', $site_code);
        $result =  $query->first();
        return $result;
    }
    
    public static function get_item_details($item_code)
    {
        $query =  DB::table('items');
        $query->select(DB::raw('item_name, item_desc1, item_desc2 '))
            ->where('item_code', '=', $item_code);
        $result =  $query->first();
        return $result;
    }

    public static function get_customer_details($cust_code)
    {
        $query =  DB::table('customers');
        $query->select(DB::raw('cust_name, cust_outlet '))
            ->where('cust_id', '=', $cust_code);
        $result =  $query->first();
        return $result;
    }

    public static function get_brand_details($code)
    {
        $query =  DB::table('code_mstr');
        $query->select(DB::raw('code_value, code_cmmt, code_chr01 '))
            ->where('code_value', '=', $code);
        $result =  $query->first();
        return $result;
    }

    public static function get_project_desc($code)
    {
        $query = DB::table('prj_mstr');
        $query->select(DB::raw('prj_desc'))
            ->where('prj_code', '=', $code);
        $result =  $query->first();
        if ($result) {
            return $result;
        } else {
            return null;
        }
    }

    public static function get_qty_by_site($item, $site, $location)
    {
        $avail_qty =  DB::table('ld_det')
            ->select(
                DB::raw('SUM(ld_qty_oh) AS qty_avail')
            )
            ->where('ld_part', '=', $item)
            ->where('ld_site', '=', $site)
            ->where('ld_loc', '=', $location)
            ->where('ld_qty_oh', '<>', 0)
            ->first();

        if ($avail_qty) {
            return $avail_qty;
        } else {
            return null;
        }
    }
    
    public static function get_lot($item, $user)
    {
        $avail_qty  = DB::table('ld_det')
                    ->select(
                        DB::raw('ld_lot,ld_qty_oh')
                    )
                    ->where('ld_site', '=', $user)
                    ->where('ld_part', '=', $item)
                    ->where('ld_loc', '=', 'LG001')
                    ->where('ld_qty_oh', '<>', 0)
                    ->get();

        if ($avail_qty) {
            return $avail_qty;
        } else {
            return null;
        }
           
    }

    
}
