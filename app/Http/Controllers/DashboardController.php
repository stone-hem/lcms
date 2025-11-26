<?php

namespace App\Http\Controllers;

use App\Models\LegalCase\LegalCase;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskAssignees;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use DateTime;



class DashboardController extends Controller
{

    public function my_analytics(Request $request)
    {
        $user_id = $request->user()->id;
               $role_id = $request->user()->role_id;
               //return $role_id;

               $all_time = $request->all_time;
               $start_date = $request->start_date;
               $end_date  = $request->end_date;
               $periodFilter = $request->pf;
               $all_time = $request->all_time;
               
               if (!$periodFilter) {
                   $periodFilter = "Year";
               }

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


            return Inertia::render('Dashboard',[
                        "cases_count" => $case_count,
                        "cases_by_status" => $case_stages,
                        "cases_by_nature_of_claim" => $cases_by_nature_of_claim,
                        "cases_by_external_advocate" => $cases_by_external_advocate,
                        "cases_by_lawyer" => $cases_by_lawyer,
                        "tasks" => $items,
                    ],
            );
    }

    public function presets(Request $request)
    {
        return response()->json(
            [
                "error" => false,
                "presets" => [],
            ],
            200
        );
    }
}
