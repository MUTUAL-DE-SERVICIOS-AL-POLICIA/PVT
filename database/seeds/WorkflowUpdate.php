<?php

use Illuminate\Database\Seeder;

class WorkflowUpdate extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try {
            DB::beginTransaction();

            DB::table('wf_sequences')
                ->whereIn('id', [228, 229, 231])
                ->delete();

            DB::table('wf_sequences')
                ->where('id', 227)
                ->update(['wf_state_next_id' => 81]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
