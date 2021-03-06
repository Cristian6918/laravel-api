<?php

use Illuminate\Database\Seeder;
use App\Category;
use Illuminate\Support\Str;

class AddCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['Medicine', 'Science'];
        foreach ($categories as $category) {

            $new_category_object = new Category();
            $new_category_object->name = $category;
            $new_category_object->slug = Str::slug($category);
            $new_category_object->save();
        }
    }
}
