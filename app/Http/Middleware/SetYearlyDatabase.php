<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SetYearlyDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract the year from the URL (assuming the year is in the first URL segment)
        $year = $request->segment(1) ?? '2024';

        $connection = preg_match('/^20\d{2}$/', $year) ? 'wcm_' . $year : 'wcm_2024';

        try {
            // Attempt to establish a connection
            DB::connection($connection)->getPdo();
        } catch (QueryException $e) {
            return response()->json(['status' => 'error', 'message' => "Could not connect to the database: " . $e->getMessage()]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => "An error occurred: " . $e->getMessage()]);
        }

        // Set the correct database for the year dynamically
        Config::set('database.default', $connection);

        DB::purge();  // Clear all previous connections
        DB::reconnect($connection);  // Reconnect with the new DB

        return $next($request);
    }
}
