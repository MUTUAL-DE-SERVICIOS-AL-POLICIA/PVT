<?php

use Illuminate\Database\Seeder;
use App\Tag;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $shortened[1]='Mora';
        $shortened[2]='Amortizando';
        $tags = [
            ['name' => 'Retraso de Pago', 'shortened' => $shortened[1],'slug' => Str::slug($shortened[1],'-')],
            ['name' => 'Pagando Cuotas', 'shortened' => $shortened[2],'slug' => Str::slug($shortened[2],'-')]
        ];
        foreach ($tags as $tag) {
            Tag::firstOrCreate($tag);
        } 
    }
}
