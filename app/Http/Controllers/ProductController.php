<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {

        $qpArr = $request->all();
        
        $variantArr = ProductVariant::pluck('variant', 'id')->toArray();

        $variantInfo = ProductVariant::join('variants', 'variants.id', 'product_variants.variant_id')
            ->select(
                'product_variants.id as pr_var_id',
                'product_variants.variant',
                'variants.id as var_id',
                'variants.title as var'
            )
            ->get();

        $variantList = [];
        if(!$variantInfo->isEmpty()){
            foreach($variantInfo as $vInfo){
                $variantList[$vInfo->var_id]['title'] = $vInfo->var ?? '';
                $variantList[$vInfo->var_id]['var'][$vInfo->variant]= $vInfo->variant ?? '';
            }
        }
        
        $productVarInfo = ProductVariantPrice::select(
            'product_variant_prices.product_id', 
            'product_variant_prices.id as var_id',
            'product_variant_prices.product_variant_one as var_one_id',
            'product_variant_prices.product_variant_two as var_two_id',
            'product_variant_prices.product_variant_three as var_three_id',
            'product_variant_prices.price',
            'product_variant_prices.stock'
        );

        

        if(!empty($request->price_from) && !empty($request->price_to)){
            $productVarInfo = $productVarInfo->whereBetween('product_variant_prices.price', [$request->price_from, $request->price_to]);
        } else {
            if(!empty($request->price_from)){
                $productVarInfo = $productVarInfo->where('product_variant_prices.price', '>=', $request->price_from);
            }
            if(!empty($request->price_to)){
                $productVarInfo = $productVarInfo->where('product_variant_prices.price', '<=', $request->price_to);
            }
        }
        $productVarInfo = $productVarInfo->get();

        $productInfo = Product::select(
                'products.id', 
                'products.created_at',
                'products.title',
                'products.description'
            )
            ->orderBy('products.created_at', 'desc');

        $productArr = $productIdArr = [];
        if(!$productVarInfo->isEmpty()){
            foreach($productVarInfo as $info){
                $varTitle = !empty($info->var_one_id) && !empty($variantArr[$info->var_one_id]) ? $variantArr[$info->var_one_id] : ''; 
                $varTitle .= !empty($info->var_two_id) && !empty($variantArr[$info->var_two_id]) ? '/ ' . $variantArr[$info->var_two_id] : ''; 
                $varTitle .= !empty($info->var_three_id) && !empty($variantArr[$info->var_three_id]) ? '/ ' . $variantArr[$info->var_three_id] : ''; 
                
                if(!empty($request->variant)){
                    if(preg_match("/" . $request->variant . "/i", $varTitle)){
                        $productIdArr[$info->product_id] = $info->product_id;
                        
                    }
                } else{
                    $productIdArr[$info->product_id] = $info->product_id;
                }
                
                $productArr[$info->product_id][$info->var_id]['title'] = $varTitle ?? '';
                $productArr[$info->product_id][$info->var_id]['price'] = $info->price ?? '';
                $productArr[$info->product_id][$info->var_id]['stock'] = $info->stock ?? '';
            }
            $productInfo = $productInfo->whereIn('id', $productIdArr);
        }

        
        if(!empty($request->date)){
            $fromDate = date('Y-m-d', strtotime($request->date)) . ' 00:00:00'; 
            $toDate = date('Y-m-d', strtotime($request->date)) . ' 23:59:59'; 
            $productInfo = $productInfo->whereBetween('created_at', [$fromDate, $toDate]);
        }
        
        if(!empty($request->title)){
            $productInfo = $productInfo->where('title', 'LIKE', '%' . $request->title . '%');
        }


        $productInfo = $productInfo->paginate(10);

        
        // echo '<pre>';
        // print_r($variantList);
        // exit;

        if ($productInfo->isEmpty() && isset($qpArr['page']) && ($qpArr['page'] > 1)) {
            $page = ($qpArr['page'] - 1);
            return redirect('/product?page=' . $page);
        }

        return view('products.index')->with(compact('qpArr', 'productArr', 'productInfo', 'variantList'));
    }

    public function filter(Request $request) {
        // echo '<pre>';
        // print_r($request->all());
        // exit;

        $url = 'title=' . urlencode($request->title) . '&variant=' . urlencode($request->variant) 
            . '&price_from=' . $request->price_from . '&price_to=' . $request->price_to
            . '&date=' . $request->date;
        return redirect('product?' . $url);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $variantList = Variant::pluck('title', 'id')->toArray();

        $product = Product::select('id', 'title', 'sku', 'description')
            ->where('id', $id)
            ->first();

        $productImageArr = ProductImage::where('product_id', $id)->pluck('file_path', 'id')->toArray();
        
        $variantInfo = ProductVariant::select('variant', 'id', 'variant_id')
            ->where('product_id', $id)->get();

        $variantArr = $variantTitleArr = [];
        if(!$variantInfo->isEmpty()){
            foreach($variantInfo as $vIn){
                $variantArr[$vIn->variant_id][$vIn->id] = $vIn->variant;
                $variantTitleArr[$vIn->id] = $vIn->variant;
            }
        }

        $productVarInfo = ProductVariantPrice::select(
                'product_variant_prices.id as var_id',
                'product_variant_prices.product_variant_one as var_one_id',
                'product_variant_prices.product_variant_two as var_two_id',
                'product_variant_prices.product_variant_three as var_three_id',
                'product_variant_prices.price',
                'product_variant_prices.stock'
            )
            ->where('product_id', $id)
            ->get();

        $productVarArr = $productIdArr = [];
        if(!$productVarInfo->isEmpty()){
            foreach($productVarInfo as $info){
                $varTitle = !empty($info->var_one_id) && !empty($variantTitleArr[$info->var_one_id]) ? $variantTitleArr[$info->var_one_id] : ''; 
                $varTitle .= !empty($info->var_two_id) && !empty($variantTitleArr[$info->var_two_id]) ? '/ ' . $variantTitleArr[$info->var_two_id] : ''; 
                $varTitle .= !empty($info->var_three_id) && !empty($variantTitleArr[$info->var_three_id]) ? '/ ' . $variantTitleArr[$info->var_three_id] : ''; 
                
                $var = !empty($info->var_one_id) ? $info->var_one_id : 0; 
                $var .= !empty($info->var_two_id) ? '_' . $info->var_two_id : '_' . 0; 
                $var .= !empty($info->var_three_id) ? '_' . $info->var_three_id : '_' . 0; 
                
                
                $productVarArr[$info->var_id]['var'] = $var ?? '';
                $productVarArr[$info->var_id]['title'] = $varTitle ?? '';
                $productVarArr[$info->var_id]['price'] = $info->price ?? '';
                $productVarArr[$info->var_id]['stock'] = $info->stock ?? '';
            }
        }


        return view('products.edit')->with(compact(
            'variantList', 
            'product', 
            'productImageArr',
            'variantArr',
            'productVarArr'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(Request $request)
    {
        // echo '<pre>';
        // print_r($request->all());
        // exit;
        $id = $request->product_id;
        $product = Product::find($id);

        $rules = $message = [];
        $rules = [
            'title' => 'required|unique:products,title,' . $id,
            'sku' => 'required|unique:products,sku,' . $id,
            // 'description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $message);
        if ($validator->fails()) {
            return Response::json(array('success' => false, 'heading' => 'Validation Error', 'message' => $validator->errors()), 400);
        }

        $product->title = $request->title;
        $product->sku = $request->sku;
        $product->description = $request->description;
        $product->updated_at = date("Y-m-d H:i:s");

        DB::beginTransaction();
        try{
            if($product->save()){
                ProductVariant::where('product_id', $id)->delete();
                ProductVariantPrice::where('product_id', $id)->delete();
                $data = [];
                $i = 0;
                if(!empty($request->variant)){
                    foreach($request->variant as $vi => $var){
                        $varArr = !empty($var['p_var']) ? explode(' ', $var['p_var']) : [];

                        if(!empty($varArr)){
                            foreach($varArr as $vj => $v){
                                $data[$i]['variant'] = $v;
                                $data[$i]['variant_id'] = $var['var'];
                                $data[$i]['product_id'] = $id;
                                $data[$i]['created_at'] = date("Y-m-d H:i:s");
                                $data[$i]['updated_at'] = date("Y-m-d H:i:s");
                                $i++;
                            }
                        }
                    }
                    ProductVariant::insert($data);
                }

                $prVarData = ProductVariant::where('product_id', $id)
                    ->select('id', 'variant_id')->get();

                $prVarArr = [];
                if(!$prVarData->isEmpty()){
                    foreach($prVarData as $prv){
                        $prVarArr[$prv->variant_id][$prv->id] = $prv->id;
                    }
                }
                $pvarArr = [];
                $i = $j = 0;
                if(!empty($prVarArr)){
                    foreach($prVarArr as $pvid => $pv){
                        foreach($pv as $prvid => $prvid){
                            $pvarArr[$i] = $prvid;
                            foreach($prVarArr as $pvid2 => $pv2){
                                if($pvid2 != $pvid){
                                    foreach($pv2 as $prvid2 => $prvid2){
                                        $pvarArr[$i] .= '_' . $prvid2;

                                        foreach($prVarArr as $pvid3 => $pv3){
                                            if($pvid3 != $pvid && $pvid3 != $pvid2){
                                                foreach($pv3 as $prvid3 => $prvid3){
                                                    $pvarArr[$i] .= '_' . $prvid3;
                                                    break;
                                                }
                                            }
                                        }
                                        break;
                                    }
                                }
                            }
                            $i++;
                        }
                    }
                }

                $data = [];
                $i = 0;
                if(!empty($request->pr_var)){
                    foreach($request->pr_var as $pvi => $pvar){
                        $pvarArr2 = !empty($pvarArr[$i]) ? explode('_', $pvarArr[$i]) : [];
                        $data[$i]['product_variant_one'] = $pvarArr2[0] ?? null;
                        $data[$i]['product_variant_two'] = $pvarArr2[1] ?? null;
                        $data[$i]['product_variant_three'] = $pvarArr2[2] ?? null;
                        $data[$i]['price'] = $pvar['price'] ?? 0;
                        $data[$i]['stock'] = $pvar['stock'] ?? 0;
                        $data[$i]['product_id'] = $id;
                        $data[$i]['created_at'] = date("Y-m-d H:i:s");
                        $data[$i]['updated_at'] = date("Y-m-d H:i:s");
                        $i++;
                    }
                    ProductVariantPrice::insert($data);
                }
            }

            DB::commit();
            return Response::json(array('success' => true, 'heading' => 'Success', 'message' => 'Product has beeen updated successfully'), 200);
            
        } catch(\Throwable $e){
            DB::rollback();
            return Response::json(array('success' => false, 'heading' => 'Error', 'message' => 'Failed to update product'), 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
