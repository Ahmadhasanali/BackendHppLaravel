<?php

namespace App\Http\Controllers\Api;

use App\Models\Hpp;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class HppController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAll()
    {
        $hpp = Hpp::orderBy('Date', 'asc')->get();
        return response()->json([
            'data' => $hpp
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $data = $request->validate([
        'date' => 'date|required',
        'qty' => 'integer|required|min:0',
        'price' => 'integer|required',
        'type' => 'required'
       ]);

       ucfirst($data['type']) == 'Pembelian'? $data['qty'] = $data['qty']:$data['qty'] = -$data['qty'];

       $cost = $this->getCost(ucfirst($data['type']), $data['date'], $data['price']);
       $totalCost = $this->getTotalCost($data['qty'], $cost);
       $qtyBalance = $this->getQtyBalance($data['date'], $data['qty']);
       $valueBalance = $this->getValueBalance($data['date'], $totalCost);
       $hpp = $valueBalance/$qtyBalance;

       return $this->storeOrAndUpdate($data, $cost, $totalCost, $qtyBalance, $valueBalance, $hpp);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hpp $hpp)
    {
        $data = Hpp::where('id', $request->id)->firstOrFail();
        if (!$data) return response()->json(['error'], Response::HTTP_SERVICE_UNAVAILABLE);

        $validate = $request->validate([
            'date' => 'date|required',
            'qty' => 'integer|required|min:0',
            'price' => 'integer|required',
            'type' => 'required'
        ]);

       ucfirst($validate['type']) == 'Pembelian'? $validate['qty'] = $validate['qty']:$validate['qty'] = -$validate['qty'];

       $cost = $this->getCost(ucfirst($validate['type']), $validate['date'], $validate['price']);
       $totalCost = $this->getTotalCost($validate['qty'], $cost);
       $qtyBalance = $this->getQtyBalance($validate['date'], $validate['qty']);
       $valueBalance = $this->getValueBalance($validate['date'], $totalCost);
       $hpp = $valueBalance/$qtyBalance;

       try {
            DB::beginTransaction();
            Hpp::where('id', $request->id)->update([
                'qty' => $validate['qty'],
                'price'=> $validate['price'],
                'date' => $validate['date'],
                'description' => $validate['type'],
                'cost' => $cost,
                'total_cost' => $totalCost,
                'qty_balance' => $qtyBalance,
                'value_balance' => $valueBalance,
                'hpp' => $hpp,
            ]);

            $nData = Hpp::where('date', '>', $validate['date'])->orderBy('date', 'asc')->get();
            foreach($nData as $value){
                $this->updateDataAfter($value);
            }

            DB::commit();
       } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(array('error' => $th));
       }
       return response()->json(array('success' => true), Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $data = Hpp::where('id', $request->id)->firstOrFail();
        $data->delete();
        $nData = Hpp::where('date', '>', $data['date'])->orderBy('date', 'asc')->get();

        foreach($nData as $value){
            $this->updateDataAfter($value);
        }
        return response()->json(array('success' => true), Response::HTTP_OK);
    }


    private function storeOrAndUpdate($data, $cost, $totalCost, $qtyBalance, $valueBalance, $hpp){
        $existNextData = Hpp::where('date', '>', $data['date'])->orderBy('date', 'asc')->get();
        if($existNextData->count() == 0){
            try {
                DB::beginTransaction();
                $this->singleStore($data, $cost, $totalCost, $qtyBalance, $valueBalance, $hpp);
                DB::commit();

                return response()->json(['success'], Response::HTTP_CREATED);
               } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(array('error' => $th), Response::HTTP_INTERNAL_SERVER_ERROR);
               }
        }else{
            try {
                DB::beginTransaction();
                $newData = $this->singleStore($data, $cost, $totalCost, $qtyBalance, $valueBalance, $hpp);
                DB::commit();
               } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json(array('error' => $th), Response::HTTP_INTERNAL_SERVER_ERROR);
               }
            foreach($existNextData as $value){
                $this->updateDataAfter($value);
            }
            return response()->json(array('success'=>true), Response::HTTP_OK);
        }
    }

    private function singleStore($data, $cost, $totalCost, $qtyBalance, $valueBalance, $hpp){
        return Hpp::create([
            'description' => $data['type'],
            'date' => $data['date'],
            'qty' => $data['qty'],
            'cost' => $cost,
            'price' => $data['price'],
            'total_cost' => $totalCost,
            'qty_balance' => $qtyBalance,
            'value_balance' =>$valueBalance,
            'hpp'=>$hpp
        ]);
    }

    private function updateDataAfter($value){
        try{
            DB::beginTransaction();
            $value->cost = $this->getCost($value->description, $value->date, $value->price);
            $value->total_cost = $this->getTotalCost($value->qty, $value->cost);
            $value->qty_balance = $this->getQtyBalance($value->date, $value->qty);
            $value->value_balance = $this->getValueBalance($value->date, $value->total_cost);
            $value->save();
            DB::commit();
        } catch(\Throwable $th){
            DB::rollBack();
            return response()->json(array('error' => $th), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Cost didapatkan berdaasarkan description, jika pembelian akan mengembalikan nilai price
     * jika penjualan akan mengembalikan nilai hpp dari data 4000
     */
    private function getCost($type, $date, $price){
        if($type == 'Pembelian') return $price;
        
        $data = Hpp::where('date','=',$date)->orderBy('id','desc')->first();
        if(!$data){
            $data = Hpp::where('date','<',$date)->orderBy('id', 'desc')->first();
        }
        return $data->hpp;
    }

    /**
     * TotalCost didapatkan dari Qty dikalikan Cost
     */
    private function getTotalCost($qty,$cost)
    {
        return $qty * $cost;
    }

    /**
     * QtyBalance didapatkan dari penjumlahan Qty Balance sebelumnya dengan Qty yang diinputkan
     */

    private function getQtyBalance($date, $qty)
    {
        $data = Hpp::where('date','=',$date)->orderBy('id','desc')->first();
        
        if($data){
            $data = $data->qty_balance + $qty;
        }
        
        if (!$data){
            $data = Hpp::where('date','<',$date)->orderBy('id','desc')->first();
            $data = $data->qty_balance + $qty;

        }
        
        if (!$data){
            $data = $qty;
        }
        
        return $data;
    }

    /**
     * Value Balance didapatkan dari penjulmahan Value balance sebelumnya dengan totalCost
     */

    private function getValueBalance($date, $totalCost)
    {
        $data = Hpp::where('date','=',$date)->orderBy('id','desc')->first();

        if($data){
            $data = $data->value_balance + $totalCost;
        }

        if (!$data){
            $data = Hpp::where('date','<',$date)->orderBy('id','desc')->first();
            $data = $data->value_balance + $totalCost;
        }

        if (!$data){
            $data = $totalCost;
        }

        return $data;
    }
}