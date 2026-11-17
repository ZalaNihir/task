<?php

namespace App\Services;

use App\Models\Warehouse;

class WareHouseService{
    public function index($perPage = 10){
        $warehouse =  Warehouse::simplePaginate($perPage);

        return $warehouse;
    }
    public function store($data){
        return Warehouse::create($data);
    }
    public function show($id){
        return Warehouse::findOrFail($id);
    }
    public function update($data, $id){
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($data);

        return $warehouse->fresh();
    }
    public function delete($id){
        $warehouse = Warehouse::findOrFail($id);

        $warehouse->delete();
    }
}
