<?php

use Illuminate\Support\Facades\DB;

function insert_in_history_table($action, $row_id, $table_name)
{
    logger(ucfirst($table_name) . ' : (' . auth()->user()->full_name . ' ' . auth()->user()->id . ') ' . $action . ' ' . $row_id);

    DB::table('history')->insert(
        [
            'user_id' => auth()->user()->id,
            'action' => $action,
            'row_id' => $row_id,
            'table_name' => $table_name,
            'created_at' => now(),
        ],
    );
}
