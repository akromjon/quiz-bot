<?php

use App\Imports\QuestionImport;
use App\Models\SubCategory;
use App\Models\TelegramUser;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelExcel;

// it('it imports Questions from ExcelSheet', function () {

//     $file = storage_path('8-sinf.xlsx');

//     // dd($file);

//     $this->assertFileExists($file);

//     Excel::import(new QuestionImport(108,1), $file,readerType: ExcelExcel::XLSX);

//     $this->assertTrue(true);

// });

it("it creates a question history", function () {

    $user = TelegramUser::first();

    $sub_category_id = SubCategory::first()->id;

    $question_id = null;

    $history = $user->history()->where('sub_category_id', $sub_category_id)->first();

    if (!$history) {

        $history=$user->history()->create([
            'sub_category_id' => $sub_category_id,
            'question_id' => $question_id
        ]);
    }


    $history->update([
        'question_id' => $question_id
    ]);

    dd($user->history);

});
