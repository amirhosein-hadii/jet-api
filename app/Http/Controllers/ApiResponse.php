<?php


namespace App\Http\Controllers;


use App\language\errors;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class ApiResponse
{
    static function Json($status, $msg, $data = [], $statusSystem = 200)
    {
        $e = new errors();

        return response()->json([
            'status' => $status,
            'message' => $e->errorGet('fa', $msg),
            'data' => $data

        ], $statusSystem);
    }


    static public function convertPersianNums($number)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $number);
        return $convertedPersianNums;
    }

    static public function paginate($items, $perPage = 10, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $offset = ($page * $perPage) - $perPage;
        $itemsForCurrentPage = array_slice($items->toArray(), $offset, $perPage, true);
        return new LengthAwarePaginator(array_values($itemsForCurrentPage ), $items->count(), $perPage, $page, $options);
    }
}
