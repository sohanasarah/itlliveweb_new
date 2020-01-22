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
        $query =  DB::table('pt_mstr');
        $query->select(DB::raw('pt_desc, pt_prod_line, pt_um_conv, pt_brand, pt_group '))
            ->where('pt_part', '=', $item_code);
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

    public function stock_transaction($conn, $tr_nbr, $tr_date, $tr_type, $tr_site, $tr_loc, $ref_site, $tr_customer, $tr_part, $tr_lot, $tr_qty_chg, $tr_ref, $tr_remarks, $tr_user)
    {
        $logfile = fopen("log.txt", "w") or die("Unable to open file!");
        $created = date("Y-m-d H:i:s");
        $executed = false;
        $tr_begin_qoh = get_qty_available($conn, $tr_site, $tr_loc, $tr_part, $tr_lot);

        $tr_price = get_price($conn, $tr_part, $tr_lot);


        $insert = 'INSERT INTO tr_hist(tr_nbr, tr_effdate, tr_type, tr_site, tr_loc, tr_ref_site, tr_customer, tr_part, tr_lot, tr_ref, '
        . 'tr_rmks, tr_price, tr_begin_qoh, tr_qty_loc, tr_created, tr_username)'
        . 'VALUES("' . $tr_nbr . '", "' . $tr_date . '", "' . $tr_type . '", "' . $tr_site . '", "' . $tr_loc . '", "' . $ref_site . '", "' . $tr_customer . '", "' . $tr_part . '", "' . $tr_lot . '", '
        . ' "' . $tr_ref . '", "' . $tr_remarks . '", "' . $tr_price . '", "' . $tr_begin_qoh . '", "' . $tr_qty_chg . '", "' . $created . '", "' . $tr_user . '")';

        if (mysqli_query($conn, $insert)) {
            if (ld_det_available($conn, $tr_site, $tr_loc, $tr_part, $tr_lot)) {
                $executed = mysqli_query($conn, 'UPDATE ld_det SET ld_qty_oh=ld_qty_oh+' . $tr_qty_chg . ', ld_qty_avail=ld_qty_avail+' . $tr_qty_chg . ' WHERE ld_site="' . $tr_site . '" AND ld_loc="' . $tr_loc . '" AND ld_part="' . $tr_part . '" AND ld_lot="' . $tr_lot . '"');
                if (!$executed) {
                    fwrite($logfile, "Error in Query: " . mysqli_error($conn));
                }
            } else {
                $executed = mysqli_query($conn, 'INSERT INTO ld_det(ld_part, ld_lot, ld_site, ld_loc, ld_qty_oh, ld_qty_avail) VALUES("' . $tr_part . '", "' . $tr_lot . '", "' . $tr_site . '", "' . $tr_loc . '", "' . $tr_qty_chg . '", "' . $tr_qty_chg . '")');
                if (!$executed) {
                    fwrite($logfile, "Error in Query: " . mysqli_error($conn));
                }
            }
        } else {
            fwrite($logfile, "Error in Query: " . mysqli_error($conn));
            $executed = false;
        }
        fclose($logfile);
        return $executed;
    }
}
