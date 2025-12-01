<?php

namespace App\Http\Controllers;

use App\Models\CaseStage;
use App\Models\LegalCase\CaseType;
use App\Models\LegalCase\LegalCase;
use App\Models\LegalCase\LegalCaseLawyer;
use App\Models\LegalCase\NatureOfClaim;
use App\Models\LegalFees\ContingentLiability;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAssignees;
use Carbon\Carbon;
use App\Models\User;
use DateTime;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{

    public function get_contingency_report(Request $request)
    {
        $lawyer_id = $request->lawyer_id;
        $start_date = $request->start_date;
        $end_date  = $request->end_date;
        $all_time = $request->all_time;

        if ($all_time) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-12';
        }

        //return $start_date.' '.$end_date;
        $lawyer_query_1 = $lawyer_id ? " and lc.lawyer_id='$lawyer_id'" : "";
        $lawyer_query_2 = $lawyer_id ? " and legal_cases.lawyer_id='$lawyer_id'" : "";

        $query = "SELECT
        lc.id,
        lc.serial_number,
        lc.title,
        lc.case_number,
        lc.is_internal,
        lc.date_received,
        lc.date_of_filing,
        lc.completion_date,
        lc.court_name,
        lc.year,
        lc.description,
        lc.case_type_id,
        lc.case_stage_id,
        lc.nature_of_claim_id,
        lc.lawyer_id,
        SUM(cl.amount) AS total_contingent_liability,

        noc.claim AS nature_of_claim,
        cs.name AS case_stage,
        ct.name AS case_type

        FROM
        legal_cases lc
        JOIN contingent_liabilities cl ON lc.id = cl.legal_case_id
        LEFT JOIN  nature_of_claims noc ON lc.nature_of_claim_id = noc.id
        LEFT JOIN case_stages cs ON lc.case_stage_id = cs.id
        LEFT JOIN case_types ct ON lc.case_type_id = ct.id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date' $lawyer_query_1
        GROUP BY lc.id,lc.serial_number,
        lc.title,lc.case_number,
        lc.is_internal,lc.date_received,
        lc.date_of_filing,lc.completion_date,
        lc.court_name,lc.year,lc.description,lc.case_type_id,lc.case_stage_id,lc.nature_of_claim_id,lc.lawyer_id,
        noc.id,noc.claim, cs.id,cs.name, ct.id,ct.name";

        $fee_query = "select SUM(amount) AS total_contingent_liability
        FROM contingent_liabilities
        JOIN legal_cases on legal_cases.id=contingent_liabilities.legal_case_id
        WHERE legal_cases.date_of_filing BETWEEN '$start_date' AND '$end_date' $lawyer_query_2";

        $legal_cases =  DB::select($query);
        $fee_query = DB::selectOne($fee_query);

        return [
            "legal_cases" => $legal_cases,
            "contigent_fee" => $fee_query
        ];
    }

    public function cases_report(Request $request)
    {
        // === SAFELY parse comma-separated values ===
        $lawyer_ids = $request->filled('lawyer_ids')
            ? array_filter(explode(',', $request->lawyer_ids))
            : [];
    
        $has = $request->filled('has')
            ? array_filter(explode(',', $request->has))
            : [];
    
        $does_not_have = $request->filled('does_not_have')
            ? array_filter(explode(',', $request->does_not_have))
            : [];
    
        // === Date handling ===
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $all_time = $request->boolean('all_time', false);
    
        if ($all_time || !$start_date || !$end_date) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-31';
        }
    
        // === Other filters with proper defaults ===
        $case_stage = $request->input('case_stage', -1);
        $nature_of_claim = $request->input('nature_of_claim', -1);
        $case_type = $request->input('case_type', -1);
        $is_internal = $request->input('is_internal', -1);
    
        // === Base query ===
        $query = LegalCase::withTrashed()
            ->with([
                'filed_by',
                'lawyers',
                'contingent_liability',
                'case_type',
                'nature_of_claim',
                'case_stage',
                'interim_fee_note',
                'final_fee_note',
                'judgement_attachments',
                'dg_approval_attachments',
                'procurement_authority_documents',
                'sla'
            ]);
    
        // === Apply filters only when meaningful values exist ===
        if (!empty($lawyer_ids)) {
            $lawyer_cases_ids = LegalCaseLawyer::whereIn('lawyer_id', $lawyer_ids)->pluck('legal_case_id');
            $filed_by_cases_ids = LegalCase::whereIn('user_id', $lawyer_ids)->pluck('id');
            $ids = $lawyer_cases_ids->merge($filed_by_cases_ids)->unique();
            $query->whereIn('id', $ids);
        }
    
        if ($case_stage != -1) {
            $query->where('case_stage_id', $case_stage);
        }
        if ($case_type != -1) {
            $query->where('case_type_id', $case_type);
        }
        if ($nature_of_claim != -1) {
            $query->where('nature_of_claim_id', $nature_of_claim);
        }
        if ($is_internal != -1) {
            $query->where('is_internal', $is_internal);
        }
    
        // === Document existence filters ===
        $hasFilters = [
            '1' => 'dg_approval_attachments',
            '2' => 'procurement_authority_documents',
            '3' => 'sla',
            '4' => 'interim_fee_note',
            '5' => 'judgement_attachments',
            '6' => 'final_fee_note'
        ];
    
        foreach ($has as $filter) {
            if (isset($hasFilters[$filter])) {
                $query->whereHas($hasFilters[$filter]);
            }
        }
    
        foreach ($does_not_have as $filter) {
            if (isset($hasFilters[$filter])) {
                $query->whereDoesntHave($hasFilters[$filter]);
            }
        }
    
        // === Date range ===
        $query->whereBetween('date_of_filing', [
            Carbon::parse($start_date)->startOfDay(),
            Carbon::parse($end_date)->endOfDay(),
        ]);
    
        $items = $query->get();
    
        $total_contingent_liability = ContingentLiability::whereIn('legal_case_id', $items->pluck('id'))->sum('amount');
    
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportToCSV($items);
        }
    
        return Inertia::render('reports/CaseReport', [
            'items' => $items,
            'total_contingent_liability' => $total_contingent_liability,
            'case_stages' => CaseStage::all(),
            'lawyers' => User::whereIn('role_id', [2, 3])->get(),
            'nature_of_claims' => NatureOfClaim::all(),
            'case_types' => CaseType::all(),
            'filters' => [
                'start_date' => $request->start_date ?? '',
                'end_date' => $request->end_date ?? '',
                'all_time' => $all_time,
                'case_stage' => $case_stage,
                'nature_of_claim' => $nature_of_claim,
                'case_type' => $case_type,
                'is_internal' => $is_internal,
                'lawyer_ids' => $lawyer_ids,
                'has' => $has,
                'does_not_have' => $does_not_have,
            ]
        ]);
    }
    
    private function exportToCSV($items)
    {
        $fileName = 'case_reports_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $fileName,
        ];
    
        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
            
            // Headers
            fputcsv($file, [
                'ID',
                'Case Title',
                'Status',
                'Amount',
                'Case Type',
                'Nature of Claim',
                'Case Stage',
                'Internal/External',
                'Date of Filing',
                'Updated At'
            ]);
    
            // Data
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->title,
                    $item->status,
                    $item->amount,
                    $item->case_type->name ?? 'N/A',
                    $item->nature_of_claim->name ?? 'N/A',
                    $item->case_stage->name ?? 'N/A',
                    $item->is_internal ? 'Internal' : 'External',
                    $item->date_of_filing,
                    $item->updated_at
                ]);
            }
    
            fclose($file);
        };
    
        return response()->stream($callback, 200, $headers);
    }


    
    public function cases_by_lawyer_report(Request $request)
    {
        // === Date Range Handling ===
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');
        $all_time   = $request->boolean('all_time', false);
    
        if ($all_time || !$start_date || !$end_date) {
            $start_date = '1970-01-01';
            $end_date   = '2090-12-31';
        }
    
        $start = Carbon::parse($start_date)->startOfDay();
        $end   = Carbon::parse($end_date)->endOfDay();
    
        // === Get all lawyers (role 2 = external, 3 = internal?) ===
        $lawyers = User::whereIn('role_id', [2, 3])
            ->select('id', 'name', 'email')
            ->get();
    
        $case_stages = CaseStage::withTrashed()
            ->select('id', 'name')
            ->orderBy('id')
            ->get();
    
        foreach ($lawyers as $lawyer) {
            // Get all case IDs assigned to this lawyer (via pivot + filed_by)
            $assigned_case_ids = LegalCaseLawyer::where('lawyer_id', $lawyer->id)
                ->pluck('legal_case_id');
    
            $filed_by_case_ids = LegalCase::where('user_id', $lawyer->id)
                ->pluck('id');
    
            $case_ids = $assigned_case_ids->merge($filed_by_case_ids)->unique();
    
            // Total cases in date range
            $total_cases = LegalCase::whereIn('id', $case_ids)
                ->whereBetween('date_of_filing', [$start, $end])
                ->count();
    
            $lawyer->total_cases = $total_cases;
    
            // Breakdown by case stage
            $stage_breakdown = [];
            foreach ($case_stages as $stage) {
                $count = LegalCase::whereIn('id', $case_ids)
                    ->where('case_stage_id', $stage->id)
                    ->whereBetween('date_of_filing', [$start, $end])
                    ->count();
    
                $stage_breakdown[] = [
                    'stage_name' => $stage->name,
                    'count'      => $count,
                ];
            }
    
            $lawyer->stage_breakdown = $stage_breakdown;
        }
    
        // === CSV Export ===
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportLawyerReportToCSV($lawyers, $case_stages);
        }
    
        return Inertia::render('reports/CasesByLawyer', [
            'lawyers'       => $lawyers,
            'case_stages'   => $case_stages,
            'filters'       => [
                'start_date' => $request->start_date ?? '',
                'end_date'   => $request->end_date ?? '',
                'all_time'   => $all_time,
            ]
        ]);
    }
    
    // === CSV Export Helper ===
    private function exportLawyerReportToCSV($lawyers, $case_stages)
    {
        $headers = [
            'Lawyer Name',
            'Total Cases',
        ];
    
        foreach ($case_stages as $stage) {
            $headers[] = $stage->name;
        }
    
        $callback = function () use ($lawyers, $case_stages, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
    
            foreach ($lawyers as $lawyer) {
                $row = [
                    $lawyer->name,
                    $lawyer->total_cases,
                ];
    
                foreach ($lawyer->stage_breakdown as $breakdown) {
                    $row[] = $breakdown['count'];
                }
    
                fputcsv($file, $row);
            }
    
            fclose($file);
        };
    
        return response()->streamDownload($callback, 'cases_by_lawyer_report_' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function cases_by_status_report(Request $request)
    {
        $user_id = $request->user()->id;
        $role_id = $request->user()->role_id;

        $start_date = $request->start_date;
        $end_date  = $request->end_date;
        $all_time = $request->all_time;

        if ($all_time) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-12';
        }

        $case_stages = CaseStage::withTrashed()->select("id", "name")->get();
        foreach ($case_stages as $value) {
            $count = LegalCase::whereBetween('date_of_filing', [
                Carbon::parse($start_date)->toDateString(),
                Carbon::parse($end_date)->toDateString()
            ])->where("case_stage_id", $value->id);
            if ($role_id != 1) {
                $count = $count->where('lawyer_id', $user_id);
            }
            $count = $count->count();
            $value->count = $count;
        }

        return $case_stages;
    }


    public function cases_by_nature_of_claim_report(Request $request)
    {
        $user_id = $request->user()->id;
        $role_id = $request->user()->role_id;

        $start_date = $request->start_date;
        $end_date  = $request->end_date;
        $all_time = $request->all_time;

        if ($all_time) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-12';
        }

        $cases_by_nature_of_claim = DB::select("
        SELECT  noc.claim AS nature_of_claim,
         COUNT(lc.id) AS case_count FROM
         legal_cases lc
         LEFT JOIN
         nature_of_claims noc ON lc.nature_of_claim_id = noc.id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date'
         GROUP BY
         noc.claim;
        ");

        return $cases_by_nature_of_claim;
    }



    public function general_report(Request $request)
    {
        $user_id = $request->user()->id;
        $role_id = $request->user()->role_id;
        //return $role_id;

        $all_time = $request->all_time;
        $start_date = $request->start_date;
        $end_date  = $request->end_date;
        $periodFilter = $request->pf;
        $all_time = $request->all_time;

        if ($all_time) {
            $start_date = '1970-01-01';
            $end_date = '2090-12-12';
        }

        $internal_cases_count = LegalCase::where('is_internal', 1)
            ->whereBetween('date_of_filing', [
                Carbon::parse($start_date)->toDateString(),
                Carbon::parse($end_date)->toDateString()
            ]); ///->count();

        if ($role_id != 1) { //admin
            $internal_cases_count = $internal_cases_count->where('lawyer_id', $user_id);
        }
        $external_cases_count = LegalCase::where('is_internal', 0)
            ->whereBetween('date_of_filing', [
                Carbon::parse($start_date)->toDateString(),
                Carbon::parse($end_date)->toDateString()
            ]); //->count();

        if ($role_id != 1) { //admin
            $external_cases_count = $external_cases_count->where('lawyer_id', $user_id);
        }
        $internal_cases_count = $internal_cases_count->count();
        $external_cases_count = $external_cases_count->count();


        $task_ids = Task::where("user_id", $user_id)->pluck("id")->toArray();
        $task_assignees_id = TaskAssignees::where("user_id", $user_id)->pluck("user_id")->toArray();

        foreach ($task_assignees_id as $id) {
            array_push($task_ids, $id);
        }

        $items = Task::with("assignees", "attachments", "legal_case")->whereIn("id", $task_ids)->limit(10)->orderBy('created_at', 'desc')->get();

        $case_stages = [];

        $case_stage_query = '';
        $case_count_query = '';

        if ($periodFilter == "Year") {
            $datetime = new DateTime($start_date);
            $year = $datetime->format('Y');

            $case_stage_query = "
            WITH months_in_year AS (
                SELECT CONCAT($year, '-01-01') AS start_date
                UNION ALL SELECT CONCAT($year, '-02-01')
                UNION ALL SELECT CONCAT($year, '-03-01')
                UNION ALL SELECT CONCAT($year, '-04-01')
                UNION ALL SELECT CONCAT($year, '-05-01')
                UNION ALL SELECT CONCAT($year, '-06-01')
                UNION ALL SELECT CONCAT($year, '-07-01')
                UNION ALL SELECT CONCAT($year, '-08-01')
                UNION ALL SELECT CONCAT($year, '-09-01')
                UNION ALL SELECT CONCAT($year, '-10-01')
                UNION ALL SELECT CONCAT($year, '-11-01')
                UNION ALL SELECT CONCAT($year, '-12-01')
            ),
            available_months AS (
                SELECT
                    DATE_FORMAT(start_date, '%M %Y') AS month_label,
                    YEAR(start_date) AS year_val,
                    MONTH(start_date) AS month_num
                FROM months_in_year
            ),
            case_counts AS (
                SELECT
                    YEAR(lc.date_of_filing) AS year_val,
                    MONTH(lc.date_of_filing) AS month_num,
                    cs.name AS case_stage_name,
                    COUNT(DISTINCT lc.id) AS case_count
                FROM legal_cases lc
                LEFT JOIN case_stages cs ON lc.case_stage_id = cs.id
                WHERE lc.date_of_filing BETWEEN CONCAT($year, '-01-01') AND CONCAT($year, '-12-31')
                GROUP BY YEAR(lc.date_of_filing), MONTH(lc.date_of_filing), cs.name
            )
            SELECT
                am.month_label AS x_value,
                COALESCE(cc.case_stage_name, '') AS label,
                COALESCE(cc.case_count, 0) AS y_value
            FROM available_months am
            LEFT JOIN case_counts cc
                ON am.year_val = cc.year_val
                AND am.month_num = cc.month_num
            ORDER BY am.year_val, am.month_num, cc.case_stage_name;
            ";

            $case_count_query = "
           WITH months_in_year AS (
                SELECT CONCAT($year, '-01-01') AS start_date
                UNION ALL SELECT CONCAT($year, '-02-01')
                UNION ALL SELECT CONCAT($year, '-03-01')
                UNION ALL SELECT CONCAT($year, '-04-01')
                UNION ALL SELECT CONCAT($year, '-05-01')
                UNION ALL SELECT CONCAT($year, '-06-01')
                UNION ALL SELECT CONCAT($year, '-07-01')
                UNION ALL SELECT CONCAT($year, '-08-01')
                UNION ALL SELECT CONCAT($year, '-09-01')
                UNION ALL SELECT CONCAT($year, '-10-01')
                UNION ALL SELECT CONCAT($year, '-11-01')
                UNION ALL SELECT CONCAT($year, '-12-01')
            ),
            available_months AS (
                SELECT
                    DATE_FORMAT(start_date, '%M %Y') AS month_label,
                    YEAR(start_date) AS year_val,
                    MONTH(start_date) AS month_num
                FROM months_in_year
            ),
            case_counts AS (
                SELECT
                    YEAR(lc.date_of_filing) AS year_val,
                    MONTH(lc.date_of_filing) AS month_num,
                    CASE
                        WHEN lc.is_internal = 0 THEN 'External'
                        ELSE 'Internal'
                    END AS label,
                    COUNT(DISTINCT lc.id) AS case_count
                FROM legal_cases lc
                WHERE lc.date_of_filing BETWEEN CONCAT($year, '-01-01') AND CONCAT($year, '-12-31')
                GROUP BY YEAR(lc.date_of_filing), MONTH(lc.date_of_filing), label
            )
            SELECT
                am.month_label AS x_value,
                COALESCE(cc.label, 'No Data') AS label,
                COALESCE(cc.case_count, 0) AS y_value
            FROM available_months am
            LEFT JOIN case_counts cc
                ON am.year_val = cc.year_val
                AND am.month_num = cc.month_num
            ORDER BY am.year_val, am.month_num, cc.label;
            ";
        } else if ($periodFilter == "Quarter") {
            $case_stage_query = "

            WITH RECURSIVE months_in_range AS (
                SELECT DATE_FORMAT('$start_date', '%Y-%m-01') AS start_date
                UNION ALL
                SELECT DATE_ADD(start_date, INTERVAL 1 MONTH)
                FROM months_in_range
                WHERE start_date < DATE_FORMAT('$end_date', '%Y-%m-01')
            ),
            available_months AS (
                SELECT
                    DATE_FORMAT(start_date, '%M %Y') AS month_label,
                    YEAR(start_date) AS year_val,
                    MONTH(start_date) AS month_num
                FROM months_in_range
            )
            SELECT
                am.month_label AS x_value,
                COALESCE(cs.name, 'No Stage') AS label,
                COUNT(DISTINCT lc.id) AS y_value
            FROM
                available_months am
            LEFT JOIN
                legal_cases lc
                ON MONTH(lc.date_of_filing) = am.month_num
                AND YEAR(lc.date_of_filing) = am.year_val
            LEFT JOIN
                case_stages cs
                ON lc.case_stage_id = cs.id
            GROUP BY
                am.month_label, cs.name
            ORDER BY
            am.year_val, am.month_num, cs.name;";

            $case_count_query = "
            WITH RECURSIVE months_in_range AS (
                SELECT DATE_FORMAT('$start_date', '%Y-%m-01') AS start_date
                UNION ALL
                SELECT DATE_ADD(start_date, INTERVAL 1 MONTH)
                FROM months_in_range
                WHERE start_date < DATE_FORMAT('$end_date', '%Y-%m-01')
            ),
            available_months AS (
                SELECT
                    DATE_FORMAT(start_date, '%M %Y') AS month_label,
                    YEAR(start_date) AS year_val,
                    MONTH(start_date) AS month_num
                FROM months_in_range
            )
            SELECT
                am.month_label AS x_value,
                CASE
                    WHEN lc.is_internal = 0 THEN 'External'
                    ELSE 'Internal'
                END AS label,
                COUNT(DISTINCT lc.id) AS y_value
            FROM
                available_months am
            LEFT JOIN
                legal_cases lc
                ON MONTH(lc.date_of_filing) = am.month_num
                AND YEAR(lc.date_of_filing) = am.year_val
            GROUP BY
                am.month_label, label
            ORDER BY am.year_val, am.month_num, label;
            ";
            //return $query;
        } else if ($periodFilter == "Date" || $periodFilter == "Range" || $periodFilter == "Month" || $periodFilter == "Week") {
            $case_stage_query = "
            WITH RECURSIVE date_range AS (
                    SELECT '$start_date' AS case_date
                    UNION ALL
                    SELECT DATE_ADD(case_date, INTERVAL 1 DAY)
                    FROM date_range
                    WHERE case_date < '$end_date'
                )
                SELECT
                    dr.case_date as x_value,
                    cs.name AS label,
                    COUNT(lc.id) AS y_value
                FROM
                    date_range dr
                LEFT JOIN
                    legal_cases lc ON DATE(lc.date_of_filing) = dr.case_date
                LEFT JOIN
                    case_stages cs ON lc.case_stage_id = cs.id
                GROUP BY
                    dr.case_date, cs.name,cs.id
                ORDER BY dr.case_date, cs.name,cs.id";

            $case_count_query = "
            WITH RECURSIVE date_range AS (
                SELECT '$start_date' AS case_date
                UNION ALL
                SELECT DATE_ADD(case_date, INTERVAL 1 DAY)
                FROM date_range
                WHERE case_date < '$end_date'
            )
            SELECT
                dr.case_date AS x_value,
                CASE
                    WHEN lc.is_internal = 0 THEN 'External'
                    ELSE 'Internal'
                END AS label,
                COUNT(lc.id) AS y_value
            FROM
                date_range dr
            LEFT JOIN
                legal_cases lc ON DATE(lc.date_of_filing) = dr.case_date
            GROUP BY
                dr.case_date, label
            ORDER BY dr.case_date, label;";
        }

        if ($all_time) {
            $yearsDistinctQuery = DB::select('
            SELECT DISTINCT YEAR(date_of_filing) AS year
            FROM legal_cases
            WHERE date_of_filing IS NOT NULL
            ORDER BY year;');

            $years = array_map(function ($case) {
                return $case->year;
            }, $yearsDistinctQuery);

            if (count($years) == 1) {
                $years = range($years[0] - 3, $years[0]);
            } else if (count($years) == 0) {
                $currentYear = date('Y');
                $years = range($currentYear - 3, $currentYear);
            }
            $year = $years[0];

            $case_stage_query = "
            WITH RECURSIVE years_range AS (
                SELECT $year AS year_val
                UNION ALL
                SELECT year_val + 1
                FROM years_range
                WHERE year_val <= YEAR(CURRENT_DATE) + 1
            ),
            case_counts AS (
                SELECT
                    YEAR(lc.date_of_filing) AS year_val,
                    cs.name AS case_stage_name,
                    COUNT(DISTINCT lc.id) AS case_count
                FROM legal_cases lc
                LEFT JOIN case_stages cs ON lc.case_stage_id = cs.id
                GROUP BY YEAR(lc.date_of_filing), cs.name
            )
            SELECT
                yr.year_val AS x_value,
                COALESCE(cc.case_stage_name, 'No Stage') AS label,
                COALESCE(cc.case_count, 0) AS y_value
            FROM years_range yr
            LEFT JOIN case_counts cc
                ON yr.year_val = cc.year_val
            ORDER BY yr.year_val, cc.case_stage_name;";

            $case_count_query = "
            WITH RECURSIVE years_range AS (
                SELECT $year AS year_val
                UNION ALL
                SELECT year_val + 1
                FROM years_range
                WHERE year_val < YEAR(CURRENT_DATE)
            ),
            case_counts AS (
                SELECT
                    YEAR(lc.date_of_filing) AS year_val,
                    CASE
                        WHEN lc.is_internal = 0 THEN 'External'
                        ELSE 'Internal'
                    END AS label,
                    COUNT(DISTINCT lc.id) AS case_count
                FROM legal_cases lc
                WHERE lc.date_of_filing IS NOT NULL
                GROUP BY YEAR(lc.date_of_filing), label
            )
            SELECT
                yr.year_val AS x_value,
                COALESCE(cc.case_count, 0) AS y_value,
                COALESCE(cc.label, 'No Stage') AS label
            FROM years_range yr
            LEFT JOIN case_counts cc
                ON yr.year_val = cc.year_val
            ORDER BY yr.year_val, cc.label;";
        }

        $case_stages = DB::select($case_stage_query);
        $case_count = DB::select($case_count_query);

        $cases_by_nature_of_claim = DB::select("
        SELECT  noc.claim AS nature_of_claim,
         COUNT(lc.id) AS case_count FROM
         legal_cases lc
         LEFT JOIN
         nature_of_claims noc ON lc.nature_of_claim_id = noc.id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date'
         GROUP BY
         noc.claim;
        ");

        $cases_by_lawyer = DB::select("
        SELECT CONCAT(
        COALESCE(l.first_name, ''),
        ' ',
        COALESCE(l.middle_name, ''),
        ' ',
        COALESCE(l.last_name, '')
    ) AS lawyer_name,
        COUNT(lcl.legal_case_id) AS case_count
        FROM
        users l
        INNER JOIN
        legal_case_lawyers lcl ON l.id = lcl.lawyer_id
        INNER JOIN
        legal_cases lc ON lc.id = lcl.legal_case_id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date' and l.is_external_counsel=0
        GROUP BY
        lawyer_name;
        ");

        $cases_by_external_advocate = DB::select("
        SELECT CONCAT(
        COALESCE(l.first_name, ''),
        ' ',
        COALESCE(l.middle_name, ''),
        ' ',
        COALESCE(l.last_name, '')
    ) AS firm_name,
        COUNT(lcl.legal_case_id) AS case_count
        FROM
        users l
        INNER JOIN
        legal_case_lawyers lcl ON l.id = lcl.lawyer_id
        INNER JOIN
        legal_cases lc ON lc.id = lcl.legal_case_id
        WHERE lc.date_of_filing BETWEEN '$start_date' AND '$end_date' and l.is_external_counsel=1
        GROUP BY
        firm_name;
        ");

        return [
            "cases_count" => $case_count,
            "cases_by_status" => $case_stages,
            "cases_by_nature_of_claim" => $cases_by_nature_of_claim,
            "cases_by_external_advocate" => $cases_by_external_advocate,
            "cases_by_lawyer" => $cases_by_lawyer,
            "tasks" => $items,
        ];
    }

    public function presets(Request $request)
    {
        $lawyers = User::where('role_id', 2)->orWhere("is_external_counsel", 1)->get();
        $case_types =  CaseType::all();
        $case_stages = [];

        $nature_of_claim = NatureOfClaim::all();

        foreach (CaseStage::all() as $value) {
            array_push($case_stages, [
                "label" => $value->name,
                "value" => $value->id
            ]);
        }

        return response()->json(
            [
                "case_types" => $case_types,
                "nature_of_claim" => $nature_of_claim,
                "lawyers" => $lawyers,
                "statuses" => $case_stages,
            ],
            200
        );
    }
}
