<?php

namespace App\Services\Dashboard;

//Interface
use App\Contracts\DashboardRepositoryInterface;

class GraphServices{
    private $dashboardRepositoryInterface;

    public function __construct(
        DashboardRepositoryInterface $dashboardRepositoryInterface
    ){
        $this->dashboardRI = $dashboardRepositoryInterface;
    }

    public function dateViewChart($request)
    {
        $sales = $this->dashboardRI->dateViewChart($request->payment_method);

        $data = [];
        foreach($sales as $sale) {
            $data['label'][] = $sale->months;
            $data['total'][] = (int)$sale->total;
        }
        return  $data;
    }

    public function dayViewChart($request)
    {
        $sales = $this->dashboardRI->dayViewChart($request->payment_method);

        $data = [];
        foreach($sales as $sale) {
            $data['label'][] = $sale->day_name;
            $data['total'][] = (int)$sale->total;
        }
        return  $data;
    }

    public function monthViewChart($request)
    {
        $sales = $this->dashboardRI->monthViewChart($request->payment_method);

        $data = [];
        foreach($sales as $sale) {
            $data['label'][] = $sale->month_name;
            $data['total'][] = (int)$sale->total;
        }
        return  $data;
    }

    public function testData1($request){
        $payment_method = $request->payment_method;
        $sales = Invoice::where('payment_method', $payment_method)
        ->whereYear('date', Carbon::now()->year)
        ->orderBy('date', 'asc')
        ->get();

        $data = [];
        foreach($sales as $sale) {
            $data['label'][] = strtotime($sale->created_at);
            $data['total'][] = (int)$sale->total;
        }
        return $data;
    }

    public function testData2($request){
        $payment_method = $request->payment_method;
        $sales = DB::table('invoices')
        ->select(
            DB::raw("DATE_FORMAT(date,'%Y-%m-%d') as months"),
            DB::raw('sum(total) as total'),
        )
        ->where('payment_method', $payment_method)
        ->whereYear('date', Carbon::now()->year)
        ->whereMonth('date', Carbon::now()->month)
        ->groupBy('months')
        ->orderBy('date', 'asc')
        ->get();

        $data = [];
        foreach($sales as $sale) {
            $data['label'][] = strtotime($sale->months);
            $data['total'][] = (int)$sale->total;
        }
        return  $data;
    }
}