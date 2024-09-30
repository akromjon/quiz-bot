<?php

use App\Imports\QuestionImport;
use App\Models\SubCategory;
use App\Models\TelegramUser;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelExcel;
use App\Telegram\Menu\Menu;

it('get question',function(){

    $question=Menu::getQuestionBySubCategoryId(144,40);
    dd($question);
});
