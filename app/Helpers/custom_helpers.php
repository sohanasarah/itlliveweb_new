<?php
    function get_site_desc($site_code)
    {
        $query =  DB::table('si_mstr');
        $query->select(DB::raw('si_desc'))
                ->where('si_site', '=', $site_code);
        $result =  $query->first();
        return $result;
    }

    function get_item_details($item_code)
    {
        $query =  DB::table('pt_mstr');
        $query->select(DB::raw('pt_desc, pt_prod_line, pt_um_conv, pt_brand, pt_group '))
                    ->where('pt_part', '=', $item_code);
        $result =  $query->first();
        return $result;
    }

    function get_customer_details($cust_code)
    {
        $query =  DB::table('customers');
        $query->select(DB::raw('cust_name, cust_outlet '))
                ->where('cust_id', '=', $cust_code);
        $result =  $query->first();
        return $result;
    }

    function get_brand_details($code)
    {
        $query =  DB::table('code_mstr');
        $query->select(DB::raw('code_value, code_cmmt, code_chr01 '))
                ->where('code_value', '=', $code);
        $result =  $query->first();
        return $result;
    }

    function get_project_desc($code)
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

    function get_qty_by_site($item, $site, $location)
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

    function get_lot($item, $user)
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

    function get_qty_available($site, $location, $item, $lot)
    {
        $qty_avail = DB::table('ld_det')
                ->select(
                    DB::raw('IFNULL(SUM(ld_qty_oh),0) AS qty_avail')
                )
                ->where('ld_part', $item)
                ->where('ld_lot', $lot)
                ->where('ld_site', $site)
                ->where('ld_loc', $location)
                ->first();
        if ($qty_avail) {
            return $qty_avail->qty_avail;
        } else {
            return 0;
        }
    }

  


    function ld_det_available($site, $location, $item, $lot)
    {
        $num_rows = DB::table('ld_det')
                ->select(
                    DB::raw('ld_id')
                )
                ->where('ld_part', $item)
                ->where('ld_lot', $lot)
                ->where('ld_site', $site)
                ->where('ld_loc', $location)
                ->count();

        if ($num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

    function get_price($item, $lot)
    {
        if ($lot != '') {
            $price = DB::table('lot_mstr')
                ->select(
                    DB::raw('lot_netprice')
                )
                ->where('lot_part', $item)
                ->where('lot_lot', $lot)
                ->where('lot_status', 'active')
                ->first();

            if ($price) {
                return $price->lot_netprice;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }


    function stock_transaction($tr_nbr, $tr_date, $tr_type, $tr_site, $tr_loc, $ref_site, $tr_customer, $tr_part, $tr_lot, $tr_qty_chg, $tr_ref, $tr_remarks, $tr_user)
    {
        $created = date("Y-m-d H:i:s");
        $success_ok = false;
        $tr_begin_qoh = get_qty_available($tr_site, $tr_loc, $tr_part, $tr_lot);
        $tr_price     = get_price($tr_part, $tr_lot);


        DB::beginTransaction();

        try {
            $insert   =  DB::table('tr_hist')
                            ->insert([
                                'tr_nbr'        => $tr_nbr,
                                'tr_effdate'    => $tr_date,
                                'tr_type'       => $tr_type,
                                'tr_site'       => $tr_site,
                                'tr_loc'        => $tr_loc,
                                'tr_ref_site'   => $ref_site,
                                'tr_customer'   => $tr_customer,
                                'tr_part'       => $tr_part,
                                'tr_lot'        => $tr_lot,
                                'tr_ref'        => $tr_ref,
                                'tr_rmks'       => $tr_remarks,
                                'tr_price'      => $tr_price,
                                'tr_begin_qoh'  => $tr_begin_qoh,
                                'tr_qty_loc'    => $tr_qty_chg,
                                'tr_created'    => $created,
                                'tr_username'   => $tr_user
                            ]);

            if ($insert) {
                $ld_det_available =ld_det_available($tr_site, $tr_loc, $tr_part, $tr_lot);
                //echo $ld_det_available;
                if ($ld_det_available) {
                    try {
                        $update_stock = DB::table('ld_det')
                                    ->where('ld_site', $tr_site)
                                    ->where('ld_loc', $tr_loc)
                                    ->where('ld_part', $tr_part)
                                    ->where('ld_lot', $tr_lot)
                                    ->update([
                                        'ld_qty_oh'    => DB::raw('ld_qty_oh + ' . $tr_qty_chg),
                                        'ld_qty_avail' => DB::raw('ld_qty_avail + ' . $tr_qty_chg),
                                    ]);
                        $success_ok = true;
                    } catch (\Exception $e) {
                        $success_ok = false;
                        $error_msg = "Error " . $e->getMessage();
                    }
                } else {
                    try {
                        $update_stock = DB::table('ld_det')
                            ->where('ld_site', $tr_site)
                            ->where('ld_loc', $tr_loc)
                            ->where('ld_part', $tr_part)
                            ->where('ld_lot', $tr_lot)
                            ->insert([
                                'ld_part'      => $tr_part,
                                'ld_lot'       => $tr_lot,
                                'ld_site'      => $tr_site,
                                'ld_loc'       => $tr_loc,
                                'ld_qty_oh'    => $tr_qty_chg,
                                'ld_qty_avail' => $tr_qty_chg
                            ]);
                        $success_ok = true;
                    } catch (\Exception $e) {
                        $success_ok = false;
                        $error_msg = "Error " . $e->getMessage();
                    }
                }
            }

            /****Commit Or Rollback****/
            if ($success_ok=='true') {
                DB::commit();
                return true;
            } else {
                DB::rollback();
                return $error_msg;
            }
        } catch (\Exception $e) {
            $error_msg = "Error " . $e->getMessage();
            return $error_msg;
        }
    }
