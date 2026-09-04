<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Sales;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function get(Request $request, $id)
    {
        $params = $request->all();

        if ($id != null) {
            $res = Sales::getByIdWithDetails($id, $params, $request);
        } else if (isset($params['all']) && $params['all']) {
            $res = Sales::getAllResult($params, $request);
        } else {
            $res = Sales::getPaginatedResult($params, $request);
        }

        return $res;
    }

    public function post(Request $request)
    {
        $params = $request->all();

        if (isset($params['items'])) {
            return Sales::createOrder($params, $request);
        }

        return Sales::createOrUpdate($params, $request->method(), $request);
    }

    public function put(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        return Sales::createOrUpdate($params, $request->method(), $request);
    }

    public function patch(Request $request, $id)
    {
        $params = $request->all();
        $params['id'] = $id;
        return Sales::createOrUpdate($params, $request->method(), $request);
    }

    public function delete(Request $request, $id)
    {
        $params = $request->all();

        return Sales::deleteById($id, $params, $request);
    }

    public function approve(Request $request, $id)
    {
        $params = $request->all();

        return Sales::approveById($id, $params, $request);
    }

    public function datatables(Request $request)
    {
        $user = auth()->guard('sanctum')->user();

        $columns = [
            'sales.number',
            'sales.date',
            'users.name',
            'total_qty',
            'total_amount',
            'sales.id',
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

        $res = Sales::datatables($start, $limit, $order, $dir, $search, $filter);

        $data = [];

        if (!empty($res['data'])) {
            foreach ($res['data'] as $row) {
                $nestedData = $row;
                $nestedData['action'] = '';
                $nestedData['action'] .= '<div class="dropdown">';
                $nestedData['action'] .= '<button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="dropdown">';
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
