<?php

namespace App\Http\Controllers;

use App\Models\PhoneModelResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 手机型号查询报表控制器
 *
 * 提供日报页面: 展示每日查询数、命中数、命中率统计
 *
 * @package App\Http\Controllers
 */
class PhoneReportController extends Controller
{
    /**
     * 机型筛选日报页面
     *
     * GET /reports/daily
     * 仅渲染页面骨架, 数据由前端异步请求 GET /reports/daily/data 获取
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function daily(Request $request)
    {
        return view('reports.daily');
    }

    /**
     * 机型筛选日报数据接口 (异步请求)
     *
     * GET /reports/daily/data
     * 返回今日数据 + 日报明细(分页) + 日期筛选条件
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyData(Request $request)
    {
        // 筛选日期(默认展示全部)
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $stats = $this->aggregateByDate($startDate, $endDate);

        // 今日数据(基于明细表按 created_at 聚合)
        $today = Carbon::today();
        $todayQuery = PhoneModelResult::whereDate('created_at', $today);
        $todayTotal = (clone $todayQuery)->count();
        $todayHit   = (clone $todayQuery)->where('matched', 1)->count();
        $todayRate  = $todayTotal > 0 ? round($todayHit / $todayTotal * 100, 2) : 0.00;

        // 分页
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 10;
        }
        $page = max(1, (int) $request->input('page', 1));

        $total = count($stats);
        $paged = array_slice($stats, ($page - 1) * $perPage, $perPage);
        $lastPage = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

        return response()->json([
            'code'    => 0,
            'message' => 'ok',
            'data'    => [
                'today'     => [
                    'total' => $todayTotal,
                    'hit'   => $todayHit,
                    'rate'  => $todayRate,
                ],
                'list'      => array_values($paged),
                'pagination' => [
                    'total'     => $total,
                    'per_page'  => $perPage,
                    'current'   => $page,
                    'last_page' => $lastPage,
                ],
                'filters'   => [
                    'start_date' => $startDate,
                    'end_date'   => $endDate,
                ],
            ],
        ]);
    }

    /**
     * 按日期聚合查询数 / 命中数
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    protected function aggregateByDate($startDate = null, $endDate = null)
    {
        $query = PhoneModelResult::query()
            ->select(
                DB::raw('DATE(created_at) as stat_date'),
                DB::raw('COUNT(*) as query_count'),
                DB::raw('SUM(CASE WHEN matched = 1 THEN 1 ELSE 0 END) as hit_count')
            )
            ->groupBy('stat_date');

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $rows = $query->orderBy('stat_date', 'desc')->get();

        return $rows->map(function ($row) {
            $total = (int) $row->query_count;
            $hit = (int) $row->hit_count;
            return [
                'stat_date'   => $row->stat_date,
                'query_count' => $total,
                'hit_count'   => $hit,
                'hit_rate'    => $total > 0 ? round($hit / $total * 100, 2) : 0.00,
            ];
        })->all();
    }
}
