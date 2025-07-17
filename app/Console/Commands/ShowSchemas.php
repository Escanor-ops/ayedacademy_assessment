<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class ShowSchemas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:showschemas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows all tables schema in the database';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Get all table names
        $tables = DB::select('SELECT table_name FROM information_schema.tables WHERE table_schema = ?', [DB::getDatabaseName()]);

        foreach ($tables as $table) {
            $tableName = $table->table_name;
            $this->info("Schema for table: $tableName");
            
            // Get the structure for each table
            $structure = DB::select('DESCRIBE ' . $tableName);

            foreach ($structure as $column) {
                $this->line(
                    "{$column->Field} | {$column->Type} | {$column->Null} | {$column->Key} | {$column->Default} | {$column->Extra}"
                );
            }

            $this->line("\n"); // Space between tables
        }
    }
}
