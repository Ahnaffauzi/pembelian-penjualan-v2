<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventories;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function get(Request $request, $id = null)
    {
        $params = $request->all();

        if ($id != null) {
            $res = Inventories::getById($id, $params, $request);
        } else if (isset($params['all']) && $params['all']) {
            $res = Inventories::getAllResult($params, $request);
        } else {
            $res = Inventories::getPaginatedResult($params, $request);
        }

        return $res;
    }

    public function post(Request $request)
    {
        $params = $request->all();
        return Inventories::createOrUpdate($params, $request->method(), $request);
    }

    public function put(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        return Inventories::createOrUpdate($params, $request->method(), $request);
    }

    public function patch(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        return Inventories::createOrUpdate($params, $request->method(), $request);
    }

    public function delete(Request $request, $id)
    {
        $params = $request->all();

        return Inventories::deleteById($id, $params, $request);
    }

    public function approve(Request $request, $id)
    {
        $params = $request->all();

        return Inventories::approveById($id, $params, $request);
    }

    public function datatables(Request $request)
    {
        $user = auth()->guard('sanctum')->user();

        $columns = [
            'inventories.id',
            'inventories.code',
            'inventories.name',
            'inventories.price',
            'inventories.stock',
            'inventories.id',
        ];

        $dataOrder = [];

        $limit = $request->length;

        $start = $request->start;

        foreach ($request->order as $row) {
            $nestedOrder['column'] = $columns[$row['column']];
            $nestedOrder['dir'] = $row['dir'];

            $dataOrder[] = $nestedOrder;
        }

        $order = $dataOrder;

        $dir = $request->order[0]['dir'];

        $search = $request->search['value'];

        $filter = $request->filter;

        $res = Inventories::datatables($start, $limit, $order, $dir, $search, $filter);

        $data = [];

        if (!empty($res['data'])) {
            foreach ($res['data'] as $row) {
                $nestedData = $row;
                $nestedData['action'] = '';
                $nestedData['action'] .= '<div class="dropdown">';
                $nestedData['action'] .= '<button type="button" class="btn btn-sm btn-outline-dark rounded-2 px-2" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Inventory actions">';
                $nestedData['action'] .= '<i class="fa fa-ellipsis-h"></i>';
                $nestedData['action'] .= '</button>';
                $nestedData['action'] .= '<ul class="dropdown-menu dropdown-menu-end">';
                $nestedData['action'] .= '<li><a href="#" class="dropdown-item edit-data" data-id="'.$row['id'].'">Edit</a></li>';
                $nestedData['action'] .= '<li><a href="#" class="dropdown-item text-danger delete-data" data-id="'.$row['id'].'">Delete</a></li>';
                $nestedData['action'] .= '</ul>';
                $nestedData['action'] .= '</div>';


                $data[] = $nestedData;
            }
        }

        $json_data = [
            'draw'  => intval($request->draw),
            'recordsTotal'  => intval($res['totalData']),
            'recordsFiltered' => intval($res['totalFiltered']),
            'data'  => $data,
            'order' => $order
        ];

        return json_encode($json_data);
    }
}
