<?php

namespace App\Models;

use DB;
use Illuminate\Support\Str;
use App\Helpers\ModelHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string number
 * @property Date   date
 * @property int    user_id
 * @property int    deleted_at
 * @property int    created_at
 * @property int    updated_at
 */
class Sales extends Model
{
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sales';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    
    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
		'date',
		'user_id',
		'deleted_at',
		'created_at',
		'updated_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'number' => 'string', 'date' => 'date', 'user_id' => 'int', 'deleted_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [

    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var boolean
     */
    public $timestamps = true;

    public $incrementing = true;

    // Scopes...

    // Functions ...

    // Relations ...

    public static function mapSchema($params = [], $user = [])
    {
        $model = new self;

        return [
            'field' => [
                'id' => ['column' => $model->table.'.id', 'alias' => 'id', 'type' => 'int'],
				'number' => ['column' => $model->table.'.number', 'alias' => 'number', 'type' => 'string'],
				'date' => ['column' => $model->table.'.date', 'alias' => 'date', 'type' => 'date'],
				'user_id' => ['column' => $model->table.'.user_id', 'alias' => 'user_id', 'type' => 'int'],

                // Additional fields from users table
                'user_name' => ['column' => 'users.name', 'alias' => 'user_name', 'type' => 'string'],

                // Additional fields for total quantity and total amount
                'total_qty' => [
                    'column' => 'COALESCE(SUM(sales_details.qty), 0)',
                    'alias' => 'total_qty',
                    'type' => 'int',
                    'is_raw' => true
                ],
                'total_amount' => [
                    'column' => 'COALESCE(SUM(sales_details.qty * sales_details.price), 0)',
                    'alias' => 'total_amount',
                    'type' => 'int',
                    'is_raw' => true
                ],

				'deleted_at' => ['column' => $model->table.'.deleted_at', 'alias' => 'deleted_at', 'type' => 'date'],
				'created_at' => ['column' => $model->table.'.created_at', 'alias' => 'created_at', 'type' => 'date'],
				'updated_at' => ['column' => $model->table.'.updated_at', 'alias' => 'updated_at', 'type' => 'date'],
            ],
            'join' => [
                [
                    'type' => 'left',
                    'table' => 'users',
                    'on' => [
                        ['users.id', '=', 'sales.user_id', false]
                    ]
                ],
                [
                    'type' => 'left',
                    'table' => 'sales_details',
                    'on' => [
                        ['sales_details.sales_id', '=', 'sales.id', false]
                    ]
                ]
            ],
            'where' => [

            ]
        ];
    }

    public static function datatables($start, $length, $order, $dir, $search, $filter = [])
    {
        $schema = self::mapSchema();

        $totalData = self::count();

        $qry = ModelHelper::select($schema['field'], null, __CLASS__);
        ModelHelper::join($schema['join'], null, $qry);

        if (!empty($filter)) {
            ModelHelper::dynamicFilterAnd($filter, null, $qry, __CLASS__);
        }
        
        //FILTER

        $totalFiltered = $qry->count();

        if (empty($search)) {
            
            if ($length > 0) {
                $qry->skip($start)
                    ->take($length);
            }

            foreach ($order as $row) {
                $qry->orderBy($row['column'], $row['dir']);
            }

        } else {
            foreach (array_values($schema['field']) as $key => $val) {
                if ($key < 1) {
                    $qry->whereRaw('('.$val['column'].'::varchar(255) ILIKE \'%'.$search.'%\'');
                } else if (count(array_values($schema['field'])) == ($key + 1)) {
                    $qry->orWhereRaw($val['column'].'::varchar(255) ILIKE \'%'.$search.'%\')');
                } else {
                    $qry->orWhereRaw($val['column'].'::varchar(255) ILIKE \'%'.$search.'%\'');
                }
            }

            $totalFiltered = $qry->count();

            if ($length > 0) {
                $qry->skip($start)
                    ->take($length);
            }

            foreach ($order as $row) {
                $qry->orderBy($row['column'], $row['dir']);
            }
        }

        // Group by sales.id and users.name to avoid duplicate rows due to the join with sales_details
        $qry->groupBy('sales.id', 'users.name');

        return [
            'data' => $qry->get(),
            'totalData' => $totalData,
            'totalFiltered' => $totalFiltered
        ];
    }

    public static function getPaginatedResult($params, $request)
    {
        $append = [];
        $schema = self::mapSchema();

        $paramsPage = isset($params['page']) ? $params['page'] : 0;
        
        $or = [];

        unset($params['page']);

        if (isset($params['or']) && $params['or']) {
            $or = $params['or'];
            unset($params['or']);
        }

        $db = ModelHelper::select($schema['field'], $request, __CLASS__);
        ModelHelper::join($schema['join'], $request, $db);

        if ($params) {
            ModelHelper::dynamicFilterAnd($params, $request, $db, __CLASS__);
        }

        if ($or) {
            ModelHelper::dynamicFilterOr($or, $request, $db, __CLASS__);
        }

        $results = ModelHelper::generatePagingResults($schema, $paramsPage, $params, $request, $db, $append);

        return response()->json($results);
    }

    public static function getById($id, $params = [], $request = null)
    {
        $models = new self;

        $append = [];

        $schema = self::mapSchema();
        
        $db = ModelHelper::select($schema['field'], $request, __CLASS__)->where($models->table.'.id', $id);
        
        ModelHelper::join($schema['join'], $request, $db);
        
        return response()->json($db->first());
    }

    public static function getAllResult($params, $request)
    {
        $append = [];
        $schema = self::mapSchema();

        $or = [];
        
        unset($params['all']);

        if (isset($params['or']) && $params['or']) {
            $or = $params['or'];
            unset($params['or']);
        }

        $db = ModelHelper::select($schema['field'], $request, __CLASS__);
        ModelHelper::join($schema['join'], $request, $db);

        if ($params) {
            ModelHelper::dynamicFilterAnd($params, $request, $db, __CLASS__);
        }

        if ($or) {
            ModelHelper::dynamicFilterOr($or, $request, $db, __CLASS__);
        }

        $results = ModelHelper::generateAllResults($schema, $params, $request, $db, $append);

        return response()->json($results);
    }

    public static function createOrUpdate($params, $method, $request)
    {
        DB::beginTransaction();

        $filename = null;

        if (isset($params['_token']) && $params['_token']) {
            unset($params['_token']);
        }

        if (empty($params['number'])) {
            $params['number'] = self::generateNumber();
        }

        if (empty($params['user_id'])) {
            $params['user_id'] = auth()->id();
        }

        if (isset($params['id']) && $params['id']) {
            $old = self::getById($params['id'])->original;

            $update = self::where('id', $params['id'])->update($params);

            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Succesfully Updated Data',
                'data' => self::getById($params['id'])->original
            ]);
        }

        $save = self::create($params);

        DB::commit();
        return response()->json([
            'status' => 'success',
            'message' => 'Succesfully Added Data',
            'data' => self::getById($save->id)->original
        ]);
    }

    public static function deleteById($id, $params, $request)
    {
        // $old = self::getById($id)->original;

        self::where('id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Succesfully Deleted Data'
        ]);
    }

    public static function approveById($id, $params, $request)
    {
        // $data = self::getById($id)->original;

        return response()->json([
            'status' => 'success',
            'message' => 'Succesfully Approved Data',
            'data' => null
        ]);
    }

    public static function generateNumber()
    {
        $date = date('Ymd');
        $count = self::whereDate('created_at', date('Y-m-d'))->count() + 1;

        return 'SLS-' . $date . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public static function createOrder($params, $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id() ?? ($params['user_id'] ?? null);

            if (!$userId) {
                throw new \Exception('User is required to create sale');
            }

            $sale = self::create([
                'number' => self::generateNumber(),
                'date' => $params['date'],
                'user_id' => $userId,
            ]);

            foreach ($params['items'] as $item) {
                $inventory = Inventories::findOrFail($item['inventory_id']);

                Inventories::decreaseStock($inventory->id, $item['qty']);

                SalesDetails::create([
                    'sales_id' => $sale->id,
                    'inventory_id' => $inventory->id,
                    'qty' => $item['qty'],
                    'price' => $inventory->price,
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Succesfully Created Order',
                'data' => $sale,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public static function getByIdWithDetails($id, $params, $request)
    {
        // Get sales header
        $sale = self::getById($id, $params, $request)->original;

        // Get sales details
        $sale->details = SalesDetails::getBySalesId($id, $request);

        return response()->json($sale);
    }
}
