<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionImport implements ToCollection, WithMultipleSheets
{
    public function __construct(protected int|string $sub_category_id)
    {
    }

    public function sheets(): array
    {
        return [
            1 => $this, // 0 is the index for the first sheet
        ];
    }
    public function collection(Collection $collection)
    {
        $filteredCollection = $collection->filter(function ($row) {
            return $row->filter()->isNotEmpty();
        });

        DB::transaction(function () use ($filteredCollection)  {

            $filteredCollection->each(function ($row) {

                if($row[1]===null){
                    return;
                }

                $question = Question::create([
                    'sub_category_id' => $this->sub_category_id,
                    'is_active' => true,
                    'number' => trim($row[0]),
                    'question' => trim($row[1]),
                ]);


                foreach (range(2, 5) as $index) {

                    $question->questionOptions()->create([
                        'option' => trim($row[$index]),
                        'is_answer' => trim($row[$index])[0] === $row[6] ? true : false,
                    ]);
                }

         

            });

        });
    }
}
