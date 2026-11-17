<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IndexRequest;
use App\Http\Requests\WareHouseFormRequest;
use App\Http\Resources\WarehouseResource;
use App\Services\WareHouseService;

class WarehouseController extends Controller
{
    public function __construct(protected WareHouseService $wareHouseService){}

    public function index(IndexRequest $request){
        $page = $request->integer('per_page', 10);
        $data = $this->wareHouseService->index($page);

        return WarehouseResource::collection($data);
    }

    public function store(WareHouseFormRequest $request)
    {
        $validate = $request->validated();
        $data = $this->wareHouseService->store($validate);

        return new WarehouseResource($data);
    }

    public function show($id){
        $data = $this->wareHouseService->show($id);

        return new WarehouseResource($data);
    }

    public function update(WareHouseFormRequest $request,$id){
        $validate = $request->validated();
        $data = $this->wareHouseService->update($validate,$id);

        return new WarehouseResource($data);
    }

    public function destroy($id){
        $this->wareHouseService->delete($id);

        return response()->json([
            'message'=>'warehouse deleted.',
        ]);
    }
}
