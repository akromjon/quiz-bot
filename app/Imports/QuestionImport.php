<?php

namespace App\Imports;

use App\Models\Question;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class QuestionImport implements ToCollection, WithMultipleSheets
{
    public function __construct(protected int|string $sub_category_id, protected int $sheet_number)
    {
    }

    public function sheets(): array
    {
        return [
            $this->sheet_number-1 => $this,
        ];
    }
    public function collection(Collection $collection)
    {
        $filteredCollection = $collection->filter(fn($row) => $row->filter()->isNotEmpty());


        Question::where('sub_category_id', $this->sub_category_id)->delete();

        DB::transaction(function () use ($filteredCollection) {
            
            $questionNumber = 1;
            
            $answersMap = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
            
            $reversedAnswersMap = array_flip($answersMap);

            $filteredCollection->each(function ($row) use (&$questionNumber, $answersMap, $reversedAnswersMap) {
               
                $question = $this->createQuestion($row, $questionNumber++);

                foreach (range(1, 4) as $index) {
                    $this->createQuestionOption($question, $row, $index, $reversedAnswersMap, $answersMap);
                }
            });
        });

        DB::commit();
    }

    private function createQuestion($row, $questionNumber):Question
    {
        return Question::create([
            'sub_category_id' => $this->sub_category_id,
            'is_active' => true,
            'number' => $questionNumber,
            'question' => trim($row[0]),
        ]);
    }

    private function createQuestionOption(Question $question, Collection $row, int $index, array $reversedAnswersMap, array $answersMap):void
    {
        $question->questionOptions()->create([
            'option' => $reversedAnswersMap[$index] . ") " . trim($row[$index]),
            'is_answer' => $answersMap[trim($row[5])] === $index,
        ]);
    }

}
