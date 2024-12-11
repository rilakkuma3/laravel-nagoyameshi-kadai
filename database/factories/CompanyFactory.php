<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Company;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'テスト', // 会社名
            'postal_code' => '0000000', // 郵便番号
            'address' => 'テスト', // 所在地
            'representative' => 'テスト', // 代表者
            'establishment_date' => 'テスト', // 設立年月日
            'capital' => 'テスト', // 資本金
            'business' => 'テスト', // 事業内容
            'number_of_employees' => 'テスト', // 従業員数
        ];
    }
}
