<?php

namespace App\Services\Pos;

class PaymentMethodServices extends PaymentServices{

    public function payWithFIAT($request, $cartItems)
    {
        $payment = $this->calPayment($request, $cartItems);

        return [
            'cartItems' => $cartItems,
            'subTotal' => $payment['subTotal'],
            'discount' => $payment['discount'],
            'tax' => $payment['tax'],
            'total' => $payment['total'],
            'payment_method' => 'fiat'
        ];
    }

    public function payWithTNBC($request, $cartItems)
    {
        $configuration = $this->baseRI->findById($this->configModel, 1);
        $tnbcRate = $configuration->tnbc_rate;

        $payment = $this->calPayment($request, $cartItems);

        $subTotalTNBC = $payment['subTotal']/$tnbcRate;
        $discountTNBC = $payment['discount']/$tnbcRate;
        $taxTNBC = $payment['tax']/$tnbcRate;
        $totalTNBC = $payment['total']/$tnbcRate;

        $tnbc=[];

        foreach($cartItems as $cartItem){
            $unit_price = $cartItem['unit_price'] /$tnbcRate;
            $total = $cartItem['total'] /$tnbcRate;
            $obj = [
                "id"=>$cartItem['id'],
                "cart_id"=>$cartItem['cart_id'],
                "item_id"=>$cartItem['item_id'],
                "item_name"=>$cartItem['item_name'],
                "unit"=>$cartItem['unit'],
                "unit_price"=>$unit_price,
                "qty"=>$cartItem['qty'],
                "total"=>$total
            ];
            array_push($tnbc, $obj);
        }
        return [
            'cartItems' => $tnbc,
            'subTotal' => $subTotalTNBC,
            'discount' => $discountTNBC,
            'tax' => $taxTNBC,
            'total' => $totalTNBC,
            'payment_method' => 'tnbc'
        ];
        
    }

    function paymentMethod($request, $cartItems)
    {
        if($request->has('payment_method')){
            $pm = $request->payment_method;
            switch ($pm) {
                case 'tnbc':
                    $list = $this->payWithTNBC($request, $cartItems);
                    break;
                case 'fiat':
                    $list = $this->payWithFIAT($request, $cartItems);
                    break;
                default:
                $list= $this->payWithFIAT($request, $cartItems);
            }
            return $list;
        }else{
            return $this->payWithFIAT($request, $cartItems);
        }
    }
}